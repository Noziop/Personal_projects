# app/models/domain/enums/ritual_type.py
from enum import Enum
from typing import Dict, Optional, List
from datetime import time
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class RitualType(str, Enum):
    """Types of rituals in the system ✨"""
    
    SOD = "sod"
    STANDUP = "standup"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.SOD: {
                "fr": "SOD",
                "en": "SOD"
            },
            self.STANDUP: {
                "fr": "Stand-up",
                "en": "Stand-up"
            }
        }[self]

    @property
    def descriptions(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.SOD: {
                "fr": "Speaker Of the Day (aka Share Or Die) - Présentation technique",
                "en": "Speaker Of the Day (aka Share Or Die) - Technical presentation"
            },
            self.STANDUP: {
                "fr": "Point quotidien de la cohorte",
                "en": "Daily cohort meeting"
            }
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.SOD: 1,
            self.STANDUP: 2
        }[self]

    @property
    def duration_minutes(self) -> int:
        """Get typical duration in minutes"""
        return {
            self.SOD: 15,  # 10-15 min presentation + Q&A
            self.STANDUP: 20  # Daily standup duration
        }[self]

    @property
    def time_slots(self) -> Dict[str, time]:
        """Get possible time slots for the ritual"""
        return {
            self.SOD: {
                "morning": time(11, 30)
            },
            self.STANDUP: {
                "default": time(11, 45)
            }
        }[self]

    @property
    def requires_feedback(self) -> bool:
        """Check if ritual requires feedback"""
        return {
            self.SOD: True,  # SOD needs detailed feedback
            self.STANDUP: False  # Standup has reports, not feedback
        }[self]

    @property
    def emoji(self) -> str:
        """Get emoji representation"""
        return {
            self.SOD: "🎯",  # Target/presentation
            self.STANDUP: "👥"  # Group/meeting
        }[self]

    @property
    def required_roles(self) -> Dict[str, List[str]]:
        """Get required roles for the ritual"""
        return {
            self.SOD: {
                "presenter": ["STUDENT"],
                "evaluator": ["STUDENT", "STAFF", "ADMIN"]
            },
            self.STANDUP: {
                "scrum_master": ["STUDENT"],
                "participants": ["STUDENT", "STAFF", "ADMIN"]
            }
        }[self]

    @classmethod
    def from_string(cls, value: str) -> Optional["RitualType"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en RitualType")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Type de rituel invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_RITUAL_TYPE,
                ritual_type=value
            )

    def validate_duration(self, minutes: int) -> bool:
        """Validate ritual duration"""
        logger.debug(f"🔍 Validation durée pour {self.value}: {minutes} minutes")
        
        if not isinstance(minutes, int):
            logger.error(f"❌ Type invalide pour minutes: {type(minutes)}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_DATA_TYPE,
                field="minutes",
                expected="integer"
            )

        if minutes > self.duration_minutes:
            logger.warning(
                f"⚠️ Durée excessive pour {self.value}: {minutes} minutes "
                f"(max: {self.duration_minutes})"
            )
            raise ValidationException(
                error_code=ErrorCode.EXCEEDED_RITUAL_DURATION,
                ritual_type=self.display_names["fr"],
                max_duration=self.duration_minutes,
                given_duration=minutes
            )
        
        return True

    def get_preparation_tips(self, lang: str = "fr") -> List[str]:
        """Get preparation tips for the ritual"""
        logger.debug(f"📝 Récupération conseils pour {self.value} (lang: {lang})")
        tips = {
            self.SOD: {
                "fr": [
                    "Préparer ses slides à l'avance",
                    "Chronométrer sa présentation",
                    "Préparer des exemples concrets",
                    "Anticiper les questions"
                ],
                "en": [
                    "Prepare slides in advance",
                    "Time your presentation",
                    "Prepare concrete examples",
                    "Anticipate questions"
                ]
            },
            self.STANDUP: {
                "fr": [
                    "Lister ses réalisations",
                    "Noter ses blocages",
                    "Préparer ses questions",
                    "Être ponctuel"
                ],
                "en": [
                    "List your achievements",
                    "Note your blockers",
                    "Prepare your questions",
                    "Be on time"
                ]
            }
        }
        return tips[self][lang]

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
            "duration_minutes": self.duration_minutes,
            "requires_feedback": self.requires_feedback,
            "emoji": self.emoji,
            "time_slots": {k: v.strftime("%H:%M") for k, v in self.time_slots.items()},
            "required_roles": self.required_roles,
            "preparation_tips": self.get_preparation_tips(lang)
        }