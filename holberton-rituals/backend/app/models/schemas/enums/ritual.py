# app/models/schemas/enums/ritual.py
from typing import Dict, List, Optional
from pydantic import Field
from ..common.base import BaseSchema, I18nSchema

class RitualTypeSchema(BaseSchema):
    """Schema for ritual type enumeration ✨"""
    
    value: str = Field(
        ...,
        pattern="^(SOD|STANDUP)$",
        description="Identifiant unique du type de rituel"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    duration_minutes: int = Field(
        ...,
        ge=5,
        le=60,
        description="Durée en minutes"
    )
    default_time: str = Field(
        ...,
        pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$",
        description="Heure par défaut (HH:MM)"
    )
    frequency_per_week: int = Field(
        ...,
        ge=1,
        le=5,
        description="Fréquence hebdomadaire"
    )
    allowed_days: List[str] = Field(
        ...,
        description="Jours autorisés"
    )
    requires_preparation: bool = Field(
        False,
        description="Nécessite une préparation"
    )
    preparation_time_minutes: Optional[int] = Field(
        None,
        ge=0,
        description="Temps de préparation en minutes"
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
                "value": "SOD",
                "display_names": {
                    "fr": "Standup of Death",
                    "en": "Standup of Death"
                },
                "descriptions": {
                    "fr": "Présentation technique approfondie",
                    "en": "In-depth technical presentation"
                },
                "duration_minutes": 15,
                "default_time": "11:30",
                "frequency_per_week": 3,
                "allowed_days": ["tuesday", "wednesday", "thursday"],
                "requires_preparation": True,
                "preparation_time_minutes": 120,
                "emoji": "💀",
                "display_order": 1
            }
        }

class RitualScheduleSchema(BaseSchema):
    """Schema for ritual scheduling rules"""
    
    ritual_type: str = Field(
        ...,
        description="Type de rituel"
    )
    time_slots: List[str] = Field(
        ...,
        description="Créneaux horaires disponibles"
    )
    buffer_minutes: int = Field(
        15,
        ge=0,
        description="Temps tampon entre rituels"
    )
    max_per_day: int = Field(
        ...,
        ge=1,
        description="Maximum par jour"
    )
    concurrent_allowed: bool = Field(
        False,
        description="Rituels simultanés autorisés"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "time_slots": ["11:30", "14:00", "15:30"],
                "buffer_minutes": 15,
                "max_per_day": 2,
                "concurrent_allowed": False
            }
        }

class RitualReminderSchema(BaseSchema):
    """Schema for ritual reminders"""
    
    ritual_type: str = Field(
        ...,
        description="Type de rituel"
    )
    reminder_times: List[int] = Field(
        ...,
        description="Minutes avant le rituel"
    )
    message_templates: Dict[str, I18nSchema] = Field(
        ...,
        description="Templates de messages par type de rappel"
    )
    channels: List[str] = Field(
        ...,
        description="Canaux de notification"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "reminder_times": [1440, 60, 15],  # 24h, 1h, 15min
                "message_templates": {
                    "24h": {
                        "fr": "Tu présentes un SOD demain à {time}",
                        "en": "You have a SOD presentation tomorrow at {time}"
                    },
                    "1h": {
                        "fr": "SOD dans 1 heure !",
                        "en": "SOD in 1 hour!"
                    }
                },
                "channels": ["slack", "email", "in_app"]
            }
        }

class RitualMetricsSchema(BaseSchema):
    """Schema for ritual metrics configuration"""
    
    ritual_type: str = Field(
        ...,
        description="Type de rituel"
    )
    tracked_metrics: List[str] = Field(
        ...,
        description="Métriques suivies"
    )
    success_criteria: Dict[str, float] = Field(
        ...,
        description="Critères de succès par métrique"
    )
    improvement_suggestions: Dict[str, I18nSchema] = Field(
        ...,
        description="Suggestions d'amélioration par métrique"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "tracked_metrics": ["participation", "timing", "feedback"],
                "success_criteria": {
                    "participation": 0.8,
                    "timing": 0.9,
                    "feedback": 0.85
                },
                "improvement_suggestions": {
                    "timing": {
                        "fr": "Essaie de chronométrer tes répétitions",
                        "en": "Try timing your rehearsals"
                    }
                }
            }
        }