# models/domain/sod.py
from datetime import date, time
from typing import Dict, Optional
from sqlalchemy import Column, Boolean, Date, Time, ForeignKey, UniqueConstraint, Index, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType

logger = settings.logger  # Notre super logger loguru ! 👑

class SODDrawing(Base):
    """Track SOD assignments and presentations ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    student_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    presentation_date = Column(Date, nullable=False)
    presentation_time = Column(Time, nullable=False, default=time(11, 30))
    evaluator_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    is_completed = Column(Boolean, default=False, index=True)
    replacement_for_id = Column(UUID, ForeignKey('sod_drawings.id'), nullable=True)
    schedule_id = Column(UUID, ForeignKey('cohort_ritual_schedule.id'), nullable=False)

    # Relationships
    student = relationship(
        "Student",
        foreign_keys=[student_id],
        back_populates="sod_presentations"
    )
    evaluator = relationship(
        "Student",
        foreign_keys=[evaluator_id],
        backref="sod_evaluations"
    )
    replaced_drawing = relationship(
        "SODDrawing",
        remote_side=[id],
        backref="replacement_drawing",
        uselist=False
    )
    schedule = relationship("CohortRitualSchedule")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('presentation_date', 'student_id', 
                        name='uq_sod_date_student'),
        Index('idx_sod_dates', 'presentation_date'),
        Index('idx_sod_completion', 'is_completed'),
    )

    @property
    def ritual_type(self) -> RitualType:
        """Get ritual type enum"""
        return RitualType.SOD

    @property
    def ritual_display_name(self) -> str:
        """Get localized ritual name"""
        return self.ritual_type.display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return self.ritual_type.emoji

    @property
    def duration_minutes(self) -> int:
        """Get expected duration in minutes"""
        return self.ritual_type.duration_minutes

    @property
    def is_past(self) -> bool:
        """Check if SOD date is in the past"""
        logger.debug(f"🔍 Vérification si SOD {self.id} est passé")
        return self.presentation_date < date.today()

    @property
    def is_today(self) -> bool:
        """Check if SOD is today"""
        return self.presentation_date == date.today()

    @property
    def is_replacement(self) -> bool:
        """Check if this is a replacement SOD"""
        return self.replacement_for_id is not None

    @property
    def status_emoji(self) -> str:
        """Get status emoji"""
        if self.is_completed:
            return "✅"
        if self.is_past:
            return "❌"
        if self.is_today:
            return "🎯"
        if self.is_replacement:
            return "🔄"
        return "⏳"

    def format_date(self) -> str:
        """Format date in French"""
        return self.presentation_date.strftime("%d/%m/%Y")

    def validate(self) -> None:
        """Validate SOD drawing"""
        logger.debug(f"🔍 Validation SOD {self.id}")
        
        # Vérifier que l'étudiant n'est pas son propre évaluateur
        if self.student_id == self.evaluator_id:
            logger.error("❌ L'étudiant ne peut pas s'auto-évaluer")
            raise ValidationException(
                error_code=ErrorCode.SELF_EVALUATION_NOT_ALLOWED,
                student_id=str(self.student_id)
            )
            
        # Vérifier l'horaire
        if self.presentation_time != time(11, 30):
            logger.error(f"❌ Horaire invalide: {self.presentation_time}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_SOD_TIME,
                given_time=self.presentation_time.strftime("%H:%M")
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour SOD {self.id}")
        return {
            "id": str(self.id),
            "student_id": str(self.student_id),
            "student_name": self.student.full_name,
            "evaluator_id": str(self.evaluator_id),
            "evaluator_name": self.evaluator.full_name,
            "presentation_date": self.presentation_date.isoformat(),
            "presentation_time": self.presentation_time.strftime("%H:%M"),
            "is_completed": self.is_completed,
            "is_replacement": self.is_replacement,
            "replaced_student_name": self.replaced_drawing.student.full_name if self.is_replacement else None,
            "status": self.status_emoji,
            "duration_minutes": self.duration_minutes
        }

    def to_notification_message(self) -> str:
        """Format for notification"""
        logger.debug(f"📝 Génération message notification pour SOD {self.id}")
        return f"""
{self.ritual_emoji} *{self.ritual_display_name}*
📅 Date : {self.format_date()}
🕐 Heure : {self.presentation_time.strftime("%H:%M")}
👤 Présentateur : {self.student.full_name}
👥 Évaluateur : {self.evaluator.full_name}
⏱️ Durée prévue : {self.duration_minutes} minutes
{f'🔄 Remplacement pour : {self.replaced_drawing.student.full_name}' if self.is_replacement else ''}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.ritual_emoji} {self.status_emoji} {self.ritual_display_name} - "
            f"{self.student.full_name} le {self.format_date()} à {self.presentation_time.strftime('%H:%M')} "
            f"(évalué par {self.evaluator.full_name})"
        )