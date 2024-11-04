# models/domain/cohort.py
from datetime import datetime, timezone, date
from typing import List, Dict, Optional
from sqlalchemy import Column, String, Boolean, Date, JSON, ForeignKey, Index, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.curriculum_type import CurriculumType
from .enums.cohort_pause_type import CohortPauseType

logger = settings.logger  # Notre super logger loguru ! 👑

class Cohort(Base):
    """Cohort model representing a group of students ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    name = Column(String(50), nullable=False, unique=True)
    curriculum_type = Column(String(20), ForeignKey('enum_curriculum_type.value'), nullable=False)
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    slack_channel = Column(String(100))
    slack_workspace_id = Column(String(100))
    timezone = Column(String(50), default='Europe/Paris')
    pause_periods = Column(JSON, comment='Format: [{type, start_date, end_date, scope, affected_students}]')
    is_active = Column(Boolean, default=True, index=True)

    # Relationships
    students = relationship("Student", back_populates="cohort")
    ritual_schedule = relationship("CohortRitualSchedule", back_populates="cohort")
    student_history = relationship("StudentCohortHistory", back_populates="cohort")
    standup_reports = relationship("StandupReport", back_populates="cohort")
    standup_assignments = relationship(
        "StandupAssignment",
        back_populates="cohort"
    )
    ritual_statistics = relationship(
        "RitualStatistics", 
        back_populates="cohort"
    )

    # Indexes and constraints
    __table_args__ = (
        Index('idx_cohort_dates', 'start_date', 'end_date'),
        Index('idx_cohort_active', 'is_active'),
    )

    @property
    def student_count(self) -> int:
        """Returns the number of active students in the cohort"""
        logger.debug(f"🔢 Comptage étudiants actifs pour {self.name}")
        return sum(1 for student in self.students if student.is_active)

    @property
    def curriculum_display_name(self) -> str:
        """Get localized curriculum name"""
        return CurriculumType(self.curriculum_type).display_names['fr']

    @property
    def duration_months(self) -> int:
        """Get program duration in months"""
        return CurriculumType(self.curriculum_type).duration_months

    @property
    def is_apprenticeship(self) -> bool:
        """Check if this is an apprenticeship cohort"""
        return CurriculumType(self.curriculum_type).is_apprenticeship

    @property
    def is_finished(self) -> bool:
        """Check if cohort has ended"""
        is_finished = self.end_date < date.today()
        if is_finished:
            logger.info(f"📅 Cohorte {self.name} terminée")
        return is_finished

    @property
    def is_ongoing(self) -> bool:
        """Check if cohort is currently running"""
        today = date.today()
        is_ongoing = self.start_date <= today <= self.end_date
        logger.debug(f"📊 Status cohorte {self.name}: {'en cours' if is_ongoing else 'pas en cours'}")
        return is_ongoing

    def is_in_pause(self, check_date: Optional[date] = None) -> bool:
        """Check if cohort is in pause period for a given date"""
        check_date = check_date or date.today()
        logger.debug(f"🔍 Vérification pause pour {self.name} à la date {check_date}")
        
        if not self.pause_periods:
            return False
            
        for period in self.pause_periods:
            try:
                start = datetime.strptime(period['start'], '%Y-%m-%d').date()
                end = datetime.strptime(period['end'], '%Y-%m-%d').date()
                pause_type = CohortPauseType(period.get('type', 'special_event'))
                
                if start <= check_date <= end:
                    logger.info(f"⏸️ Cohorte {self.name} en pause ({pause_type.display_names['fr']})")
                    return True
                    
            except (ValueError, KeyError) as e:
                logger.error(f"❌ Erreur format période de pause: {str(e)}")
                continue
                
        return False

    def get_pause_periods_formatted(self) -> List[Dict]:
        """Get formatted pause periods with type information"""
        logger.debug(f"📅 Formatage périodes de pause pour {self.name}")
        
        if not self.pause_periods:
            return []
            
        formatted_periods = []
        for period in self.pause_periods:
            try:
                pause_type = CohortPauseType(period.get('type', 'special_event'))
                formatted_periods.append({
                    'start': period['start'],
                    'end': period['end'],
                    'type': pause_type.display_names['fr'],
                    'emoji': pause_type.emoji,
                    'scope': pause_type.scope,
                    'affected_students': period.get('affected_students', [])
                })
            except (ValueError, KeyError) as e:
                logger.error(f"❌ Erreur formatage période: {str(e)}")
                continue
                
        return formatted_periods

    def validate_dates(self) -> None:
        """Validate cohort dates"""
        logger.debug(f"🔍 Validation dates pour {self.name}")
        
        if self.end_date <= self.start_date:
            logger.error(f"❌ Dates invalides pour {self.name}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_DATE_RANGE,
                start_date=self.start_date.isoformat(),
                end_date=self.end_date.isoformat()
            )

    def __str__(self) -> str:
        """String representation"""
        status = "✅" if self.is_active else "❌"
        return f"Cohort {self.name} ({self.curriculum_display_name}) {status}"