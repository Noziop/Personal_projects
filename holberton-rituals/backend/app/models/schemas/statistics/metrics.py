# app/models/schemas/statistics/metrics.py
from typing import Dict, List, Optional
from datetime import date
from pydantic import Field, UUID4
from ..common.base import BaseSchema, I18nSchema
from ..enums.metric import MetricTypeSchema

class MetricDataPoint(BaseSchema):
    """Schema for individual metric data points ✨"""
    
    measurement_date: date = Field(
        ...,
        description="Date de la mesure"
    )
    value: float = Field(
        ...,
        ge=0,
        description="Valeur mesurée"
    )
    target: float = Field(
        ...,
        ge=0,
        description="Valeur cible"
    )
    evaluation: I18nSchema = Field(
        ...,
        description="Évaluation qualitative"
    )
    trend: str = Field(
        ...,
        pattern="^(improving|stable|declining)$",
        description="Tendance"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "measurement_date": "2024-02-15",
                "value": 85.5,
                "target": 90.0,
                "evaluation": {
                    "fr": "Proche de l'objectif",
                    "en": "Close to target"
                },
                "trend": "improving"
            }
        }

class MetricPeriodStats(BaseSchema):
    """Schema for metric period statistics"""
    
    metric_type: MetricTypeSchema = Field(
        ...,
        description="Type de métrique"
    )
    period_start: date = Field(
        ...,
        description="Début de la période"
    )
    period_end: date = Field(
        ...,
        description="Fin de la période"
    )
    data_points: List[MetricDataPoint] = Field(
        ...,
        description="Points de données"
    )
    average_value: float = Field(
        ...,
        ge=0,
        description="Valeur moyenne"
    )
    min_value: float = Field(
        ...,
        ge=0,
        description="Valeur minimum"
    )
    max_value: float = Field(
        ...,
        ge=0,
        description="Valeur maximum"
    )
    trend_analysis: I18nSchema = Field(
        ...,
        description="Analyse de la tendance"
    )
    improvement_suggestions: List[I18nSchema] = Field(
        default_factory=list,
        description="Suggestions d'amélioration"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "metric_type": {
                    "value": "sod_participation",
                    "display_names": {
                        "fr": "Participation SOD",
                        "en": "SOD Participation"
                    }
                },
                "period_start": "2024-02-01",
                "period_end": "2024-02-29",
                "data_points": [
                    {
                        "date": "2024-02-15",
                        "value": 85.5,
                        "target": 90.0,
                        "evaluation": {
                            "fr": "Proche de l'objectif",
                            "en": "Close to target"
                        },
                        "trend": "improving"
                    }
                ],
                "average_value": 85.5,
                "min_value": 80.0,
                "max_value": 95.0,
                "trend_analysis": {
                    "fr": "Tendance positive sur le mois",
                    "en": "Positive trend over the month"
                },
                "improvement_suggestions": [
                    {
                        "fr": "Participer à un SOD supplémentaire",
                        "en": "Participate in one more SOD"
                    }
                ]
            }
        }

class MetricComparison(BaseSchema):
    """Schema for metric comparisons"""
    
    entity_id: UUID4 = Field(
        ...,
        description="ID de l'entité (étudiant/cohorte)"
    )
    entity_name: str = Field(
        ...,
        description="Nom de l'entité"
    )
    metric_value: float = Field(
        ...,
        ge=0,
        description="Valeur de la métrique"
    )
    comparison_type: str = Field(
        ...,
        pattern="^(cohort_average|personal_best|target)$",
        description="Type de comparaison"
    )
    difference: float = Field(
        ...,
        description="Différence (en %)"
    )
    ranking: Optional[int] = Field(
        None,
        description="Classement (si applicable)"
    )
    percentile: Optional[float] = Field(
        None,
        ge=0,
        le=100,
        description="Percentile (si applicable)"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "entity_id": "123e4567-e89b-12d3-a456-426614174000",
                "entity_name": "Ada Lovelace",
                "metric_value": 85.5,
                "comparison_type": "cohort_average",
                "difference": 5.5,
                "ranking": 3,
                "percentile": 85.0
            }
        }