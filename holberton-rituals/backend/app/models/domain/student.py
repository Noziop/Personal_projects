# models/domain/student.py
from datetime import datetime, timezone, date, time
from typing import Dict, List, Optional
from sqlalchemy import Column, Boolean, DateTime, Date, ForeignKey, Index, String, Enum, Integer, UUID, Time
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType
from .enums.cohort_pause_type import CohortPauseType
from .enums.curriculum_type import CurriculumType

logger = settings.logger  # Notre super logger loguru ! 👑

class Student(Base):
    """Student model extending user information for students ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    user_id = Column(UUID, ForeignKey('users.id'), nullable=False, unique=True)
    current_cohort_id = Column(UUID, ForeignKey('cohorts.id'), nullable=False)
    curriculum_type = Column(String(20), ForeignKey('enum_curriculum_type.value'), nullable=False)
    sod_count = Column(Integer, default=0)
    standup_count = Column(Integer, default=0)
    last_sod_date = Column(Date, nullable=True)
    last_standup_date = Column(Date, nullable=True)
    is_active = Column(Boolean, default=True, index=True)

    # Relationships
    user = relationship("User", back_populates="student")
    cohort = relationship("Cohort", back_populates="students")
    cohort_history = relationship("StudentCohortHistory", back_populates="student")
    unavailability_periods = relationship("StudentUnavailability", back_populates="student")
    sod_presentations = relationship("SODDrawing", foreign_keys='SODDrawing.student_id', back_populates="student")
    standup_assignments = relationship("StandupAssignment", back_populates="student")
    standup_reports = relationship("StandupReport", back_populates="scrum_master")
    daily_reports = relationship("StudentDailyReport", back_populates="student")
    progress_records = relationship("StudentProgress", back_populates="student")
    pauses = relationship("StudentPause", back_populates="student")
    
    # Indexes
    __table_args__ = (
        Index('idx_student_counts', 'sod_count', 'standup_count'),
        Index('idx_student_last_dates', 'last_sod_date', 'last_standup_date'),
        Index('idx_student_active', 'is_active'),
    )

    def format_date(self, date_value: date) -> str:
        """Format date in French"""
        return date_value.strftime("%d/%m/%Y") if date_value else "Jamais"

    @property
    def full_name(self) -> str:
        """Returns student's full name from user"""
        return self.user.full_name if self.user else ""

    @property
    def ritual_counts(self) -> Dict[str, int]:
        """Get counts for each ritual type"""
        logger.debug(f"📊 Récupération compteurs rituels pour {self.full_name}")
        return {
            RitualType.SOD.value: self.sod_count,
            RitualType.STANDUP.value: self.standup_count
        }

    @property
    def last_ritual_dates(self) -> Dict[str, str]:
        """Get last dates for each ritual type"""
        logger.debug(f"📅 Récupération dernières dates pour {self.full_name}")
        return {
            RitualType.SOD.value: self.format_date(self.last_sod_date),
            RitualType.STANDUP.value: self.format_date(self.last_standup_date)
        }

    @property
    def ritual_schedule(self) -> Dict[str, List[Dict]]:
        """Get ritual schedule based on curriculum type"""
        logger.debug(f"📅 Récupération planning pour {self.full_name}")
        curriculum = CurriculumType(self.curriculum_type)
        return {
            RitualType.SOD.value: [
                {"day": day, "time": time(11, 30)}
                for day in curriculum.ritual_days[RitualType.SOD.value]
            ],
            RitualType.STANDUP.value: [
                {"day": day, "time": time(11, 45)}
                for day in curriculum.ritual_days[RitualType.STANDUP.value]
            ]
        }

    def get_next_ritual_date(self, ritual_type: RitualType) -> Optional[date]:
        """Get next scheduled ritual date"""
        logger.debug(f"🔍 Recherche prochaine date {ritual_type.value} pour {self.full_name}")
        today = date.today()
        
        if ritual_type == RitualType.SOD:
            future_rituals = [sod for sod in self.sod_presentations 
                            if sod.presentation_date >= today]
        else:
            future_rituals = [standup for standup in self.standup_assignments 
                            if standup.assignment_date >= today]
        
        if not future_rituals:
            logger.info(f"ℹ️ Aucun {ritual_type.value} programmé pour {self.full_name}")
            return None
            
        next_date = min(r.presentation_date if ritual_type == RitualType.SOD 
                       else r.assignment_date for r in future_rituals)
        logger.info(f"📅 Prochain {ritual_type.value}: {next_date}")
        return next_date

    def is_available_on(self, check_date: date) -> bool:
        """Check if student is available on a specific date"""
        logger.debug(f"🔍 Vérification disponibilité pour {self.full_name} le {check_date}")
        
        for period in self.unavailability_periods:
            if period.is_validated and period.start_date <= check_date <= period.end_date:
                logger.info(f"⚠️ {self.full_name} indisponible le {check_date}")
                return False
                
        return True

    def validate(self) -> None:
        """Validate student data"""
        logger.debug(f"🔍 Validation données pour {self.full_name}")
        
        # Vérifier le curriculum
        curriculum = CurriculumType(self.curriculum_type)
        if not curriculum:
            logger.error(f"❌ Type de curriculum invalide: {self.curriculum_type}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_CURRICULUM_TYPE,
                student_id=str(self.id)
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour {self.full_name}")
        return {
            "id": str(self.id),
            "user_id": str(self.user_id),
            "full_name": self.full_name,
            "cohort_id": str(self.current_cohort_id),
            "cohort_name": self.cohort.name,
            "curriculum_type": self.curriculum_type,
            "ritual_counts": self.ritual_counts,
            "last_ritual_dates": self.last_ritual_dates,
            "ritual_schedule": self.ritual_schedule,
            "is_active": self.is_active
        }

    def to_summary_message(self) -> str:
        """Format student summary for notification"""
        logger.debug(f"📝 Génération résumé pour {self.full_name}")
        return f"""
👤 *{self.full_name}* ({self.cohort.name})
📊 Participations :
• SOD : {self.sod_count} (Dernier : {self.last_ritual_dates[RitualType.SOD.value]})
• Stand-up : {self.standup_count} (Dernier : {self.last_ritual_dates[RitualType.STANDUP.value]})
{f'⚠️ Inactif' if not self.is_active else ''}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        status = "✅" if self.is_active else "❌"
        return f"{status} {self.full_name} ({self.cohort.name})"

class StudentPause(Base):
    """Student pause periods tracking ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    student_id = Column(UUID, ForeignKey("student.id"), nullable=False)
    pause_type = Column(String(30), ForeignKey('enum_cohort_pause_type.value'), nullable=False)
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    reason = Column(String)
    
    # Relationships
    student = relationship("Student", back_populates="pauses")

    def validate(self) -> None:
        """Validate pause period"""
        logger.debug(f"🔍 Validation pause pour {self.student_id}")
        
        pause_type = CohortPauseType(self.pause_type)
        duration = (self.end_date - self.start_date).days
        
        if not pause_type.validate_duration(duration):
            logger.error(f"❌ Durée invalide pour pause {self.pause_type}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_PAUSE_DURATION,
                pause_type=self.pause_type,
                duration=duration
            )