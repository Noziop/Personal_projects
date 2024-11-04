# app/models/schemas/rituals/base.py
from datetime import date, time, datetime
from typing import Optional, Dict, TypeVar, Generic
from pydantic import Field, UUID4
from pydantic.generics import GenericModel
from ..common.base import BaseSchema, ResponseSchema, I18nSchema
from ...domain.enums.ritual_type import RitualType

T = TypeVar('T')  # Pour le type générique

class RitualBase(BaseSchema):
    """Base schema for all rituals ✨"""
    
    ritual_type: RitualType = Field(
        ...,
        description="Type de rituel"
    )
    session_date: date = Field(
        ...,
        description="Date de la session"
    )
    session_time: time = Field(
        ...,
        description="Heure de la session"
    )
    schedule_id: UUID4 = Field(
        ...,
        description="ID du planning associé"
    )

    @property
    def ritual_display_name(self) -> str:
        """Get ritual display name"""
        return self.ritual_type.display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return self.ritual_type.emoji

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "session_date": "2024-02-15",
                "session_time": "11:30:00",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174000"
            }
        }

class RitualAssignmentBase(RitualBase):
    """Base schema for ritual assignments"""
    
    student_id: UUID4 = Field(
        ...,
        description="ID de l'étudiant assigné"
    )
    is_replacement: bool = Field(
        False,
        description="Indique si c'est un remplacement"
    )
    replacement_for_id: Optional[UUID4] = Field(
        None,
        description="ID de l'assignation remplacée"
    )
    replacement_reason: Optional[str] = Field(
        None,
        description="Raison du remplacement"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "session_date": "2024-02-15",
                "session_time": "11:30:00",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174000",
                "student_id": "123e4567-e89b-12d3-a456-426614174001",
                "is_replacement": False,
                "replacement_for_id": None,
                "replacement_reason": None
            }
        }

class RitualResponse(ResponseSchema, RitualBase):
    """Base schema for ritual responses"""
    
    student_name: str = Field(
        ...,
        description="Nom de l'étudiant"
    )
    cohort_name: str = Field(
        ...,
        description="Nom de la cohorte"
    )
    status: str = Field(
        ...,
        description="Statut du rituel"
    )
    status_emoji: str = Field(
        ...,
        description="Emoji du statut"
    )
    duration_minutes: int = Field(
        ...,
        description="Durée prévue en minutes"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "ritual_type": "SOD",
                "session_date": "2024-02-15",
                "session_time": "11:30:00",
                "schedule_id": "123e4567-e89b-12d3-a456-426614174000",
                "student_name": "Ada Lovelace",
                "cohort_name": "C#22",
                "status": "Planifié",
                "status_emoji": "⏳",
                "duration_minutes": 15,
                "created_at": "2024-02-01T12:00:00Z",
                "updated_at": "2024-02-11T14:30:00Z"
            }
        }

class RitualScheduleInfo(BaseSchema):
    """Schema for ritual schedule information"""
    
    ritual_type: RitualType = Field(
        ...,
        description="Type de rituel"
    )
    weekday: str = Field(
        ...,
        description="Jour de la semaine"
    )
    scheduled_time: time = Field(  # On renomme 'time' en 'scheduled_time'
        ...,
        description="Heure prévue"
    )
    duration_minutes: int = Field(
        ...,
        description="Durée en minutes"
    )
    location: Optional[str] = Field(
        None,
        description="Lieu (si applicable)"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_type": "SOD",
                "weekday": "Mardi",
                "scheduled_time": "11:30:00",  # Mise à jour de l'exemple aussi
                "duration_minutes": 15,
                "location": "Salle principale"
            }
        }


class RitualReminder(BaseSchema):
    """Schema for ritual reminders"""
    
    ritual_id: UUID4 = Field(
        ...,
        description="ID du rituel"
    )
    reminder_type: str = Field(
        ...,
        description="Type de rappel"
    )
    message: I18nSchema = Field(
        ...,
        description="Message du rappel"
    )
    send_at: datetime = Field(
        ...,
        description="Date d'envoi prévue"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "ritual_id": "123e4567-e89b-12d3-a456-426614174000",
                "reminder_type": "before_15min",
                "message": {
                    "fr": "Votre SOD commence dans 15 minutes !",
                    "en": "Your SOD starts in 15 minutes!"
                },
                "send_at": "2024-02-15T11:15:00Z"
            }
        }