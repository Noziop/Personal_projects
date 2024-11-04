# app/models/domain/enums/notification_type.py
from enum import Enum
from typing import Dict, Optional, List
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class NotificationType(str, Enum):
    """Types of notifications in the system ✨"""
    
    UNAVAILABILITY_REQUEST = "unavailability_request"
    SOD_FEEDBACK = "sod_feedback"
    STANDUP_FEEDBACK = "standup_feedback"
    RITUAL_REMINDER = "ritual_reminder"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.UNAVAILABILITY_REQUEST: {
                "fr": "Demande d'indisponibilité",
                "en": "Unavailability Request"
            },
            self.SOD_FEEDBACK: {
                "fr": "Feedback SOD",
                "en": "SOD Feedback"
            },
            self.STANDUP_FEEDBACK: {
                "fr": "Feedback Stand-up",
                "en": "Stand-up Feedback"
            },
            self.RITUAL_REMINDER: {
                "fr": "Rappel Rituel",
                "en": "Ritual Reminder"
            }
        }[self]

    @property
    def descriptions(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.UNAVAILABILITY_REQUEST: {
                "fr": "Nouvelle demande à traiter",
                "en": "New request to process"
            },
            self.SOD_FEEDBACK: {
                "fr": "Feedback à donner sur un SOD",
                "en": "SOD feedback to provide"
            },
            self.STANDUP_FEEDBACK: {
                "fr": "Rapport de stand-up à remplir",
                "en": "Stand-up report to fill"
            },
            self.RITUAL_REMINDER: {
                "fr": "Rappel de participation à un rituel",
                "en": "Ritual participation reminder"
            }
        }[self]

    @property
    def priority(self) -> int:
        """Get notification priority (1 highest, 3 lowest)"""
        return {
            self.UNAVAILABILITY_REQUEST: 1,
            self.SOD_FEEDBACK: 2,
            self.STANDUP_FEEDBACK: 2,
            self.RITUAL_REMINDER: 3
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.UNAVAILABILITY_REQUEST: 1,
            self.SOD_FEEDBACK: 2,
            self.STANDUP_FEEDBACK: 3,
            self.RITUAL_REMINDER: 4
        }[self]

    @property
    def requires_immediate_attention(self) -> bool:
        """Check if notification requires immediate attention"""
        return self.priority == 1

    @property
    def default_channels(self) -> List[str]:
        """Get default notification channels"""
        return {
            self.UNAVAILABILITY_REQUEST: ["email", "slack", "in_app"],
            self.SOD_FEEDBACK: ["slack", "in_app"],
            self.STANDUP_FEEDBACK: ["slack", "in_app"],
            self.RITUAL_REMINDER: ["in_app"]
        }[self]

    @property
    def expiration_hours(self) -> Optional[int]:
        """Get notification expiration in hours"""
        return {
            self.UNAVAILABILITY_REQUEST: 48,  # 2 jours
            self.SOD_FEEDBACK: 24,           # 1 jour
            self.STANDUP_FEEDBACK: 24,       # 1 jour
            self.RITUAL_REMINDER: 4          # 4 heures
        }[self]

    @property
    def notification_templates(self) -> Dict[str, Dict[str, Dict[str, str]]]:
        """Get all notification templates"""
        return {
            self.UNAVAILABILITY_REQUEST: {
                "fr": {
                    "title": "Nouvelle demande d'indisponibilité",
                    "template": "notification_templates/unavailability_fr.html",
                    "subject": "Demande d'indisponibilité à traiter"
                },
                "en": {
                    "title": "New Unavailability Request",
                    "template": "notification_templates/unavailability_en.html",
                    "subject": "Unavailability Request to Process"
                }
            },
            self.SOD_FEEDBACK: {
                "fr": {
                    "title": "Feedback SOD en attente",
                    "template": "notification_templates/sod_feedback_fr.html",
                    "subject": "Feedback SOD à compléter"
                },
                "en": {
                    "title": "Pending SOD Feedback",
                    "template": "notification_templates/sod_feedback_en.html",
                    "subject": "SOD Feedback to Complete"
                }
            },
            self.STANDUP_FEEDBACK: {
                "fr": {
                    "title": "Rapport Stand-up en attente",
                    "template": "notification_templates/standup_feedback_fr.html",
                    "subject": "Rapport Stand-up à compléter"
                },
                "en": {
                    "title": "Pending Stand-up Report",
                    "template": "notification_templates/standup_feedback_en.html",
                    "subject": "Stand-up Report to Complete"
                }
            },
            self.RITUAL_REMINDER: {
                "fr": {
                    "title": "Rappel de rituel",
                    "template": "notification_templates/ritual_reminder_fr.html",
                    "subject": "Rappel : Rituel à venir"
                },
                "en": {
                    "title": "Ritual Reminder",
                    "template": "notification_templates/ritual_reminder_en.html",
                    "subject": "Reminder: Upcoming Ritual"
                }
            }
        }

    @classmethod
    def from_string(cls, value: str) -> Optional["NotificationType"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en NotificationType")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Type de notification invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_NOTIFICATION_TYPE,
                notification_type=value
            )

    def get_template_data(self, lang: str = "fr") -> Dict[str, str]:
        """Get notification template data"""
        logger.debug(f"📝 Récupération template pour {self.value} (lang: {lang})")
        template_data = self.notification_templates.get(self, {}).get(lang, {})
        
        if not template_data:
            logger.warning(f"⚠️ Template non trouvé pour {self.value} en {lang}")
            template_data = self.notification_templates.get(self, {}).get("fr", {})
        
        return template_data

    def __str__(self) -> str:
        """String representation"""
        return f"{self.display_names['fr']} (Priorité: {self.priority})"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "description": self.descriptions[lang],
            "priority": self.priority,
            "display_order": self.display_order,
            "requires_immediate_attention": self.requires_immediate_attention,
            "default_channels": self.default_channels,
            "expiration_hours": self.expiration_hours,
            "template": self.get_template_data(lang)
        }