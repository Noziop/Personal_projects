# app/models/domain/enums/unavailability_status.py
from enum import Enum
from typing import Dict, Optional
from datetime import datetime
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class UnavailabilityStatus(str, Enum):
    """Status for student unavailability requests ✨"""
    
    PENDING = "pending"
    VALIDATED = "validated"
    REJECTED = "rejected"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.PENDING: {
                "fr": "En attente",
                "en": "Pending"
            },
            self.VALIDATED: {
                "fr": "Validée",
                "en": "Approved"
            },
            self.REJECTED: {
                "fr": "Rejetée",
                "en": "Rejected"
            }
        }[self]

    @property
    def descriptions(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.PENDING: {
                "fr": "Demande soumise, en attente de validation",
                "en": "Request submitted, waiting for approval"
            },
            self.VALIDATED: {
                "fr": "Demande acceptée par le staff",
                "en": "Request approved by staff"
            },
            self.REJECTED: {
                "fr": "Demande refusée par le staff",
                "en": "Request rejected by staff"
            }
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.PENDING: 1,
            self.VALIDATED: 2,
            self.REJECTED: 3
        }[self]

    @property
    def emoji(self) -> str:
        """Get emoji representation"""
        return {
            self.PENDING: "⏳",    # Waiting
            self.VALIDATED: "✅",  # Approved
            self.REJECTED: "❌"    # Rejected
        }[self]

    @property
    def requires_action(self) -> bool:
        """Check if status requires staff action"""
        return self == self.PENDING

    @property
    def notification_priority(self) -> int:
        """Get notification priority for this status"""
        return {
            self.PENDING: 1,    # High priority - needs action
            self.VALIDATED: 2,  # Medium priority - info
            self.REJECTED: 2    # Medium priority - info
        }[self]

    @property
    def can_transition_to(self) -> list["UnavailabilityStatus"]:
        """Get possible next statuses"""
        return {
            self.PENDING: [self.VALIDATED, self.REJECTED],
            self.VALIDATED: [],  # Terminal state
            self.REJECTED: []    # Terminal state
        }[self]

    @classmethod
    def from_string(cls, value: str) -> Optional["UnavailabilityStatus"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en UnavailabilityStatus")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Statut d'indisponibilité invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_UNAVAILABILITY_STATUS,
                status=value
            )

    def validate_transition(self, to_status: "UnavailabilityStatus") -> bool:
        """Validate status transition"""
        logger.debug(f"🔍 Validation transition: {self.value} -> {to_status.value}")
        
        if to_status not in self.can_transition_to:
            logger.error(
                f"❌ Transition invalide: {self.value} -> {to_status.value}"
            )
            raise ValidationException(
                error_code=ErrorCode.INVALID_STATUS_TRANSITION,
                from_status=self.display_names["fr"],
                to_status=to_status.display_names["fr"]
            )
        
        logger.info(f"✨ Transition validée: {self.value} -> {to_status.value}")
        return True

    def get_notification_message(self, lang: str = "fr", **kwargs) -> str:
        """Get notification message for this status"""
        logger.debug(f"📝 Génération message pour {self.value} (lang: {lang})")
        templates = {
            self.PENDING: {
                "fr": "Nouvelle demande d'indisponibilité de {student_name} du {start_date} au {end_date}",
                "en": "New unavailability request from {student_name} from {start_date} to {end_date}"
            },
            self.VALIDATED: {
                "fr": "Votre demande d'indisponibilité a été approuvée ✨",
                "en": "Your unavailability request has been approved ✨"
            },
            self.REJECTED: {
                "fr": "Votre demande d'indisponibilité a été refusée",
                "en": "Your unavailability request has been rejected"
            }
        }
        message = templates[self][lang].format(**kwargs)
        logger.debug(f"📨 Message généré: {message}")
        return message

    def __str__(self) -> str:
        """String representation"""
        return f"{self.emoji} {self.display_names['fr']}"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "description": self.descriptions[lang],
            "display_order": self.display_order,
            "emoji": self.emoji,
            "requires_action": self.requires_action,
            "notification_priority": self.notification_priority,
            "possible_transitions": [s.value for s in self.can_transition_to]
        }