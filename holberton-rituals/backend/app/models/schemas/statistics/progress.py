# app/models/schemas/statistics/progress.py
from typing import Dict, List, Optional
from datetime import date
from pydantic import Field, UUID4
from ..common.base import BaseSchema, I18nSchema
from ..enums.ritual_type import RitualTypeSchema

class ProgressSnapshot(BaseSchema):
    """Schema for progress snapshots ✨"""
    
    student_id: UUID4 = Field(
        ...,
        description="ID de l'étudiant"
    )
    ritual_type: RitualTypeSchema = Field(
        ...,
        description="Type de rituel"
    )
    snapshot_date: date = Field(
        ...,
        description="Date du snapshot"
    )
    participation_count: int = Field(
        0,
        ge=0,
        description="Nombre de participations"
    )
    success_rate: float = Field(
        0.0,
        ge=0,
        le=100,
        description="Taux de réussite"
    )
    skills_progress: Dict[str, float] = Field(
        default_factory=dict,
        description="Progression par compétence"
    )
    achievements: List[I18nSchema] = Field(
        default_factory=list,
        description="Réalisations débloquées"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "student_id": "123e4567-e89b-12d3-a456-426614174000",
                "ritual_type": {
                    "value": "SOD",
                    "display_names": {
                        "fr": "Standup of Death",
                        "en": "Standup of Death"
                    }
                },
                "snapshot_date": "2024-02-15",
                "participation_count": 5,
                "success_rate": 92.5,
                "skills_progress": {
                    "presentation": 85.0,
                    "technical_mastery": 90.0
                },
                "achievements": [
                    {
                        "fr": "Premier SOD parfait",
                        "en": "First perfect SOD"
                    }
                ]
            }
        }

class ProgressReport(BaseSchema):
    """Schema for progress reports"""
    
    student_id: UUID4 = Field(
        ...,
        description="ID de l'étudiant"
    )
    period_start: date = Field(
        ...,
        description="Début de la période"
    )
    period_end: date = Field(
        ...,
        description="Fin de la période"
    )
    snapshots: List[ProgressSnapshot] = Field(
        ...,
        description="Snapshots de progression"
    )
    overall_progress: float = Field(
        ...,
        ge=0,
        le=100,
        description="Progression globale"
    )
    strengths: List[I18nSchema] = Field(
        ...,
        description="Points forts identifiés"
    )
    areas_for_improvement: List[I18nSchema] = Field(
        ...,
        description="Axes d'amélioration"
    )
    recommendations: List[I18nSchema] = Field(
        ...,
        description="Recommandations personnalisées"
    )
    next_milestones: List[Dict] = Field(
        ...,
        description="Prochains objectifs"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "student_id": "123e4567-e89b-12d3-a456-426614174000",
                "period_start": "2024-02-01",
                "period_end": "2024-02-29",
                "snapshots": [],  # Liste de ProgressSnapshot
                "overall_progress": 88.5,
                "strengths": [
                    {
                        "fr": "Excellence technique",
                        "en": "Technical excellence"
                    }
                ],
                "areas_for_improvement": [
                    {
                        "fr": "Gestion du temps",
                        "en": "Time management"
                    }
                ],
                "recommendations": [
                    {
                        "fr": "Participer à plus de SODs",
                        "en": "Participate in more SODs"
                    }
                ],
                "next_milestones": [
                    {
                        "type": "achievement",
                        "name": {
                            "fr": "5 SODs consécutifs réussis",
                            "en": "5 consecutive successful SODs"
                        },
                        "progress": 80
                    }
                ]
            }
        }

class CohortProgress(BaseSchema):
    """Schema for cohort-wide progress tracking"""
    
    cohort_id: UUID4 = Field(
        ...,
        description="ID de la cohorte"
    )
    period: str = Field(
        ...,
        description="Période (week/month/quarter)"
    )
    average_progress: float = Field(
        ...,
        ge=0,
        le=100,
        description="Progression moyenne"
    )
    top_performers: List[Dict] = Field(
        ...,
        max_items=5,
        description="Top 5 des étudiants"
    )
    most_improved: List[Dict] = Field(
        ...,
        max_items=5,
        description="Meilleures progressions"
    )
    ritual_statistics: Dict[str, Dict] = Field(
        ...,
        description="Statistiques par rituel"
    )
    cohort_achievements: List[I18nSchema] = Field(
        ...,
        description="Réalisations de la cohorte"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "cohort_id": "123e4567-e89b-12d3-a456-426614174000",
                "period": "month",
                "average_progress": 85.5,
                "top_performers": [
                    {
                        "student_id": "123e4567-e89b-12d3-a456-426614174001",
                        "name": "Ada Lovelace",
                        "score": 95.0
                    }
                ],
                "most_improved": [
                    {
                        "student_id": "123e4567-e89b-12d3-a456-426614174002",
                        "name": "Grace Hopper",
                        "improvement": 15.0
                    }
                ],
                "ritual_statistics": {
                    "SOD": {
                        "participation_rate": 92.0,
                        "success_rate": 88.5
                    }
                },
                "cohort_achievements": [
                    {
                        "fr": "100% de participation cette semaine !",
                        "en": "100% participation this week!"
                    }
                ]
            }
        }