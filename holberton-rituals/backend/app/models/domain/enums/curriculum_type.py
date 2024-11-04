# app/models/domain/enums/curriculum_type.py
from enum import Enum
from typing import Dict, Optional
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class CurriculumType(str, Enum):
    """Types of curriculum programs ✨"""
    
    FUNDAMENTALS = "fundamentals"
    SPECIALIZATION = "specialization"
    APPRENTICESHIP = "apprenticeship"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.FUNDAMENTALS: {
                "fr": "Formation fondamentale de 9 mois",
                "en": "Fundamentals - 9 months program"
            },
            self.SPECIALIZATION: {
                "fr": "Spécialisation avancée de 9 mois",
                "en": "Advanced specialization - 9 months program"
            },
            self.APPRENTICESHIP: {
                "fr": "Spécialisation alternance avancée de 24 mois",
                "en": "Advanced apprenticeship - 24 months program"
            }
        }[self]

    @property
    def duration_months(self) -> int:
        """Get program duration in months"""
        return {
            self.FUNDAMENTALS: 9,
            self.SPECIALIZATION: 9,
            self.APPRENTICESHIP: 24
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.FUNDAMENTALS: 1,
            self.SPECIALIZATION: 2,
            self.APPRENTICESHIP: 3
        }[self]

    @property
    def is_apprenticeship(self) -> bool:
        """Check if program is apprenticeship"""
        return self == self.APPRENTICESHIP

    @property
    def ritual_frequency(self) -> Dict[str, int]:
        """Get ritual frequency per week"""
        return {
            self.FUNDAMENTALS: {
                "sod": 3,  # 3 SODs par semaine lu/ma/je
                "standup": 4  # 4 standups par semaine ma/me/je/ve
            },
            self.SPECIALIZATION: {
                "sod": 1,  # 1 SOD par semaine (vendredi)
                "standup": 4  # 4 standups par semaine ma/me/je/ve
            },
            self.APPRENTICESHIP: {
                "sod": 1,  # 1 SOD par semaine (vendredi)
                "standup": 1  # 1 standup par semaine (vendredi)
            }
        }[self]

    @property
    def ritual_days(self) -> Dict[str, list]:
        """Get ritual days for each type"""
        return {
            self.FUNDAMENTALS: {
                "sod": ["MONDAY", "TUESDAY", "THURSDAY"],
                "standup": ["TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY"]
            },
            self.SPECIALIZATION: {
                "sod": ["FRIDAY"],
                "standup": ["TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY"]
            },
            self.APPRENTICESHIP: {
                "sod": ["FRIDAY"],
                "standup": ["FRIDAY"]
            }
        }[self]

    @classmethod
    def from_string(cls, value: str) -> Optional["CurriculumType"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en CurriculumType")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Type de curriculum invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_CURRICULUM_TYPE,
                curriculum_type=value
            )

    def validate_program_duration(self, months: int) -> bool:
        """Validate if given duration matches program requirements"""
        logger.debug(f"🔍 Validation durée pour {self.value}: {months} mois")
        
        if not isinstance(months, int):
            logger.error(f"❌ Type invalide pour months: {type(months)}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_DATA_TYPE,
                field="months",
                expected="integer"
            )

        if months != self.duration_months:
            logger.warning(
                f"⚠️ Durée invalide pour {self.value}: {months} mois "
                f"(attendu: {self.duration_months})"
            )
            raise ValidationException(
                error_code=ErrorCode.INVALID_PROGRAM_DURATION,
                curriculum_type=self.display_names["fr"],
                expected_months=self.duration_months,
                given_months=months
            )
        
        logger.info(f"✨ Durée validée pour {self.display_names['fr']}: {months} mois")
        return True

    def get_ritual_schedule(self, lang: str = "fr") -> str:
        """Get human-readable ritual schedule"""
        logger.debug(f"🔍 Génération planning rituels pour {self.value} (lang: {lang})")
        freq = self.ritual_frequency
        days = self.ritual_days
        schedule = {
            "fr": (
                f"{freq['sod']} SOD{'s' if freq['sod'] > 1 else ''} ({', '.join(days['sod']).lower()}) et "
                f"{freq['standup']} stand-up{'s' if freq['standup'] > 1 else ''} "
                f"({', '.join(days['standup']).lower()}) par semaine"
            ),
            "en": (
                f"{freq['sod']} SOD{'s' if freq['sod'] > 1 else ''} ({', '.join(days['sod']).lower()}) and "
                f"{freq['standup']} stand-up{'s' if freq['standup'] > 1 else ''} "
                f"({', '.join(days['standup']).lower()}) per week"
            )
        }[lang]
        logger.debug(f"📅 Planning généré: {schedule}")
        return schedule

    def __str__(self) -> str:
        """String representation"""
        return f"{self.display_names['fr']} ({self.value})"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "duration_months": self.duration_months,
            "display_order": self.display_order,
            "is_apprenticeship": self.is_apprenticeship,
            "ritual_schedule": self.get_ritual_schedule(lang),
            "ritual_frequency": self.ritual_frequency,
            "ritual_days": self.ritual_days
        }