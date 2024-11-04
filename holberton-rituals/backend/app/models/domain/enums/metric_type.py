# app/models/domain/enums/metric_type.py
from enum import Enum
from typing import Dict, Optional, Any
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class MetricType(str, Enum):
    """Types of metrics for analytics ✨"""
    
    SOD_PARTICIPATION = "sod_participation"
    STANDUP_MASTER = "standup_master"
    RITUAL_BALANCE = "ritual_balance"
    COHORT_ACTIVITY = "cohort_activity"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.SOD_PARTICIPATION: {
                "fr": "Participation SOD",
                "en": "SOD Participation"
            },
            self.STANDUP_MASTER: {
                "fr": "Animation Stand-up",
                "en": "Stand-up Mastering"
            },
            self.RITUAL_BALANCE: {
                "fr": "Équilibre des rituels",
                "en": "Ritual Balance"
            },
            self.COHORT_ACTIVITY: {
                "fr": "Activité de cohorte",
                "en": "Cohort Activity"
            }
        }[self]

    @property
    def descriptions(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.SOD_PARTICIPATION: {
                "fr": "Nombre de SOD effectués par étudiant",
                "en": "Number of SODs per student"
            },
            self.STANDUP_MASTER: {
                "fr": "Nombre de fois Scrum Master",
                "en": "Number of times as Scrum Master"
            },
            self.RITUAL_BALANCE: {
                "fr": "Répartition équitable des passages",
                "en": "Fair distribution of participation"
            },
            self.COHORT_ACTIVITY: {
                "fr": "Taux d'activité global par cohorte",
                "en": "Global activity rate per cohort"
            }
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.SOD_PARTICIPATION: 1,
            self.STANDUP_MASTER: 2,
            self.RITUAL_BALANCE: 3,
            self.COHORT_ACTIVITY: 4
        }[self]

    @property
    def is_individual_metric(self) -> bool:
        """Check if metric is individual or group-based"""
        return self in [self.SOD_PARTICIPATION, self.STANDUP_MASTER]

    @property
    def target_values(self) -> Dict[str, float]:
        """Get target values for each metric"""
        return {
            self.SOD_PARTICIPATION: {
                "min": 0.8,  # 80% de participation minimum
                "target": 0.9,  # Objectif de 90%
                "excellent": 0.95  # Excellence à 95%
            },
            self.STANDUP_MASTER: {
                "min": 1,  # Au moins 1 fois par mois
                "target": 2,  # Idéalement 2 fois
                "excellent": 3  # Excellence à 3 fois
            },
            self.RITUAL_BALANCE: {
                "min": 0.7,  # Écart max de 30%
                "target": 0.8,  # Écart idéal de 20%
                "excellent": 0.9  # Excellence à 10% d'écart
            },
            self.COHORT_ACTIVITY: {
                "min": 0.75,  # 75% d'activité minimum
                "target": 0.85,  # Objectif de 85%
                "excellent": 0.95  # Excellence à 95%
            }
        }[self]

    @property
    def calculation_period(self) -> str:
        """Get calculation period for each metric"""
        return {
            self.SOD_PARTICIPATION: "monthly",  # Calculé par mois
            self.STANDUP_MASTER: "monthly",     # Calculé par mois
            self.RITUAL_BALANCE: "weekly",      # Calculé par semaine
            self.COHORT_ACTIVITY: "monthly"     # Calculé par mois
        }[self]

    @classmethod
    def from_string(cls, value: str) -> Optional["MetricType"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en MetricType")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Type de métrique invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_METRIC_TYPE,
                metric_type=value
            )

    def evaluate_value(self, value: float, period: str = None) -> Dict[str, Any]:
        """Evaluate a metric value against targets"""
        logger.debug(f"📊 Évaluation de {self.value}: {value} (période: {period})")
        
        if not isinstance(value, (int, float)):
            logger.error(f"❌ Type invalide pour value: {type(value)}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_DATA_TYPE,
                field="value",
                expected="number"
            )

        targets = self.target_values
        
        # Détermination du statut
        if value < targets["min"]:
            status = "warning"
            message = "En dessous des attentes"
            emoji = "⚠️"
        elif value < targets["target"]:
            status = "ok"
            message = "Acceptable"
            emoji = "✅"
        elif value < targets["excellent"]:
            status = "good"
            message = "Bon niveau"
            emoji = "🌟"
        else:
            status = "excellent"
            message = "Excellent niveau"
            emoji = "👑"

        logger.info(f"{emoji} {self.display_names['fr']}: {value} -> {status}")
        
        return {
            "value": value,
            "status": status,
            "message": message,
            "emoji": emoji,
            "targets": targets,
            "period": period or self.calculation_period
        }

    def get_improvement_suggestion(self, current_value: float, lang: str = "fr") -> Optional[str]:
        """Get improvement suggestion based on current value"""
        logger.debug(f"💡 Génération suggestion pour {self.value}: {current_value}")
        
        targets = self.target_values
        if current_value >= targets["min"]:
            return None

        suggestions = {
            self.SOD_PARTICIPATION: {
                "fr": "Participer plus activement aux SODs",
                "en": "Participate more actively in SODs"
            },
            self.STANDUP_MASTER: {
                "fr": "Se porter volontaire comme Scrum Master",
                "en": "Volunteer as Scrum Master"
            },
            self.RITUAL_BALANCE: {
                "fr": "Participer de manière plus régulière",
                "en": "Participate more regularly"
            },
            self.COHORT_ACTIVITY: {
                "fr": "Encourager la participation de tous",
                "en": "Encourage everyone's participation"
            }
        }
        
        suggestion = suggestions.get(self, {}).get(lang)
        if suggestion:
            logger.info(f"💡 Suggestion générée: {suggestion}")
        
        return suggestion

    def __str__(self) -> str:
        """String representation"""
        return f"{self.display_names['fr']} ({self.value})"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "description": self.descriptions[lang],
            "display_order": self.display_order,
            "is_individual": self.is_individual_metric,
            "target_values": self.target_values,
            "calculation_period": self.calculation_period
        }