# app/models/schemas/rituals/standup.py
from typing import Optional, List, Dict
from datetime import time
from pydantic import Field, UUID4
from .base import RitualAssignmentBase, ResponseSchema, BaseSchema
from ..common.base import I18nSchema

class StandupCreate(RitualAssignmentBase):
    """Schema for Standup creation ✨"""
    
    cohort_id: UUID4 = Field(
        ...,
        description="ID de la cohorte"
    )
    meeting_time: time = Field(
        default=time(11, 45),
        description="Heure du standup"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "STANDUP",
                "session_date": "2024-02-15",
                "session_time": "11:45:00",
                "student_id": "123e4567-e89b-12d3-a456-426614174000",
                "cohort_id": "123e4567-e89b-12d3-a456-426614174001",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174002"
            }
        }

class StandupReport(BaseSchema):
    """Schema for Standup reports"""
    
    bugs_report: Optional[I18nSchema] = Field(
        None,
        description="Rapport des bugs"
    )
    common_difficulties: Optional[I18nSchema] = Field(
        None,
        description="Difficultés communes"
    )
    shared_tips: Optional[I18nSchema] = Field(
        None,
        description="Astuces partagées"
    )
    conclusion: Optional[I18nSchema] = Field(
        None,
        description="Conclusion"
    )
    projects_of_week: List[I18nSchema] = Field(
        ...,
        description="Projets de la semaine"
    )
    attendance: Dict[str, int] = Field(
        ...,
        description="Statistiques de présence",
        example={
            "total": 18,
            "present": 15,
            "remote": 3,
            "absent": 0
        }
    )
    mood_summary: Dict[str, int] = Field(
        ...,
        description="Résumé des humeurs",
        example={
            "great": 10,
            "good": 5,
            "neutral": 2,
            "struggling": 1
        }
    )

    class Config:
        json_schema_extra = {
            "example": {
                "bugs_report": {
                    "fr": "Problème avec l'authentification OAuth",
                    "en": "Issue with OAuth authentication"
                },
                "common_difficulties": {
                    "fr": "Gestion des migrations Alembic",
                    "en": "Alembic migrations management"
                },
                "shared_tips": {
                    "fr": "Utiliser black pour le formatage automatique",
                    "en": "Use black for automatic formatting"
                },
                "conclusion": {
                    "fr": "Bonne progression générale",
                    "en": "Good overall progress"
                },
                "projects_of_week": [
                    {
                        "fr": "API REST",
                        "en": "REST API"
                    },
                    {
                        "fr": "Authentification JWT",
                        "en": "JWT Authentication"
                    }
                ],
                "attendance": {
                    "total": 18,
                    "present": 15,
                    "remote": 3,
                    "absent": 0
                },
                "mood_summary": {
                    "great": 10,
                    "good": 5,
                    "neutral": 2,
                    "struggling": 1
                }
            }
        }

class StandupResponse(ResponseSchema, StandupCreate):
    """Schema for Standup responses"""
    
    is_completed: bool = Field(
        False,
        description="Standup terminé"
    )
    report: Optional[StandupReport] = Field(
        None,
        description="Rapport du standup"
    )
    scrum_master_name: str = Field(
        ...,
        description="Nom du Scrum Master"
    )
    cohort_name: str = Field(
        ...,
        description="Nom de la cohorte"
    )
    status_emoji: str = Field(
        ...,
        description="Emoji de statut"
    )
    mood_emoji: str = Field(
        "😊",
        description="Emoji d'ambiance générale"
    )
    
    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "ritual_type": "STANDUP",
                "session_date": "2024-02-15",
                "session_time": "11:45:00",
                "student_id": "123e4567-e89b-12d3-a456-426614174001",
                "cohort_id": "123e4567-e89b-12d3-a456-426614174002",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174003",
                "is_completed": True,
                "scrum_master_name": "Ada Lovelace",
                "cohort_name": "C#22",
                "status_emoji": "✅",
                "mood_emoji": "😊",
                "report": {
                    # Voir exemple StandupReport
                },
                "created_at": "2024-02-15T09:00:00Z",
                "updated_at": "2024-02-15T09:30:00Z"
            }
        }