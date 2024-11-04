# app/models/schemas/enums/notification.py
from typing import Dict, List, Optional
from pydantic import Field, confloat
from ..common.base import BaseSchema, I18nSchema

class NotificationTypeSchema(BaseSchema):
    """Schema for notification type enumeration ✨"""
    
    value: str = Field(
        ...,
        description="Identifiant unique du type de notification"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    priority: int = Field(
        ...,
        ge=0,
        description="Priorité de la notification"
    )
    requires_immediate_attention: bool = Field(
        False,
        description="Exige une attention immédiate"
    )
    channels: List[str] = Field(
        default_factory=list,
        description="Canaux de notification autorisés"
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
                "value": "new_sod",
                "display_names": {
                    "fr": "Nouveau SOD",
                    "en": "New SOD"
                },
                "descriptions": {
                    "fr": "Notification pour un nouveau SOD assigné",
                    "en": "Notification for a new assigned SOD"
                },
                "priority": 2,
                "requires_immediate_attention": False,
                "channels": ["in_app", "email", "slack"],
                "emoji": "🔔",
                "display_order": 1
            }
        }

class NotificationPreferenceSchema(BaseSchema):
    """Schema for notification preferences"""
    
    notification_type: NotificationTypeSchema = Field(
        ...,
        description="Type de notification"
    )
    email_enabled: bool = Field(
        True,
        description="Notifications par email activées"
    )
    in_app_enabled: bool = Field(
        True,
        description="Notifications dans l'application activées"
    )
    slack_enabled: bool = Field(
        True,
        description="Notifications sur Slack activées"
    )
    quiet_hours: Dict[str, str] = Field(
        default_factory=dict,
        description="Heures calmes (format: {'start': 'HH:MM', 'end': 'HH:MM', 'timezone': 'Europe/Paris'})"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "notification_type": {
                    # Voir exemple NotificationTypeSchema
                },
                "email_enabled": True,
                "in_app_enabled": True,
                "slack_enabled": True,
                "quiet_hours": {
                    "start": "08:00",
                    "end": "18:00",
                    "timezone": "Europe/Paris"
                }
            }
        }

class NotificationChannelSchema(BaseSchema):
    """Schema for notification channels"""
    
    channel: str = Field(
        ...,
        description="Nom du canal de notification"
    )
    enabled: bool = Field(
        True,
        description="Canal activé"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "channel": "email",
                "enabled": True
            }
        }