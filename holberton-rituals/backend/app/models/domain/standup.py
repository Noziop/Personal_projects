# models/domain/standup.py
from datetime import date, time
from typing import Dict, Optional
from sqlalchemy import Column, Boolean, Date, Time, ForeignKey, UniqueConstraint, Index, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType

logger = settings.logger  # Notre super logger loguru ! 👑

class StandupAssignment(Base):
    """Track Standup assignments and facilitations ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    student_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    assignment_date = Column(Date, nullable=False)
    meeting_time = Column(Time, nullable=False, default=time(11, 45))
    cohort_id = Column(UUID, ForeignKey('cohorts.id'), nullable=False)
    is_completed = Column(Boolean, default=False, index=True)
    replacement_for_id = Column(UUID, ForeignKey('standup_assignments.id'), nullable=True)
    schedule_id = Column(UUID, ForeignKey('cohort_ritual_schedule.id'), nullable=False)

    # Relationships
    student = relationship(
        "Student",
        back_populates="standup_assignments"
    )
    cohort = relationship(
        "Cohort",
        back_populates="standup_assignments"
    )
    replaced_assignment = relationship(
        "StandupAssignment",
        remote_side=[id],
        backref="replacement_assignment",
        uselist=False
    )
    schedule = relationship("CohortRitualSchedule")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('assignment_date', 'cohort_id', 
                        name='uq_standup_date_cohort'),
        Index('idx_standup_dates', 'assignment_date'),
        Index('idx_standup_completion', 'is_completed'),
    )

    @property
    def ritual_type(self) -> RitualType:
        """Get ritual type enum"""
        return RitualType.STANDUP

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

    def format_date(self) -> str:
        """Format date in French"""
        return self.assignment_date.strftime("%d/%m/%Y")

    @property
    def is_past(self) -> bool:
        """Check if Standup date is in the past"""
        logger.debug(f"🔍 Vérification si standup {self.id} est passé")
        return self.assignment_date < date.today()

    @property
    def is_today(self) -> bool:
        """Check if Standup is today"""
        return self.assignment_date == date.today()

    @property
    def is_replacement(self) -> bool:
        """Check if this is a replacement Standup"""
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

    def validate(self) -> None:
        """Validate standup assignment"""
        logger.debug(f"🔍 Validation standup {self.id}")
        
        if self.meeting_time != time(11, 45):
            logger.error(f"❌ Horaire invalide: {self.meeting_time}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_STANDUP_TIME,
                given_time=self.meeting_time.strftime("%H:%M")
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour standup {self.id}")
        return {
            "id": str(self.id),
            "student_id": str(self.student_id),
            "student_name": self.student.full_name,
            "assignment_date": self.assignment_date.isoformat(),
            "meeting_time": self.meeting_time.strftime("%H:%M"),
            "cohort_id": str(self.cohort_id),
            "cohort_name": self.cohort.name,
            "is_completed": self.is_completed,
            "is_replacement": self.is_replacement,
            "replaced_student_name": self.replaced_assignment.student.full_name if self.is_replacement else None,
            "status": self.status_emoji,
            "duration_minutes": self.duration_minutes
        }

    def to_notification_message(self) -> str:
        """Format for notification"""
        logger.debug(f"📝 Génération message notification pour standup {self.id}")
        return f"""
{self.ritual_emoji} *{self.ritual_display_name}*
📅 Date : {self.format_date()}
🕐 Heure : {self.meeting_time.strftime("%H:%M")}
👤 Scrum Master : {self.student.full_name}
👥 Cohorte : {self.cohort.name}
⏱️ Durée prévue : {self.duration_minutes} minutes
{f'🔄 Remplacement pour : {self.replaced_assignment.student.full_name}' if self.is_replacement else ''}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.ritual_emoji} {self.status_emoji} {self.ritual_display_name} - "
            f"{self.student.full_name} pour {self.cohort.name} "
            f"le {self.format_date()} à {self.meeting_time.strftime('%H:%M')}"
        )