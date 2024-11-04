# app/models/schemas/rituals/feedback.py
from datetime import date
from typing import Optional, Dict, Any, List, Union
from pydantic import Field, UUID4, validator, AnyUrl
from ..common.base import BaseSchema, ResponseSchema, I18nSchema
from ...domain.enums.ritual_type import RitualType

class FeedbackTemplateField(BaseSchema):
    """Schema for feedback template fields ✨"""
    
    type: str = Field(
        ..., 
        regex="^(text|rating|boolean|select|multi_select)$",
        description="Type du champ"
    )
    label: I18nSchema = Field(
        ...,
        description="Libellé du champ"
    )
    required: bool = Field(
        True,
        description="Champ obligatoire"
    )
    max_points: Optional[int] = Field(
        None,
        ge=0,
        le=100,
        description="Points maximum"
    )
    options: Optional[List[I18nSchema]] = Field(
        None,
        description="Options pour les champs select/multi_select"
    )
    description: Optional[I18nSchema] = Field(
        None,
        description="Description du champ"
    )
    validation_rules: Optional[Dict[str, Any]] = Field(
        None,
        description="Règles de validation spécifiques"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "type": "rating",
                "label": {
                    "fr": "Clarté de l'explication",
                    "en": "Explanation clarity"
                },
                "required": True,
                "max_points": 10,
                "description": {
                    "fr": "Évaluer la clarté globale de la présentation",
                    "en": "Evaluate the overall presentation clarity"
                }
            }
        }

class FeedbackTemplate(BaseSchema):
    """Schema for feedback templates"""
    
    id: UUID4 = Field(
        ...,
        description="Identifiant unique"
    )
    ritual_type: RitualType = Field(
        ...,
        description="Type de rituel"
    )
    version: int = Field(
        ..., 
        ge=1,
        description="Version du template"
    )
    title: I18nSchema = Field(
        ...,
        description="Titre du template"
    )
    description: Optional[I18nSchema] = Field(
        None,
        description="Description du template"
    )
    fields: Dict[str, FeedbackTemplateField] = Field(
        ...,
        description="Champs du template"
    )
    total_points: int = Field(
        ...,
        ge=0,
        description="Total des points possibles"
    )
    is_active: bool = Field(
        True,
        description="Template actif"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "ritual_type": "SOD",
                "version": 1,
                "title": {
                    "fr": "Grille d'évaluation SOD",
                    "en": "SOD Evaluation Grid"
                },
                "description": {
                    "fr": "Évaluation des présentations SOD",
                    "en": "SOD presentations evaluation"
                },
                "total_points": 100,
                "is_active": True,
                "fields": {
                    "clarity": {
                        "type": "rating",
                        "label": {
                            "fr": "Clarté",
                            "en": "Clarity"
                        },
                        "required": True,
                        "max_points": 10
                    }
                }
            }
        }

class FeedbackCreate(BaseSchema):
    """Schema for feedback creation ✨"""
    
    ritual_type: RitualType = Field(
        ...,
        description="Type de rituel"
    )
    template_id: UUID4 = Field(
        ...,
        description="ID du template"
    )
    evaluator_id: UUID4 = Field(
        ...,
        description="ID de l'évaluateur"
    )
    evaluated_id: UUID4 = Field(
        ...,
        description="ID de l'évalué"
    )
    feedback_data: Dict[str, Any] = Field(
        ...,
        description="Données du feedback"
    )
    session_date: date = Field(
        ...,
        description="Date de la session"
    )
    presentation_url: Optional[AnyUrl] = Field(
        None,
        description="URL de la présentation"
    )

    @validator('feedback_data')
    def validate_feedback_data(cls, v: Dict[str, Any], values: Dict[str, Any]) -> Dict[str, Any]:
        """Validate feedback data structure"""
        if not isinstance(v, dict):
            raise ValueError("Les données doivent être un dictionnaire")
        return v

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "template_id": "123e4567-e89b-12d3-a456-426614174000",
                "evaluator_id": "123e4567-e89b-12d3-a456-426614174001",
                "evaluated_id": "123e4567-e89b-12d3-a456-426614174002",
                "session_date": "2024-02-15",
                "presentation_url": "https://slides.holberton.fr/sod/123",
                "feedback_data": {
                    "clarity": 9,
                    "technical_mastery": 8,
                    "strengths": ["Excellente structure", "Exemples pertinents"],
                    "improvements": ["Gestion du temps"],
                    "general_comment": "Très bonne présentation !"
                }
            }
        }

class FeedbackResponse(ResponseSchema):
    """Schema for feedback responses"""
    
    ritual_type: RitualType = Field(
        ...,
        description="Type de rituel"
    )
    template: FeedbackTemplate = Field(
        ...,
        description="Template utilisé"
    )
    evaluator: Dict = Field(  # UserResponse
        ...,
        description="Informations sur l'évaluateur"
    )
    evaluated: Dict = Field(  # UserResponse
        ...,
        description="Informations sur l'évalué"
    )
    feedback_data: Dict[str, Any] = Field(
        ...,
        description="Données du feedback"
    )
    session_date: date = Field(
        ...,
        description="Date de la session"
    )
    presentation_url: Optional[AnyUrl] = Field(
        None,
        description="URL de la présentation"
    )
    total_score: float = Field(
        ...,
        ge=0,
        le=100,
        description="Score total"
    )
    status_emoji: str = Field(
        "✨",
        description="Emoji représentant le statut"
    )
    
    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "ritual_type": "SOD",
                "template": {
                    "id": "123e4567-e89b-12d3-a456-426614174001",
                    "version": 1,
                    "title": {
                        "fr": "Grille d'évaluation SOD",
                        "en": "SOD Evaluation Grid"
                    }
                },
                "evaluator": {
                    "id": "123e4567-e89b-12d3-a456-426614174002",
                    "full_name": "Grace Hopper"
                },
                "evaluated": {
                    "id": "123e4567-e89b-12d3-a456-426614174003",
                    "full_name": "Ada Lovelace"
                },
                "feedback_data": {
                    "clarity": 9,
                    "technical_mastery": 8,
                    "strengths": ["Excellente structure"],
                    "improvements": ["Gestion du temps"]
                },
                "session_date": "2024-02-15",
                "presentation_url": "https://slides.holberton.fr/sod/123",
                "total_score": 85.0,
                "status_emoji": "✨",
                "created_at": "2024-02-15T14:30:00Z",
                "updated_at": "2024-02-15T14:30:00Z"
            }
        }

class FeedbackSummary(BaseSchema):
    """Schema for feedback summaries"""
    
    total_feedbacks: int = Field(
        ...,
        ge=0,
        description="Nombre total de feedbacks"
    )
    average_score: float = Field(
        ...,
        ge=0,
        le=100,
        description="Score moyen"
    )
    strength_areas: List[I18nSchema] = Field(
        ...,
        description="Points forts identifiés"
    )
    improvement_areas: List[I18nSchema] = Field(
        ...,
        description="Axes d'amélioration"
    )
    progress_trend: str = Field(
        ...,
        regex="^(improving|stable|declining)$",
        description="Tendance de progression"
    )
    trend_emoji: str = Field(
        ...,
        description="Emoji représentant la tendance"
    )
    recent_feedbacks: List[FeedbackResponse] = Field(
        default_factory=list,
        max_items=5,
        description="5 derniers feedbacks"
    )

    @validator('trend_emoji')
    def set_trend_emoji(cls, v: str, values: Dict[str, Any]) -> str:
        """Set emoji based on trend"""
        trend_emojis = {
            "improving": "📈",
            "stable": "➡️",
            "declining": "📉"
        }
        return trend_emojis.get(values.get('progress_trend', 'stable'), "➡️")

    class Config:
        json_schema_extra = {
            "example": {
                "total_feedbacks": 10,
                "average_score": 87.5,
                "strength_areas": [
                    {
                        "fr": "Communication claire",
                        "en": "Clear communication"
                    },
                    {
                        "fr": "Maîtrise technique",
                        "en": "Technical mastery"
                    }
                ],
                "improvement_areas": [
                    {
                        "fr": "Gestion du temps",
                        "en": "Time management"
                    }
                ],
                "progress_trend": "improving",
                "trend_emoji": "📈",
                "recent_feedbacks": []
            }
        }