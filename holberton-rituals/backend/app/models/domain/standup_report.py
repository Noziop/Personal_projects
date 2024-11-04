# models/domain/standup_report.py
from datetime import date, time
from typing import List, Dict, Optional
from sqlalchemy import Column, Text, JSON, Date, Boolean, ForeignKey, UniqueConstraint, Index, UUID, Time
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType

logger = settings.logger  # Notre super logger loguru ! 👑

class StandupReport(Base):
    """Daily standup reports for each cohort ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    cohort_id = Column(UUID, ForeignKey('cohorts.id'), nullable=False)
    report_date = Column(Date, nullable=False)
    meeting_time = Column(Time, nullable=False, default=time(11, 45))
    scrum_master_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    bugs_report = Column(Text)
    common_difficulties = Column(Text)
    shared_tips = Column(Text)
    conclusion = Column(Text)
    additional_notes = Column(Text)
    projects_of_week = Column(JSON)
    schedule_id = Column(UUID, ForeignKey('cohort_ritual_schedule.id'), nullable=False)

    # Relationships
    cohort = relationship("Cohort", back_populates="standup_reports")
    scrum_master = relationship(
        "Student",
        back_populates="standup_reports"
    )
    student_reports = relationship(
        "StudentDailyReport", 
        back_populates="standup_report",
        cascade="all, delete-orphan"
    )
    schedule = relationship("CohortRitualSchedule")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('cohort_id', 'report_date', 
                        name='uq_standup_report_date_cohort'),
        Index('idx_standup_report_date', 'report_date'),
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

    def format_date(self) -> str:
        """Format date in French"""
        return self.report_date.strftime("%d/%m/%Y")

    @property
    def has_bugs(self) -> bool:
        """Check if any bugs were reported"""
        logger.debug(f"🔍 Vérification bugs pour rapport {self.id}")
        return bool(self.bugs_report and self.bugs_report.strip())

    @property
    def has_difficulties(self) -> bool:
        """Check if any difficulties were reported"""
        logger.debug(f"🔍 Vérification difficultés pour rapport {self.id}")
        return bool(self.common_difficulties and self.common_difficulties.strip())

    def get_active_projects(self) -> List[str]:
        """Get list of active projects for the week"""
        logger.debug(f"📋 Récupération projets actifs pour rapport {self.id}")
        return self.projects_of_week.get('active', []) if self.projects_of_week else []

    def validate(self) -> None:
        """Validate standup report"""
        logger.debug(f"🔍 Validation rapport {self.id}")
        
        if self.meeting_time != time(11, 45):
            logger.error(f"❌ Horaire invalide: {self.meeting_time}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_STANDUP_TIME,
                given_time=self.meeting_time.strftime("%H:%M")
            )
            
        if not self.conclusion and self.student_reports:
            logger.warning("⚠️ Rapport sans conclusion")

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour rapport {self.id}")
        return {
            "id": str(self.id),
            "cohort_id": str(self.cohort_id),
            "cohort_name": self.cohort.name,
            "report_date": self.report_date.isoformat(),
            "meeting_time": self.meeting_time.strftime("%H:%M"),
            "scrum_master_id": str(self.scrum_master_id),
            "scrum_master_name": self.scrum_master.full_name,
            "bugs_report": self.bugs_report,
            "common_difficulties": self.common_difficulties,
            "shared_tips": self.shared_tips,
            "conclusion": self.conclusion,
            "additional_notes": self.additional_notes,
            "projects_of_week": self.projects_of_week,
            "has_bugs": self.has_bugs,
            "has_difficulties": self.has_difficulties,
            "active_projects": self.get_active_projects()
        }

    def to_slack_message(self) -> str:
        """Format report for Slack notification"""
        logger.debug(f"💬 Génération message Slack pour rapport {self.id}")
        return f"""
{self.ritual_emoji} *Rapport {self.ritual_display_name} - {self.cohort.name} - {self.format_date()}*
🕐 *Heure* : {self.meeting_time.strftime("%H:%M")}
👤 *Scrum Master* : {self.scrum_master.full_name}
🎯 *Projets* : {', '.join(self.get_active_projects())}
🐛 *Bugs* : {self.bugs_report or 'Aucun signalé'}
⚠️ *Difficultés* : {self.common_difficulties or 'Aucune signalée'}
💡 *Astuces* : {self.shared_tips or 'Aucune partagée'}
📝 *Conclusion* : {self.conclusion or 'Pas de conclusion'}
{f'📌 *Notes* : {self.additional_notes}' if self.additional_notes else ''}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.ritual_emoji} Rapport {self.ritual_display_name} - "
            f"{self.cohort.name} du {self.format_date()} à {self.meeting_time.strftime('%H:%M')}"
        )
    
class StudentDailyReport(Base):
    """Individual student reports for daily standups ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    standup_report_id = Column(UUID, ForeignKey('standup_reports.id'), nullable=False)
    student_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    is_absent = Column(Boolean, default=False)
    is_on_site = Column(Boolean, default=False)
    achievements = Column(Text)
    today_goals = Column(Text)
    needs_help = Column(Boolean, default=False)
    problem_nature = Column(Text)
    other_remarks = Column(Text)

    # Relationships
    standup_report = relationship("StandupReport", back_populates="student_reports")
    student = relationship("Student", back_populates="daily_reports")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('standup_report_id', 'student_id', 
                        name='uq_daily_report_student'),
        Index('idx_student_attendance', 'is_absent', 'is_on_site'),
    )

    def format_date(self) -> str:
        """Format date in French"""
        logger.debug(f"📅 Formatage date pour rapport {self.id}")
        return self.standup_report.report_date.strftime("%d/%m/%Y")

    @property
    def location_status(self) -> str:
        """Get location status in French"""
        if self.is_absent:
            return "Absent(e)"
        return "Sur site" if self.is_on_site else "En distanciel"

    @property
    def status_emoji(self) -> str:
        """Get emoji representing student status"""
        if self.is_absent:
            return "❌"
        return "🏢" if self.is_on_site else "🏠"

    @property
    def needs_help_emoji(self) -> str:
        """Get emoji representing help status"""
        return "🆘" if self.needs_help else "✅"

    def validate(self) -> None:
        """Validate daily report"""
        logger.debug(f"🔍 Validation rapport quotidien {self.id}")
        
        if self.is_absent and self.is_on_site:
            logger.error("❌ Un étudiant ne peut pas être absent ET sur site")
            raise ValidationException(
                error_code=ErrorCode.INVALID_ATTENDANCE_STATUS,
                student_id=str(self.student_id)
            )
            
        if self.needs_help and not self.problem_nature:
            logger.warning("⚠️ Besoin d'aide signalé sans description du problème")
            raise ValidationException(
                error_code=ErrorCode.MISSING_PROBLEM_DESCRIPTION,
                student_id=str(self.student_id)
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour rapport quotidien {self.id}")
        return {
            "id": str(self.id),
            "standup_report_id": str(self.standup_report_id),
            "student_id": str(self.student_id),
            "student_name": self.student.full_name,
            "date": self.format_date(),
            "is_absent": self.is_absent,
            "is_on_site": self.is_on_site,
            "location_status": self.location_status,
            "achievements": self.achievements,
            "today_goals": self.today_goals,
            "needs_help": self.needs_help,
            "problem_nature": self.problem_nature,
            "other_remarks": self.other_remarks,
            "status_emoji": self.status_emoji,
            "needs_help_emoji": self.needs_help_emoji
        }

    def to_slack_message(self) -> str:
        """Format report for Slack notification"""
        logger.debug(f"💬 Génération message Slack pour rapport quotidien {self.id}")
        return f"""
*{self.student.full_name}* {self.status_emoji} ({self.location_status})
🎯 *Objectifs* : {self.today_goals or 'Non spécifiés'}
✨ *Réalisations* : {self.achievements or 'Aucune signalée'}
{self.needs_help_emoji} {f"*Besoin d'aide avec* : {self.problem_nature}" if self.needs_help else "Pas besoin d'aide"}
📝 *Remarques* : {self.other_remarks or 'Aucune'}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        return (
            f"Rapport quotidien - {self.student.full_name} "
            f"({self.status_emoji} {self.needs_help_emoji}) "
            f"du {self.format_date()}"
        )