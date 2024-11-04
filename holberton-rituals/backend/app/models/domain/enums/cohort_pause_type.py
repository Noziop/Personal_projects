# app/models/domain/enums/cohort_pause_type.py
from enum import Enum
from typing import Dict, Optional
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class CohortPauseType(str, Enum):
    """Types of cohort pause periods ✨"""
    
    SCHOOL_HOLIDAY = "school_holiday"
    GROUP_PROJECT = "group_project"
    SPECIAL_EVENT = "special_event"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.SCHOOL_HOLIDAY: {
                "fr": "Vacances scolaires",
                "en": "School Holiday"
            },
            self.GROUP_PROJECT: {
                "fr": "Projet de groupe",
                "en": "Group Project"
            },
            self.SPECIAL_EVENT: {
                "fr": "Événement spécial",
                "en": "Special Event"
            }
        }[self]

    @property
    def description(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.SCHOOL_HOLIDAY: {
                "fr": "Période de vacances officielles",
                "en": "Official holiday period"
            },
            self.GROUP_PROJECT: {
                "fr": "Période de projet collaboratif majeur",
                "en": "Major collaborative project period"
            },
            self.SPECIAL_EVENT: {
                "fr": "Hackathon, conférences, etc.",
                "en": "Hackathon, conferences, etc."
            }
        }[self]

    @property
    def scope(self) -> str:
        """Get pause scope (cohort/group/individual)"""
        return {
            self.SCHOOL_HOLIDAY: "cohort",    # Affecte toute la cohorte
            self.GROUP_PROJECT: "group",       # Affecte un groupe d'étudiants
            self.SPECIAL_EVENT: "individual"   # Affecte un étudiant
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.SCHOOL_HOLIDAY: 1,
            self.GROUP_PROJECT: 2,
            self.SPECIAL_EVENT: 3
        }[self]

    @property
    def min_duration_days(self) -> int:
        """Get minimum duration in days"""
        return {
            self.SCHOOL_HOLIDAY: 7,
            self.GROUP_PROJECT: 3,
            self.SPECIAL_EVENT: 1
        }[self]

    @property
    def max_duration_days(self) -> int:
        """Get maximum duration in days"""
        return {
            self.SCHOOL_HOLIDAY: 30,
            self.GROUP_PROJECT: 14,
            self.SPECIAL_EVENT: 7
        }[self]

    @classmethod
    def from_string(cls, value: str) -> Optional["CohortPauseType"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en CohortPauseType")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Type de pause invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_PAUSE_TYPE,
                pause_type=value
            )

    def validate_duration(self, days: int) -> bool:
        """Validate duration for this pause type"""
        logger.debug(f"🔍 Validation durée pour {self.value}: {days} jours")
        
        if not isinstance(days, int):
            logger.error(f"❌ Type invalide pour days: {type(days)}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_DATA_TYPE,
                field="days",
                expected="integer"
            )

        if not (self.min_duration_days <= days <= self.max_duration_days):
            logger.warning(
                f"⚠️ Durée invalide pour {self.value}: {days} jours "
                f"(min: {self.min_duration_days}, max: {self.max_duration_days})"
            )
            raise ValidationException(
                error_code=ErrorCode.INVALID_PAUSE_DURATION,
                pause_type=self.display_names["fr"],
                min_days=self.min_duration_days,
                max_days=self.max_duration_days,
                current_days=days
            )
        
        logger.info(f"✨ Durée validée pour {self.display_names['fr']}: {days} jours")
        return True

    def validate_scope(self, student_count: int) -> bool:
        """Validate number of students for this pause type"""
        logger.debug(f"🔍 Validation scope pour {self.value}: {student_count} étudiants")
        
        if self.scope == "individual" and student_count > 1:
            logger.warning(f"⚠️ Trop d'étudiants pour un événement spécial")
            raise ValidationException(
                error_code=ErrorCode.INVALID_PAUSE_SCOPE,
                pause_type=self.display_names["fr"],
                expected_scope="individual",
                student_count=student_count
            )
        
        return True

    def __str__(self) -> str:
        """String representation"""
        return f"{self.display_names['fr']} ({self.value})"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "description": self.description[lang],
            "display_order": self.display_order,
            "min_duration": self.min_duration_days,
            "max_duration": self.max_duration_days,
            "scope": self.scope
        }