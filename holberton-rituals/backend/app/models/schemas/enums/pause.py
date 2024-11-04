# app/models/schemas/enums/pause.py
from typing import Dict, List, Optional
from pydantic import Field, confloat
from ..common.base import BaseSchema, I18nSchema

class PauseTypeSchema(BaseSchema):
    """Schema for pause type enumeration ✨"""
    
    value: str = Field(
        ...,
        description="Identifiant unique du type de pause"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    scope: str = Field(
        ...,
        pattern="^(individual|group|cohort)$",
        description="Portée de la pause"
    )
    min_duration_days: int = Field(
        1,
        ge=1,
        description="Durée minimum en jours"
    )
    max_duration_days: Optional[int] = Field(
        None,
        description="Durée maximum en jours"
    )
    requires_validation: bool = Field(
        True,
        description="Nécessite une validation"
    )
    validator_roles: List[str] = Field(
        ...,
        description="Rôles autorisés à valider"
    )
    affects_rituals: List[str] = Field(
        ...,
        description="Rituels affectés"
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
                "value": "vacation",
                "display_names": {
                    "fr": "Vacances",
                    "en": "Vacation"
                },
                "descriptions": {
                    "fr": "Période de congés",
                    "en": "Vacation period"
                },
                "scope": "individual",
                "min_duration_days": 1,
                "max_duration_days": 30,
                "requires_validation": True,
                "validator_roles": ["STAFF", "ADMIN"],
                "affects_rituals": ["SOD", "STANDUP"],
                "emoji": "🌴",
                "display_order": 1
            }
        }

class PauseRequestSchema(BaseSchema):
    """Schema for pause requests"""
    
    pause_type: str = Field(
        ...,
        description="Type de pause"
    )
    start_date: str = Field(
        ...,
        description="Date de début (YYYY-MM-DD)"
    )
    end_date: str = Field(
        ...,
        description="Date de fin (YYYY-MM-DD)"
    )
    reason: Optional[I18nSchema] = Field(
        None,
        description="Raison de la pause"
    )
    affected_students: Optional[List[str]] = Field(
        None,
        description="IDs des étudiants affectés (pour group/cohort)"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "pause_type": "vacation",
                "start_date": "2024-07-15",
                "end_date": "2024-07-30",
                "reason": {
                    "fr": "Vacances d'été",
                    "en": "Summer vacation"
                },
                "affected_students": None
            }
        }

class PauseStatusSchema(BaseSchema):
    """Schema for pause status"""
    
    is_active: bool = Field(
        ...,
        description="Pause en cours"
    )
    remaining_days: Optional[int] = Field(
        None,
        description="Jours restants"
    )
    missed_rituals: Dict[str, int] = Field(
        default_factory=dict,
        description="Nombre de rituels manqués par type"
    )
    return_date: Optional[str] = Field(
        None,
        description="Date de retour prévue"
    )
    status_emoji: str = Field(
        ...,
        description="Emoji de statut"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "is_active": True,
                "remaining_days": 5,
                "missed_rituals": {
                    "SOD": 2,
                    "STANDUP": 3
                },
                "return_date": "2024-07-30",
                "status_emoji": "🌴"
            }
        }

class PauseSummarySchema(BaseSchema):
    """Schema for pause summaries"""
    
    total_pauses: int = Field(
        0,
        description="Nombre total de pauses"
    )
    total_days: int = Field(
        0,
        description="Nombre total de jours"
    )
    by_type: Dict[str, int] = Field(
        default_factory=dict,
        description="Répartition par type"
    )
    current_pauses: List[PauseStatusSchema] = Field(
        default_factory=list,
        description="Pauses en cours"
    )
    upcoming_pauses: List[PauseRequestSchema] = Field(
        default_factory=list,
        description="Pauses à venir"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "total_pauses": 3,
                "total_days": 45,
                "by_type": {
                    "vacation": 2,
                    "sick_leave": 1
                },
                "current_pauses": [],
                "upcoming_pauses": []
            }
        }