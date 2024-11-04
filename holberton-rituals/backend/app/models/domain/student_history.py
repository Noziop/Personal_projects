# models/domain/student_history.py
from datetime import date
from typing import Dict, Optional
from sqlalchemy import Column, Text, Date, ForeignKey, Index, CheckConstraint, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base

logger = settings.logger  # Notre super logger loguru ! 👑

class StudentCohortHistory(Base):
    """Track student movements between cohorts ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    student_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    cohort_id = Column(UUID, ForeignKey('cohorts.id'), nullable=False)
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=True)
    reason = Column(Text)
    created_by = Column(UUID, ForeignKey('users.id'), nullable=False)

    # Relationships
    student = relationship("Student", back_populates="cohort_history")
    cohort = relationship("Cohort", back_populates="student_history")
    creator = relationship("User")

    # Constraints and indexes
    __table_args__ = (
        CheckConstraint('end_date IS NULL OR end_date >= start_date', 
                       name='check_history_dates'),
        Index('idx_history_dates', 'start_date', 'end_date'),
    )

    def format_date(self, date_value: date) -> str:
        """Format date in French"""
        return date_value.strftime("%d/%m/%Y")

    @property
    def is_active(self) -> bool:
        """Check if this is the current active period"""
        logger.debug(f"🔍 Vérification période active pour {self.id}")
        return self.end_date is None

    @property
    def duration_days(self) -> int:
        """Calculate the duration in days"""
        logger.debug(f"📅 Calcul durée pour {self.id}")
        if not self.end_date:
            return (date.today() - self.start_date).days
        return (self.end_date - self.start_date).days

    @property
    def duration_formatted(self) -> str:
        """Get formatted duration in French"""
        logger.debug(f"⏱️ Formatage durée pour {self.id}")
        days = self.duration_days
        if days < 30:
            return f"{days} jour{'s' if days > 1 else ''}"
        months = days // 30
        remaining_days = days % 30
        result = f"{months} mois"
        if remaining_days:
            result += f" et {remaining_days} jour{'s' if remaining_days > 1 else ''}"
        return result

    @property
    def status_emoji(self) -> str:
        """Get status emoji"""
        return "✅" if self.is_active else "📚"

    def validate(self) -> None:
        """Validate history entry"""
        logger.debug(f"🔍 Validation historique {self.id}")
        
        # Vérifier les dates
        if self.end_date and self.end_date <= self.start_date:
            logger.error("❌ Dates invalides")
            raise ValidationException(
                error_code=ErrorCode.INVALID_HISTORY_DATES,
                start_date=self.start_date.isoformat(),
                end_date=self.end_date.isoformat()
            )
            
        # Vérifier le chevauchement avec d'autres périodes
        if self.has_overlap():
            logger.error("❌ Chevauchement de périodes détecté")
            raise ValidationException(
                error_code=ErrorCode.OVERLAPPING_PERIODS,
                student_id=str(self.student_id)
            )

    def has_overlap(self) -> bool:
        """Check for overlapping periods"""
        logger.debug(f"🔍 Vérification chevauchements pour {self.id}")
        
        for history in self.student.cohort_history:
            if history.id != self.id:
                if (history.start_date <= self.start_date <= (history.end_date or date.max) or
                    self.start_date <= history.start_date <= (self.end_date or date.max)):
                    return True
        return False

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour historique {self.id}")
        return {
            "id": str(self.id),
            "student_id": str(self.student_id),
            "student_name": self.student.full_name,
            "cohort_id": str(self.cohort_id),
            "cohort_name": self.cohort.name,
            "start_date": self.start_date.isoformat(),
            "end_date": self.end_date.isoformat() if self.end_date else None,
            "reason": self.reason,
            "is_active": self.is_active,
            "duration_days": self.duration_days,
            "duration_formatted": self.duration_formatted,
            "created_by": str(self.created_by)
        }

    def to_notification_message(self) -> str:
        """Format for notification"""
        logger.debug(f"📝 Génération message notification pour historique {self.id}")
        return f"""
👤 *Changement de cohorte - {self.student.full_name}*
📅 Date de début : {self.format_date(self.start_date)}
{f'📅 Date de fin : {self.format_date(self.end_date)}' if self.end_date else ''}
👥 Cohorte : {self.cohort.name}
⏱️ Durée : {self.duration_formatted}
{f'📝 Raison : {self.reason}' if self.reason else ''}
👤 Modifié par : {self.creator.full_name}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        status = "En cours" if self.is_active else "Terminé"
        return (
            f"{self.status_emoji} {status} - {self.student.full_name} "
            f"dans {self.cohort.name} "
            f"depuis le {self.format_date(self.start_date)} "
            f"({self.duration_formatted})"
        )