# app/models/schemas/users/student.py
from datetime import date
from typing import Optional, List, Dict
from pydantic import Field, UUID4
from ..common.base import BaseSchema, ResponseSchema, I18nSchema
from ...domain.enums.ritual_type import RitualType
from ...domain.enums.curriculum_type import CurriculumType

class StudentBase(BaseSchema):
    """Base schema for student data ✨"""
    
    current_cohort_id: UUID4 = Field(
        ..., 
        description="ID de la cohorte actuelle"
    )
    curriculum_type: CurriculumType = Field(
        ...,
        description="Type de curriculum"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "current_cohort_id": "123e4567-e89b-12d3-a456-426614174000",
                "curriculum_type": "fundamentals"
            }
        }

class StudentCreate(StudentBase):
    """Schema for student creation"""
    
    user_id: UUID4 = Field(
        ..., 
        description="ID de l'utilisateur associé"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "user_id": "123e4567-e89b-12d3-a456-426614174000",
                "current_cohort_id": "123e4567-e89b-12d3-a456-426614174000",
                "curriculum_type": "fundamentals"
            }
        }

class RitualStats(BaseSchema):
    """Schema for ritual statistics"""
    
    ritual_type: RitualType = Field(
        ...,
        description="Type de rituel"
    )
    count: int = Field(
        ...,
        ge=0,
        description="Nombre de participations"
    )
    last_date: Optional[date] = Field(
        None,
        description="Date de dernière participation"
    )
    next_scheduled: Optional[date] = Field(
        None,
        description="Prochaine participation prévue"
    )
    success_rate: Optional[float] = Field(
        None,
        ge=0,
        le=100,
        description="Taux de réussite en pourcentage"
    )
    improvement_suggestion: Optional[str] = Field(
        None,
        description="Suggestion d'amélioration"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "count": 5,
                "last_date": "2024-02-01",
                "next_scheduled": "2024-02-15",
                "success_rate": 92.5,
                "improvement_suggestion": "Penser à chronométrer la présentation"
            }
        }

class StudentResponse(ResponseSchema, StudentBase):
    """Schema for student responses"""
    
    user: Dict = Field(  # UserResponse sera importé plus tard
        ...,
        description="Informations utilisateur"
    )
    sod_count: int = Field(
        default=0,
        description="Nombre total de SODs"
    )
    standup_count: int = Field(
        default=0,
        description="Nombre total de standups"
    )
    last_sod_date: Optional[date] = Field(
        None,
        description="Date du dernier SOD"
    )
    last_standup_date: Optional[date] = Field(
        None,
        description="Date du dernier standup"
    )
    is_active: bool = Field(
        True,
        description="Statut d'activité"
    )
    rituals: List[RitualStats] = Field(
        default_factory=list,
        description="Statistiques par rituel"
    )
    cohort_name: str = Field(
        ...,
        description="Nom de la cohorte"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "current_cohort_id": "123e4567-e89b-12d3-a456-426614174000",
                "cohort_name": "C#22",
                "curriculum_type": "fundamentals",
                "user": {
                    "id": "123e4567-e89b-12d3-a456-426614174000",
                    "email": "ada@holberton.fr",
                    "first_name": "Ada",
                    "last_name": "Lovelace",
                    "role": "STUDENT"
                },
                "sod_count": 5,
                "standup_count": 15,
                "last_sod_date": "2024-02-01",
                "last_standup_date": "2024-02-11",
                "is_active": True,
                "rituals": [
                    {
                        "ritual_type": "SOD",
                        "count": 5,
                        "last_date": "2024-02-01",
                        "next_scheduled": "2024-02-15",
                        "success_rate": 92.5,
                        "improvement_suggestion": "Penser à chronométrer la présentation"
                    }
                ],
                "created_at": "2024-02-01T12:00:00Z",
                "updated_at": "2024-02-11T14:30:00Z"
            }
        }

class StudentProgressResponse(BaseSchema):
    """Schema for student progress overview"""
    
    student: StudentResponse = Field(
        ...,
        description="Informations étudiant"
    )
    current_month_stats: List[RitualStats] = Field(
        ...,
        description="Statistiques du mois en cours"
    )
    total_presentations: int = Field(
        ...,
        ge=0,
        description="Nombre total de présentations"
    )
    average_success_rate: float = Field(
        ...,
        ge=0,
        le=100,
        description="Taux de réussite moyen"
    )
    improvement_areas: List[I18nSchema] = Field(
        ...,
        description="Axes d'amélioration"
    )
    achievements: List[I18nSchema] = Field(
        ...,
        description="Réalisations"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "student": {},  # StudentResponse example
                "current_month_stats": [
                    {
                        "ritual_type": "SOD",
                        "count": 2,
                        "success_rate": 95.0,
                        "improvement_suggestion": "Penser à chronométrer la présentation"
                    }
                ],
                "total_presentations": 20,
                "average_success_rate": 93.5,
                "improvement_areas": [
                    {
                        "fr": "Gestion du temps pendant les SODs",
                        "en": "Time management during SODs"
                    }
                ],
                "achievements": [
                    {
                        "fr": "Premier SOD avec note parfaite",
                        "en": "First perfect score SOD"
                    }
                ]
            }
        }