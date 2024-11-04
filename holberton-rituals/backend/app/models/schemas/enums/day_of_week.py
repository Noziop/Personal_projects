# app/models/schemas/enums/day_of_week.py
from typing import Dict, List
from pydantic import Field
from ..common.base import BaseSchema, I18nSchema

class DayOfWeekSchema(BaseSchema):
    """Schema for day of week enumeration ✨"""
    
    value: str = Field(
        ...,
        description="Identifiant unique du jour",
        pattern="^(monday|tuesday|wednesday|thursday|friday|saturday|sunday)$"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    short_names: I18nSchema = Field(
        ...,
        description="Noms courts"
    )
    is_working_day: bool = Field(
        True,
        description="Jour ouvré"
    )
    ritual_types: List[str] = Field(
        default_factory=list,
        description="Types de rituels possibles ce jour"
    )
    emoji: str = Field(
        ...,
        description="Emoji représentatif"
    )
    display_order: int = Field(
        ...,
        ge=1,
        le=7,
        description="Ordre d'affichage (1-7)"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "value": "monday",
                "display_names": {
                    "fr": "Lundi",
                    "en": "Monday"
                },
                "short_names": {
                    "fr": "Lun",
                    "en": "Mon"
                },
                "is_working_day": True,
                "ritual_types": ["standup"],
                "emoji": "1️⃣",
                "display_order": 1
            }
        }

class DayOfWeekResponse(DayOfWeekSchema):
    """Schema for day of week responses with scheduling info"""
    
    scheduled_rituals: List[Dict] = Field(
        default_factory=list,
        description="Rituels programmés ce jour"
    )
    available_slots: List[str] = Field(
        default_factory=list,
        description="Créneaux horaires disponibles"
    )
    workload_status: str = Field(
        "available",
        description="Statut de charge",
        pattern="^(available|moderate|full)$"
    )
    workload_emoji: str = Field(
        "✅",
        description="Emoji de charge"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "value": "monday",
                "display_names": {
                    "fr": "Lundi",
                    "en": "Monday"
                },
                "short_names": {
                    "fr": "Lun",
                    "en": "Mon"
                },
                "is_working_day": True,
                "ritual_types": ["standup"],
                "emoji": "1️⃣",
                "display_order": 1,
                "scheduled_rituals": [
                    {
                        "type": "standup",
                        "time": "11:45:00",
                        "cohort": "C#22"
                    }
                ],
                "available_slots": [
                    "09:00",
                    "10:00",
                    "14:00",
                    "15:00"
                ],
                "workload_status": "moderate",
                "workload_emoji": "📊"
            }
        }

class WeekSchedule(BaseSchema):
    """Schema for weekly schedule overview"""
    
    days: Dict[str, DayOfWeekResponse] = Field(
        ...,
        description="Planning de la semaine"
    )
    total_rituals: int = Field(
        0,
        description="Nombre total de rituels"
    )
    sod_count: int = Field(
        0,
        description="Nombre de SODs"
    )
    standup_count: int = Field(
        0,
        description="Nombre de Standups"
    )
    busiest_day: str = Field(
        None,
        description="Jour le plus chargé"
    )
    status_emoji: str = Field(
        "📅",
        description="Emoji de statut"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "days": {
                    "monday": {
                        "value": "monday",
                        "display_names": {
                            "fr": "Lundi",
                            "en": "Monday"
                        },
                        "scheduled_rituals": [
                            {
                                "type": "standup",
                                "time": "11:45:00",
                                "cohort": "C#22"
                            }
                        ]
                    }
                    # ... autres jours
                },
                "total_rituals": 15,
                "sod_count": 9,
                "standup_count": 6,
                "busiest_day": "tuesday",
                "status_emoji": "📅"
            }
        }