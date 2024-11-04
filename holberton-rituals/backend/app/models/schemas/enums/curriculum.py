# app/models/schemas/enums/curriculum.py
from typing import Dict, List
from pydantic import Field
from ..common.base import BaseSchema, I18nSchema

class CurriculumTypeSchema(BaseSchema):
    """Schema for curriculum type enumeration ✨"""
    
    value: str = Field(
        ...,
        description="Identifiant unique du curriculum"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    duration_months: int = Field(
        ...,
        ge=1,
        description="Durée en mois"
    )
    ritual_frequency: Dict[str, int] = Field(
        ...,
        description="Fréquence des rituels par semaine"
    )
    ritual_days: Dict[str, List[str]] = Field(
        ...,
        description="Jours des rituels"
    )
    is_apprenticeship: bool = Field(
        False,
        description="Programme en alternance"
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
                "value": "fundamentals",
                "display_names": {
                    "fr": "Formation Fondamentale",
                    "en": "Fundamentals Training"
                },
                "descriptions": {
                    "fr": "Formation intensive de 9 mois aux fondamentaux du développement",
                    "en": "9-month intensive training in development fundamentals"
                },
                "duration_months": 9,
                "ritual_frequency": {
                    "sod": 3,
                    "standup": 4
                },
                "ritual_days": {
                    "sod": ["tuesday", "wednesday", "thursday"],
                    "standup": ["monday", "tuesday", "wednesday", "thursday"]
                },
                "is_apprenticeship": False,
                "emoji": "🎓",
                "display_order": 1
            }
        }

class CurriculumTypeResponse(CurriculumTypeSchema):
    """Schema for curriculum type responses with additional info"""
    
    student_count: int = Field(
        0,
        description="Nombre d'étudiants actifs"
    )
    active_cohorts: int = Field(
        0,
        description="Nombre de cohortes actives"
    )
    next_start_date: str = Field(
        None,
        description="Prochaine date de début"
    )
    success_rate: float = Field(
        None,
        ge=0,
        le=100,
        description="Taux de réussite global"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "value": "fundamentals",
                "display_names": {
                    "fr": "Formation Fondamentale",
                    "en": "Fundamentals Training"
                },
                "descriptions": {
                    "fr": "Formation intensive de 9 mois aux fondamentaux du développement",
                    "en": "9-month intensive training in development fundamentals"
                },
                "duration_months": 9,
                "ritual_frequency": {
                    "sod": 3,
                    "standup": 4
                },
                "ritual_days": {
                    "sod": ["tuesday", "wednesday", "thursday"],
                    "standup": ["monday", "tuesday", "wednesday", "thursday"]
                },
                "is_apprenticeship": False,
                "emoji": "🎓",
                "display_order": 1,
                "student_count": 45,
                "active_cohorts": 2,
                "next_start_date": "2024-03-01",
                "success_rate": 92.5
            }
        }