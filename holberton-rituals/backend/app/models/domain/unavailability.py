# models/domain/unavailability.py
from datetime import date
from typing import Dict, Optional
from sqlalchemy import Column, String, Text, Date, ForeignKey, CheckConstraint, Index, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.unavailability_status import UnavailabilityStatus
from .enums.cohort_pause_type import CohortPauseType

logger = settings.logger  # Notre super logger loguru ! 👑

class StudentUnavailability(Base):
    """Track student unavailability periods ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    student_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    reason = Column(Text)
    pause_type = Column(String(30), ForeignKey('enum_cohort_pause_type.value'), nullable=False)
    status = Column(
        String(20), 
        ForeignKey('enum_unavailability_status.value'),
        nullable=False,
        default=UnavailabilityStatus.PENDING.value,
        index=True
    )
    validated_by = Column(UUID, ForeignKey('users.id'), nullable=True)

    # Relationships
    student = relationship("Student", back_populates="unavailability_periods")
    validator = relationship("User", foreign_keys=[validated_by])

    # Constraints and indexes
    __table_args__ = (
        CheckConstraint('end_date >= start_date', name='check_unavail_dates'),
        Index('idx_unavail_dates', 'start_date', 'end_date'),
        Index('idx_unavail_status', 'status'),
    )

    def format_date(self, date_value: date) -> str:
        """Format date in French"""
        return date_value.strftime("%d/%m/%Y")

    @property
    def status_display_name(self) -> str:
        """Get localized status name"""
        return UnavailabilityStatus(self.status).display_names['fr']

    @property
    def status_emoji(self) -> str:
        """Get status emoji"""
        return UnavailabilityStatus(self.status).emoji

    @property
    def pause_display_name(self) -> str:
        """Get localized pause type name"""
        return CohortPauseType(self.pause_type).display_names['fr']

    @property
    def pause_emoji(self) -> str:
        """Get pause type emoji"""
        return CohortPauseType(self.pause_type).emoji

    @property
    def duration_days(self) -> int:
        """Calculate the duration in days"""
        logger.debug(f"📅 Calcul durée pour indisponibilité {self.id}")
        return (self.end_date - self.start_date).days + 1

    @property
    def duration_formatted(self) -> str:
        """Get formatted duration in French"""
        logger.debug(f"⏱️ Formatage durée pour indisponibilité {self.id}")
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
    def is_pending(self) -> bool:
        """Check if request is pending"""
        return self.status == UnavailabilityStatus.PENDING.value

    @property
    def is_validated(self) -> bool:
        """Check if request is validated"""
        return self.status == UnavailabilityStatus.VALIDATED.value

    @property
    def is_current(self) -> bool:
        """Check if this period includes current date"""
        today = date.today()
        is_current = self.start_date <= today <= self.end_date
        if is_current:
            logger.info(f"📅 Période d'indisponibilité active pour {self.student.full_name}")
        return is_current

    def validate(self) -> None:
        """Validate unavailability period"""
        logger.debug(f"🔍 Validation indisponibilité {self.id}")
        
        # Vérifier la durée selon le type de pause
        pause_type = CohortPauseType(self.pause_type)
        if not pause_type.validate_duration(self.duration_days):
            logger.error(f"❌ Durée invalide pour {self.pause_type}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_UNAVAILABILITY_DURATION,
                pause_type=self.pause_display_name,
                duration=self.duration_days
            )
            
        # Vérifier le chevauchement avec d'autres périodes
        if self.has_overlap():
            logger.error("❌ Chevauchement de périodes détecté")
            raise ValidationException(
                error_code=ErrorCode.OVERLAPPING_UNAVAILABILITY,
                student_id=str(self.student_id)
            )

    def has_overlap(self) -> bool:
        """Check for overlapping periods"""
        logger.debug(f"🔍 Vérification chevauchements pour {self.id}")
        
        for period in self.student.unavailability_periods:
            if period.id != self.id and period.is_validated:
                if (period.start_date <= self.start_date <= period.end_date or
                    self.start_date <= period.start_date <= self.end_date):
                    return True
        return False

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour indisponibilité {self.id}")
        return {
            "id": str(self.id),
            "student_id": str(self.student_id),
            "student_name": self.student.full_name,
            "start_date": self.start_date.isoformat(),
            "end_date": self.end_date.isoformat(),
            "reason": self.reason,
            "pause_type": self.pause_type,
            "pause_display_name": self.pause_display_name,
            "status": self.status,
            "status_display_name": self.status_display_name,
            "validated_by": str(self.validated_by) if self.validated_by else None,
            "validator_name": self.validator.full_name if self.validator else None,
            "duration_days": self.duration_days,
            "duration_formatted": self.duration_formatted,
            "is_current": self.is_current
        }

    def to_notification_message(self) -> str:
        """Format for notification"""
        logger.debug(f"📝 Génération message notification pour indisponibilité {self.id}")
        return f"""
👤 *Indisponibilité - {self.student.full_name}*
{self.pause_emoji} Type : {self.pause_display_name}
📅 Période : du {self.format_date(self.start_date)} au {self.format_date(self.end_date)}
⏱️ Durée : {self.duration_formatted}
{self.status_emoji} Statut : {self.status_display_name}
{f'📝 Raison : {self.reason}' if self.reason else ''}
{f'✍️ Validé par : {self.validator.full_name}' if self.validator else ''}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.status_emoji} {self.pause_emoji} Indisponibilité de {self.student.full_name} : "
            f"du {self.format_date(self.start_date)} "
            f"au {self.format_date(self.end_date)} "
            f"({self.duration_formatted}) - {self.status_display_name}"
        )