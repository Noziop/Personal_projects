# app/models/schemas/rituals/sod.py
from typing import Optional, List
from datetime import time
from pydantic import Field, AnyUrl, UUID4
from .base import RitualAssignmentBase, ResponseSchema, BaseSchema
from ..common.base import I18nSchema

class SODCreate(RitualAssignmentBase):
    """Schema for SOD creation ✨"""
    
    evaluator_id: UUID4 = Field(
        ...,
        description="ID de l'évaluateur"
    )
    presentation_time: time = Field(
        default=time(11, 30),
        description="Heure de la présentation"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "session_date": "2024-02-15",
                "session_time": "11:30:00",
                "student_id": "123e4567-e89b-12d3-a456-426614174000",
                "evaluator_id": "123e4567-e89b-12d3-a456-426614174001",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174002",
                "is_replacement": False
            }
        }

class SODResponse(ResponseSchema, SODCreate):
    """Schema for SOD responses"""
    
    is_completed: bool = Field(
        False,
        description="SOD terminé"
    )
    presentation_url: Optional[AnyUrl] = Field(
        None,
        description="URL des slides"
    )
    feedback_score: Optional[float] = Field(
        None,
        ge=0,
        le=100,
        description="Score du feedback"
    )
    student_name: str = Field(
        ...,
        description="Nom du présentateur"
    )
    evaluator_name: str = Field(
        ...,
        description="Nom de l'évaluateur"
    )
    cohort_name: str = Field(
        ...,
        description="Nom de la cohorte"
    )
    status_emoji: str = Field(
        ...,
        description="Emoji de statut"
    )
    
    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "ritual_type": "SOD",
                "session_date": "2024-02-15",
                "session_time": "11:30:00",
                "student_id": "123e4567-e89b-12d3-a456-426614174001",
                "student_name": "Ada Lovelace",
                "evaluator_id": "123e4567-e89b-12d3-a456-426614174002",
                "evaluator_name": "Grace Hopper",
                "cohort_name": "C#22",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174003",
                "is_completed": True,
                "presentation_url": "https://slides.holberton.fr/sod/123",
                "feedback_score": 95.5,
                "status_emoji": "✅",
                "created_at": "2024-02-01T12:00:00Z",
                "updated_at": "2024-02-15T14:30:00Z"
            }
        }

class SODFeedback(BaseSchema):
    """Schema for SOD feedback"""
    
    presentation_score: int = Field(
        ..., 
        ge=0, 
        le=100,
        description="Score global"
    )
    timing_score: int = Field(
        ..., 
        ge=0, 
        le=10,
        description="Score gestion du temps"
    )
    clarity_score: int = Field(
        ..., 
        ge=0, 
        le=10,
        description="Score clarté"
    )
    technical_score: int = Field(
        ..., 
        ge=0, 
        le=10,
        description="Score technique"
    )
    strengths: List[I18nSchema] = Field(
        ...,
        description="Points forts"
    )
    improvements: List[I18nSchema] = Field(
        ...,
        description="Axes d'amélioration"
    )
    comments: Optional[I18nSchema] = Field(
        None,
        description="Commentaires additionnels"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "presentation_score": 95,
                "timing_score": 9,
                "clarity_score": 10,
                "technical_score": 9,
                "strengths": [
                    {
                        "fr": "Excellente maîtrise du sujet",
                        "en": "Excellent subject mastery"
                    },
                    {
                        "fr": "Présentation claire et structurée",
                        "en": "Clear and structured presentation"
                    }
                ],
                "improvements": [
                    {
                        "fr": "Gérer un peu mieux le temps des questions",
                        "en": "Better manage Q&A time"
                    }
                ],
                "comments": {
                    "fr": "Super présentation, continue comme ça !",
                    "en": "Great presentation, keep it up!"
                }
            }
        }