# app/models/schemas/enums/status.py
from typing import Dict, List, Optional
from pydantic import Field
from ..common.base import BaseSchema, I18nSchema

class UnavailabilityStatusSchema(BaseSchema):
    """Schema for unavailability status enumeration ✨"""
    
    value: str = Field(
        ...,
        pattern="^(PENDING|VALIDATED|REJECTED|CANCELLED)$",
        description="Identifiant unique du statut"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    is_final: bool = Field(
        ...,
        description="Statut final"
    )
    allowed_transitions: List[str] = Field(
        ...,
        description="Transitions autorisées"
    )
    requires_reason: bool = Field(
        False,
        description="Nécessite une justification"
    )
    notifies_user: bool = Field(
        True,
        description="Notifie l'utilisateur"
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
                "value": "PENDING",
                "display_names": {
                    "fr": "En attente",
                    "en": "Pending"
                },
                "descriptions": {
                    "fr": "Demande en attente de validation",
                    "en": "Request pending validation"
                },
                "is_final": False,
                "allowed_transitions": ["VALIDATED", "REJECTED"],
                "requires_reason": False,
                "notifies_user": True,
                "emoji": "⏳",
                "display_order": 1
            }
        }

class StatusTransitionSchema(BaseSchema):
    """Schema for status transitions"""
    
    from_status: str = Field(
        ...,
        description="Statut de départ"
    )
    to_status: str = Field(
        ...,
        description="Statut d'arrivée"
    )
    allowed_roles: List[str] = Field(
        ...,
        description="Rôles autorisés"
    )
    requires_comment: bool = Field(
        False,
        description="Nécessite un commentaire"
    )
    notification_template: I18nSchema = Field(
        ...,
        description="Template de notification"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "from_status": "PENDING",
                "to_status": "VALIDATED",
                "allowed_roles": ["STAFF", "ADMIN"],
                "requires_comment": False,
                "notification_template": {
                    "fr": "Votre demande a été validée",
                    "en": "Your request has been validated"
                }
            }
        }

class StatusActionSchema(BaseSchema):
    """Schema for status-related actions"""
    
    status: str = Field(
        ...,
        description="Statut"
    )
    action: str = Field(
        ...,
        description="Action à effectuer"
    )
    comment: str = Field(
        None,
        description="Commentaire optionnel"
    )
    notify_channels: List[str] = Field(
        default=["in_app"],
        description="Canaux de notification"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "status": "PENDING",
                "action": "validate",
                "comment": "Absence justifiée",
                "notify_channels": ["in_app", "email"]
            }
        }

class StatusSummarySchema(BaseSchema):
    """Schema for status summaries"""
    
    status_counts: Dict[str, int] = Field(
        ...,
        description="Nombre d'éléments par statut"
    )
    pending_actions: int = Field(
        0,
        description="Actions en attente"
    )
    average_resolution_time: Optional[float] = Field(
        None,
        description="Temps moyen de résolution (heures)"
    )
    status_distribution: Dict[str, float] = Field(
        ...,
        description="Distribution des statuts (%)"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "status_counts": {
                    "PENDING": 5,
                    "VALIDATED": 20,
                    "REJECTED": 2
                },
                "pending_actions": 5,
                "average_resolution_time": 24.5,
                "status_distribution": {
                    "PENDING": 18.5,
                    "VALIDATED": 74.1,
                    "REJECTED": 7.4
                }
            }
        }