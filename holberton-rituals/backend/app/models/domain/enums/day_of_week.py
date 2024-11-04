# app/models/domain/enums/day_of_week.py
from enum import Enum
from typing import Dict, Optional
from datetime import datetime, timedelta
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class DayOfWeek(str, Enum):
    """Days of the week for ritual scheduling ✨"""
    
    MONDAY = "monday"
    TUESDAY = "tuesday"
    WEDNESDAY = "wednesday"
    THURSDAY = "thursday"
    FRIDAY = "friday"

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.MONDAY: {
                "fr": "Lundi",
                "en": "Monday"
            },
            self.TUESDAY: {
                "fr": "Mardi",
                "en": "Tuesday"
            },
            self.WEDNESDAY: {
                "fr": "Mercredi",
                "en": "Wednesday"
            },
            self.THURSDAY: {
                "fr": "Jeudi",
                "en": "Thursday"
            },
            self.FRIDAY: {
                "fr": "Vendredi",
                "en": "Friday"
            }
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.MONDAY: 1,
            self.TUESDAY: 2,
            self.WEDNESDAY: 3,
            self.THURSDAY: 4,
            self.FRIDAY: 5
        }[self]

    @property
    def is_working_day(self) -> bool:
        """Check if it's a working day"""
        return True  # All days are working days in our context

    @property
    def next_working_day(self) -> 'DayOfWeek':
        """Get next working day"""
        day_map = {
            self.MONDAY: self.TUESDAY,
            self.TUESDAY: self.WEDNESDAY,
            self.WEDNESDAY: self.THURSDAY,
            self.THURSDAY: self.FRIDAY,
            self.FRIDAY: self.MONDAY
        }
        logger.debug(f"🔄 Prochain jour ouvré après {self.value}: {day_map[self].value}")
        return day_map[self]

    @classmethod
    def from_string(cls, value: str) -> Optional['DayOfWeek']:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en DayOfWeek")
            return cls(value.lower())
        except ValueError:
            logger.error(f"❌ Jour invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_DAY,
                day=value
            )

    @classmethod
    def from_date(cls, date: datetime) -> 'DayOfWeek':
        """Get DayOfWeek from a datetime object"""
        logger.debug(f"🔍 Conversion de la date {date.strftime('%Y-%m-%d')} en DayOfWeek")
        day_map = {
            0: cls.MONDAY,
            1: cls.TUESDAY,
            2: cls.WEDNESDAY,
            3: cls.THURSDAY,
            4: cls.FRIDAY
        }
        weekday = date.weekday()
        if weekday not in day_map:
            logger.error(f"❌ Jour non ouvré: {date.strftime('%A')}")
            raise ValidationException(
                error_code=ErrorCode.NON_WORKING_DAY,
                date=date.strftime("%Y-%m-%d")
            )
        return day_map[weekday]

    @classmethod
    def get_next_working_day(cls, from_date: datetime) -> tuple['DayOfWeek', datetime]:
        """Get next working day from a given date"""
        logger.debug(f"🔍 Recherche du prochain jour ouvré après {from_date.strftime('%Y-%m-%d')}")
        next_date = from_date
        attempts = 0
        while attempts < 7:  # Évite une boucle infinie
            next_date += timedelta(days=1)
            try:
                day = cls.from_date(next_date)
                logger.info(f"✨ Prochain jour ouvré trouvé: {day.display_names['fr']}")
                return day, next_date
            except ValidationException:
                attempts += 1
                logger.debug(f"⏭️ Jour non ouvré, tentative suivante: {next_date.strftime('%Y-%m-%d')}")
                continue
        
        logger.error("❌ Impossible de trouver le prochain jour ouvré")
        raise ValidationException(
            error_code=ErrorCode.NO_WORKING_DAY_FOUND,
            start_date=from_date.strftime("%Y-%m-%d")
        )

    @property
    def ritual_times(self) -> Dict[str, Dict[str, str]]:
        """Get ritual times for this day"""
        return {
            self.MONDAY: {
                "sod": "09:30",
                "standup": "14:00"
            },
            self.TUESDAY: {
                "sod": "09:30",
                "standup": "14:00"
            },
            self.WEDNESDAY: {
                "sod": None,  # Pas de SOD le mercredi
                "standup": "14:00"
            },
            self.THURSDAY: {
                "sod": "09:30",
                "standup": "14:00"
            },
            self.FRIDAY: {
                "sod": "09:30",
                "standup": "14:00"
            }
        }[self]

    def has_ritual(self, ritual_type: str) -> bool:
        """Check if day has specific ritual"""
        logger.debug(f"🔍 Vérification rituel {ritual_type} pour {self.value}")
        times = self.ritual_times
        has_ritual = times.get(ritual_type.lower()) is not None
        logger.debug(f"{'✨' if has_ritual else '❌'} {ritual_type} le {self.display_names['fr']}")
        return has_ritual

    def __str__(self) -> str:
        """String representation"""
        return f"{self.display_names['fr']} ({self.value})"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "display_order": self.display_order,
            "is_working_day": self.is_working_day,
            "ritual_times": self.ritual_times
        }