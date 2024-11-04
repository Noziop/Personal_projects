# models/domain/ritual_day.py
from typing import Dict, Optional
from sqlalchemy import Column, String, ForeignKey, UniqueConstraint, Index, UUID, DateTime
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.day_of_week import DayOfWeek
from .enums.ritual_type import RitualType

logger = settings.logger  # Notre super logger loguru ! 👑

class CohortRitualSchedule(Base):
    """Configuration of ritual schedules for each cohort ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    cohort_id = Column(UUID, ForeignKey('cohorts.id'), nullable=False)
    day = Column(String(20), ForeignKey('enum_day_of_week.value'), nullable=False)
    ritual_type = Column(String(20), ForeignKey('enum_ritual_type.value'), nullable=False)
    scheduled_time = Column(DateTime(timezone=True), nullable=False)
    effective_from = Column(DateTime(timezone=True), nullable=False)
    effective_until = Column(DateTime(timezone=True), nullable=True)
    created_by = Column(UUID, ForeignKey('users.id'), nullable=False)

    # Relationships
    cohort = relationship("Cohort", back_populates="ritual_schedule")
    creator = relationship("User")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('cohort_id', 'day', 'ritual_type', 'effective_from',
                        name='uq_cohort_ritual_schedule'),
        Index('idx_ritual_schedule', 'cohort_id', 'day'),
        Index('idx_schedule_dates', 'effective_from', 'effective_until'),
    )

    @property
    def day_display_name(self) -> str:
        """Get localized day name"""
        return DayOfWeek(self.day).display_names['fr']

    @property
    def ritual_display_name(self) -> str:
        """Get localized ritual name"""
        return RitualType(self.ritual_type).display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return RitualType(self.ritual_type).emoji

    @property
    def is_sod(self) -> bool:
        """Check if this is a SOD ritual"""
        return self.ritual_type == RitualType.SOD.value

    @property
    def is_standup(self) -> bool:
        """Check if this is a Standup ritual"""
        return self.ritual_type == RitualType.STANDUP.value

    @property
    def ritual_duration(self) -> int:
        """Get ritual duration in minutes"""
        return RitualType(self.ritual_type).duration_minutes

    @property
    def default_time(self) -> str:
        """Get default time for this ritual type"""
        logger.debug(f"🕒 Récupération horaire par défaut pour {self.ritual_type}")
        return ("11:30" if self.is_sod else "11:45")

    def validate_schedule(self) -> None:
        """Validate ritual schedule"""
        logger.debug(f"🔍 Validation planning pour {self.ritual_type}")
        
        # Vérifier les dates
        if self.effective_until and self.effective_until <= self.effective_from:
            logger.error("❌ Dates invalides pour le planning")
            raise ValidationException(
                error_code=ErrorCode.INVALID_SCHEDULE_DATES,
                start_date=self.effective_from.isoformat(),
                end_date=self.effective_until.isoformat()
            )
            
        # Vérifier l'horaire
        ritual_type = RitualType(self.ritual_type)
        if not ritual_type.validate_time(self.scheduled_time.time()):
            logger.error(f"❌ Horaire invalide: {self.scheduled_time.time()}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_RITUAL_TIME,
                ritual_type=self.ritual_display_name,
                given_time=self.scheduled_time.time().isoformat()
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour planning {self.id}")
        return {
            "id": str(self.id),
            "cohort_id": str(self.cohort_id),
            "day": self.day,
            "day_display_name": self.day_display_name,
            "ritual_type": self.ritual_type,
            "ritual_display_name": self.ritual_display_name,
            "scheduled_time": self.scheduled_time.isoformat(),
            "effective_from": self.effective_from.isoformat(),
            "effective_until": self.effective_until.isoformat() if self.effective_until else None,
            "duration_minutes": self.ritual_duration,
            "emoji": self.ritual_emoji
        }

    def __str__(self) -> str:
        """String representation"""
        status = "actif" if not self.effective_until else "terminé"
        return (
            f"{self.ritual_emoji} {self.ritual_display_name} "
            f"le {self.day_display_name} à {self.scheduled_time.strftime('%H:%M')} "
            f"pour {self.cohort.name} "
            f"({self.ritual_duration} min) [{status}]"
        )