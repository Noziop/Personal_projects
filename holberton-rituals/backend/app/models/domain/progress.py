# models/domain/progress.py
from datetime import date
from typing import Dict, Optional
from sqlalchemy import Column, Integer, String, Date, ForeignKey, UniqueConstraint, Index, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType
from .enums.metric_type import MetricType

logger = settings.logger  # Notre super logger loguru ! 👑

class StudentProgress(Base):
    """Monthly progress tracking for student rituals ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    student_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    ritual_type = Column(String(20), ForeignKey('enum_ritual_type.value'), nullable=False)
    month_date = Column(Date, nullable=False)
    participation_count = Column(Integer, default=0)
    successful_presentations = Column(Integer, default=0)  # Pour les SOD
    helpful_contributions = Column(Integer, default=0)     # Pour les standups

    # Relationships
    student = relationship("Student", back_populates="progress_records")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('student_id', 'ritual_type', 'month_date',
                        name='uq_student_monthly_progress'),
        Index('idx_progress_date', 'month_date'),
    )

    @property
    def ritual_display_name(self) -> str:
        """Get localized ritual name"""
        return RitualType(self.ritual_type).display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return RitualType(self.ritual_type).emoji

    @property
    def month_year(self) -> str:
        """Get formatted month and year in French"""
        logger.debug(f"📅 Formatage date pour {self.month_date}")
        mois_fr = {
            "January": "Janvier", "February": "Février", "March": "Mars",
            "April": "Avril", "May": "Mai", "June": "Juin",
            "July": "Juillet", "August": "Août", "September": "Septembre",
            "October": "Octobre", "November": "Novembre", "December": "Décembre"
        }
        month_en = self.month_date.strftime("%B")
        return f"{mois_fr[month_en]} {self.month_date.year}"

    @property
    def success_rate(self) -> float:
        """Calculate success rate for SODs"""
        logger.debug(f"📊 Calcul taux de réussite SOD pour {self.student_id}")
        if self.ritual_type != RitualType.SOD.value or not self.participation_count:
            return 0.0
        return (self.successful_presentations / self.participation_count) * 100

    @property
    def contribution_rate(self) -> float:
        """Calculate helpful contribution rate for Standups"""
        logger.debug(f"📊 Calcul taux de contribution standup pour {self.student_id}")
        if self.ritual_type != RitualType.STANDUP.value or not self.participation_count:
            return 0.0
        return (self.helpful_contributions / self.participation_count) * 100

    def evaluate_performance(self) -> Dict:
        """Evaluate performance against targets"""
        logger.debug(f"🎯 Évaluation performance pour {self.student_id}")
        
        metric_type = (MetricType.SOD_PARTICIPATION if self.ritual_type == RitualType.SOD.value 
                      else MetricType.STANDUP_MASTER)
        
        rate = self.success_rate if self.ritual_type == RitualType.SOD.value else self.contribution_rate
        return metric_type.evaluate_value(rate / 100)

    def get_improvement_suggestion(self) -> Optional[str]:
        """Get improvement suggestion based on performance"""
        logger.debug(f"💡 Génération suggestion pour {self.student_id}")
        
        metric_type = (MetricType.SOD_PARTICIPATION if self.ritual_type == RitualType.SOD.value 
                      else MetricType.STANDUP_MASTER)
        
        rate = self.success_rate if self.ritual_type == RitualType.SOD.value else self.contribution_rate
        return metric_type.get_improvement_suggestion(rate / 100)

    def to_progress_message(self) -> str:
        """Format progress for reporting"""
        logger.debug(f"📝 Génération message progrès pour {self.student_id}")
        
        evaluation = self.evaluate_performance()
        suggestion = self.get_improvement_suggestion()
        
        if self.ritual_type == RitualType.SOD.value:
            message = f"""
📈 *Progrès {self.ritual_display_name} - {self.student.full_name}* ({self.month_year})
🎯 Participations : {self.participation_count}
✨ Présentations réussies : {self.successful_presentations}
📊 Taux de réussite : {self.success_rate:.1f}% {evaluation['emoji']}
            """.strip()
        else:
            message = f"""
📈 *Progrès {self.ritual_display_name} - {self.student.full_name}* ({self.month_year})
🎯 Participations : {self.participation_count}
💡 Contributions utiles : {self.helpful_contributions}
📊 Taux de contribution : {self.contribution_rate:.1f}% {evaluation['emoji']}
            """.strip()
            
        if suggestion:
            message += f"\n💪 Suggestion : {suggestion}"
            
        return message

    def validate(self) -> None:
        """Validate progress data"""
        logger.debug(f"🔍 Validation progrès pour {self.student_id}")
        
        if self.participation_count < 0:
            logger.error("❌ Nombre de participations négatif")
            raise ValidationException(
                error_code=ErrorCode.INVALID_PARTICIPATION_COUNT,
                count=self.participation_count
            )
            
        if self.successful_presentations > self.participation_count:
            logger.error("❌ Plus de succès que de participations")
            raise ValidationException(
                error_code=ErrorCode.INVALID_SUCCESS_COUNT,
                success_count=self.successful_presentations,
                total_count=self.participation_count
            )

    def __str__(self) -> str:
        """String representation"""
        metric = (f"{self.success_rate:.1f}% de réussite" if self.ritual_type == RitualType.SOD.value 
                 else f"{self.contribution_rate:.1f}% de contributions utiles")
        return (
            f"{self.ritual_emoji} Progrès {self.ritual_display_name} - {self.student.full_name} "
            f"({self.month_year}) : {metric}"
        )