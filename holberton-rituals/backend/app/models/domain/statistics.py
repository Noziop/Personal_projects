# models/domain/statistics.py
from datetime import date
from typing import Dict, List, Optional
from sqlalchemy import Column, String, JSON, Date, ForeignKey, UniqueConstraint, Index, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType
from .enums.metric_type import MetricType

logger = settings.logger  # Notre super logger loguru ! 👑

class RitualStatistics(Base):
    """Statistics tracking for rituals by cohort and period ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    cohort_id = Column(UUID, ForeignKey('cohorts.id'), nullable=False)
    ritual_type = Column(String(20), ForeignKey('enum_ritual_type.value'), nullable=False)
    period_start = Column(Date, nullable=False)
    period_end = Column(Date, nullable=False)
    stats_data = Column(JSON, nullable=False)

    # Relationships
    cohort = relationship("Cohort", back_populates="ritual_statistics")

    # Constraints and indexes
    __table_args__ = (
        UniqueConstraint('cohort_id', 'ritual_type', 'period_start',
                        name='uq_stats_period'),
        Index('idx_stats_period', 'period_start', 'period_end'),
    )

    @property
    def ritual_display_name(self) -> str:
        """Get localized ritual name"""
        return RitualType(self.ritual_type).display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return RitualType(self.ritual_type).emoji

    def format_date(self, date_value: date) -> str:
        """Format date in French"""
        return date_value.strftime("%d/%m/%Y")

    @property
    def period_days(self) -> int:
        """Calculate number of days in period"""
        logger.debug(f"📅 Calcul durée période pour stats {self.id}")
        return (self.period_end - self.period_start).days + 1

    def get_participation_rate(self) -> float:
        """Calculate participation rate from stats data"""
        logger.debug(f"📊 Calcul taux participation pour stats {self.id}")
        total_sessions = self.stats_data.get('total_sessions', 0)
        
        if not total_sessions:
            logger.warning("⚠️ Aucune session dans la période")
            return 0.0
            
        attended = self.stats_data.get('attended_sessions', 0)
        rate = (attended / total_sessions) * 100
        logger.info(f"✨ Taux participation: {rate:.1f}% ({attended}/{total_sessions})")
        return rate

    def get_average_score(self) -> float:
        """Get average score for SOD presentations"""
        logger.debug(f"📊 Calcul score moyen pour stats {self.id}")
        
        if self.ritual_type != RitualType.SOD.value:
            return 0.0
            
        total_presentations = self.stats_data.get('total_presentations', 0)
        if not total_presentations:
            logger.warning("⚠️ Aucune présentation dans la période")
            return 0.0
            
        total_score = self.stats_data.get('total_score', 0)
        avg_score = total_score / total_presentations
        logger.info(f"✨ Score moyen: {avg_score:.1f}/100")
        return avg_score

    def get_metric_value(self, metric_type: MetricType) -> float:
        """Get value for specific metric type"""
        logger.debug(f"📊 Récupération métrique {metric_type.value}")
        value = self.stats_data.get(metric_type.value, 0.0)
        
        if value:
            evaluation = metric_type.evaluate_value(value)
            logger.info(f"{evaluation['emoji']} {metric_type.display_names['fr']}: {value:.1f}")
            
        return value

    def validate(self) -> None:
        """Validate statistics data"""
        logger.debug(f"🔍 Validation stats {self.id}")
        
        if self.period_end <= self.period_start:
            logger.error("❌ Période invalide")
            raise ValidationException(
                error_code=ErrorCode.INVALID_STATS_PERIOD,
                start_date=self.period_start.isoformat(),
                end_date=self.period_end.isoformat()
            )
            
        if not isinstance(self.stats_data, dict):
            logger.error("❌ Format de données invalide")
            raise ValidationException(
                error_code=ErrorCode.INVALID_STATS_DATA,
                stats_id=str(self.id)
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour stats {self.id}")
        return {
            "id": str(self.id),
            "cohort_id": str(self.cohort_id),
            "cohort_name": self.cohort.name,
            "ritual_type": self.ritual_type,
            "ritual_display_name": self.ritual_display_name,
            "period_start": self.period_start.isoformat(),
            "period_end": self.period_end.isoformat(),
            "period_days": self.period_days,
            "participation_rate": self.get_participation_rate(),
            "average_score": self.get_average_score() if self.ritual_type == RitualType.SOD.value else None,
            "metrics": {
                metric.value: {
                    "value": self.get_metric_value(metric),
                    "evaluation": metric.evaluate_value(self.get_metric_value(metric))
                }
                for metric in MetricType
            }
        }

    def to_report_message(self) -> str:
        """Format statistics for reporting"""
        logger.debug(f"📝 Génération rapport pour stats {self.id}")
        is_sod = self.ritual_type == RitualType.SOD.value
        return f"""
{self.ritual_emoji} *Statistiques {self.ritual_display_name} - {self.cohort.name}*
📅 Période : du {self.format_date(self.period_start)} au {self.format_date(self.period_end)}
👥 Taux de participation : {self.get_participation_rate():.1f}%
{'🎯 Score moyen : ' + f"{self.get_average_score():.1f}/100" if is_sod else ''}
📊 Métriques détaillées :
{self.get_detailed_metrics()}
        """.strip()

    def get_detailed_metrics(self) -> str:
        """Get detailed metrics formatted"""
        logger.debug(f"📊 Formatage métriques détaillées pour stats {self.id}")
        metrics = []
        for metric in MetricType:
            value = self.get_metric_value(metric)
            if value > 0:
                evaluation = metric.evaluate_value(value)
                metrics.append(
                    f"• {evaluation['emoji']} {metric.display_names['fr']} : "
                    f"{value:.1f} ({evaluation['message']})"
                )
        return "\n".join(metrics) if metrics else "Aucune métrique détaillée disponible"

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.ritual_emoji} Statistiques {self.ritual_display_name} - "
            f"{self.cohort.name} "
            f"(du {self.format_date(self.period_start)} "
            f"au {self.format_date(self.period_end)})"
        )