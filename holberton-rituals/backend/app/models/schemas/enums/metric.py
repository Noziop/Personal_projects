# app/models/schemas/enums/metric.py
from typing import Dict, List, Optional
from pydantic import Field, confloat
from ..common.base import BaseSchema, I18nSchema

class MetricThreshold(BaseSchema):
    """Schema for metric evaluation thresholds ✨"""
    
    min_value: float = Field(
        ...,
        ge=0,
        le=1,
        description="Valeur minimum du seuil"
    )
    max_value: float = Field(
        ...,
        ge=0,
        le=1,
        description="Valeur maximum du seuil"
    )
    evaluation: I18nSchema = Field(
        ...,
        description="Message d'évaluation"
    )
    emoji: str = Field(
        ...,
        description="Emoji représentatif"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "min_value": 0.8,
                "max_value": 1.0,
                "evaluation": {
                    "fr": "Excellent",
                    "en": "Excellent"
                },
                "emoji": "🌟"
            }
        }
        
class MetricTypeSchema(BaseSchema):
    """Schema for metric type enumeration"""
    
    value: str = Field(
        ...,
        description="Identifiant unique de la métrique"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    unit: str = Field(
        ...,
        description="Unité de mesure"
    )
    is_individual: bool = Field(
        True,
        description="Métrique individuelle ou de groupe"
    )
    calculation_period: str = Field(
        ...,
        pattern="^(daily|weekly|monthly)$",
        description="Période de calcul"
    )
    thresholds: List[MetricThreshold] = Field(
        ...,
        description="Seuils d'évaluation"
    )
    target_value: float = Field(
        ...,
        ge=0,
        le=100,
        description="Valeur cible"
    )
    emoji: str = Field(
        ...,
        description="Emoji représentatif"
    )
    display_order: int = Field(
        ...,
        ge=0,
        description="Ordre d'affichage"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "value": "sod_participation",
                "display_names": {
                    "fr": "Participation SOD",
                    "en": "SOD Participation"
                },
                "descriptions": {
                    "fr": "Taux de participation aux SODs",
                    "en": "SOD participation rate"
                },
                "unit": "%",
                "is_individual": True,
                "calculation_period": "monthly",
                "thresholds": [
                    {
                        "min_value": 0.8,
                        "max_value": 1.0,
                        "evaluation": {
                            "fr": "Excellent",
                            "en": "Excellent"
                        },
                        "emoji": "🌟"
                    }
                ],
                "target_value": 90.0,
                "emoji": "📊",
                "display_order": 1
            }
        }

class MetricValue(BaseSchema):
    """Schema for metric values"""
    
    value: float = Field(
        ...,
        ge=0,
        description="Valeur mesurée"
    )
    target_achieved: bool = Field(
        ...,
        description="Objectif atteint"
    )
    trend: str = Field(
        ...,
        pattern="^(improving|stable|declining)$",
        description="Tendance"
    )
    evaluation: I18nSchema = Field(
        ...,
        description="Évaluation"
    )
    suggestion: Optional[I18nSchema] = Field(
        None,
        description="Suggestion d'amélioration"
    )
    emoji: str = Field(
        ...,
        description="Emoji d'évaluation"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "value": 85.5,
                "target_achieved": False,
                "trend": "improving",
                "evaluation": {
                    "fr": "Très bien, proche de l'objectif",
                    "en": "Very good, close to target"
                },
                "suggestion": {
                    "fr": "Participer à un SOD supplémentaire ce mois-ci",
                    "en": "Participate in one more SOD this month"
                },
                "emoji": "📈"
            }
        }

class MetricSummary(BaseSchema):
    """Schema for metric summaries"""
    
    metric_type: MetricTypeSchema = Field(
        ...,
        description="Type de métrique"
    )
    current_value: MetricValue = Field(
        ...,
        description="Valeur actuelle"
    )
    historical_values: List[Dict[str, float]] = Field(
        ...,
        description="Valeurs historiques"
    )
    progress_chart: str = Field(
        ...,
        description="Graphique ASCII de progression"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "metric_type": {
                    # Voir exemple MetricTypeSchema
                },
                "current_value": {
                    # Voir exemple MetricValue
                },
                "historical_values": [
                    {"2024-01": 82.5},
                    {"2024-02": 85.5}
                ],
                "progress_chart": "📊 ▁▂▃▅▆▇"
            }
        }