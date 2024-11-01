from datetime import datetime, timedelta
from typing import List, Dict, Tuple
from sqlalchemy.orm import Session
import statistics

from ...models.students import Student
from ...models.public_holidays import PublicHoliday
from ...models.cohorts import Cohort, CurriculumType
from ...models.rituals import RitualType

class DrawingRules:
    """Règles et contraintes pour les tirages au sort"""

    # Constantes de configuration
    MIN_DAYS_BETWEEN_SOD = 18
    MAX_MONTHS_ADVANCE = 3
    MIN_STUDENTS_FOR_DRAWING = 2

    # Jours autorisés par rituel
    RITUAL_DAYS = {
        RitualType.STANDUP: ["tuesday", "wednesday", "thursday", "friday"],
        RitualType.SOD: {
            CurriculumType.FUNDAMENTALS: ["monday", "tuesday", "thursday"],
            CurriculumType.SPECIALIZATION: ["friday"]
        }
    }

    @classmethod
    def is_valid_ritual_day(
        cls,
        date: datetime,
        ritual_type: RitualType,
        curriculum_type: CurriculumType = None
    ) -> bool:
        """Vérifie si le jour est valide pour le rituel"""
        day = date.strftime("%A").lower()
        
        if ritual_type == RitualType.STANDUP:
            return day in cls.RITUAL_DAYS[ritual_type]
        
        if not curriculum_type:
            raise ValueError("curriculum_type requis pour les SOD")
            
        return day in cls.RITUAL_DAYS[ritual_type][curriculum_type]

    @classmethod
    def validate_drawing_period(
        cls,
        start_date: datetime,
        end_date: datetime,
        ritual_type: RitualType
    ) -> Tuple[bool, List[str]]:
        """Valide une période de tirage"""
        errors = []
        now = datetime.now()

        # Règles temporelles
        if start_date.date() < now.date():
            errors.append("La date de début ne peut pas être dans le passé")

        if end_date < start_date:
            errors.append("La date de fin doit être après la date de début")

        max_date = now + timedelta(days=30 * cls.MAX_MONTHS_ADVANCE)
        if end_date > max_date:
            errors.append(
                f"Les tirages ne peuvent pas être planifiés plus de "
                f"{cls.MAX_MONTHS_ADVANCE} mois à l'avance"
            )

        return len(errors) == 0, errors

    @staticmethod
    def is_holiday(db: Session, date: datetime) -> bool:
        """Vérifie si la date est un jour férié"""
        return db.query(PublicHoliday).filter(
            PublicHoliday.date == date.date()
        ).first() is not None

    @staticmethod
    def is_cohort_pause(cohort: Cohort, date: datetime) -> bool:
        """Vérifie si la date tombe pendant une pause de la cohorte"""
        if not cohort.pause_periods:
            return False
        
        for period in cohort.pause_periods:
            if period['start_date'] <= date.date() <= period['end_date']:
                return True
        return False

    @classmethod
    def calculate_weights(
        cls,
        students: List[Student],
        ritual_type: RitualType
    ) -> Dict[int, float]:
        """
        Calcule les poids pour le tirage au sort
        Plus sophistiqué que la version précédente
        """
        if len(students) < cls.MIN_STUDENTS_FOR_DRAWING:
            raise ValueError(
                f"Il faut au moins {cls.MIN_STUDENTS_FOR_DRAWING} étudiants "
                "pour effectuer un tirage"
            )

        weights = {}
        count_field = 'sod_count' if ritual_type == RitualType.SOD else 'standup_count'
        
        # Calcul des statistiques de base
        counts = [getattr(s, count_field) for s in students]
        min_count = min(counts)
        avg_count = statistics.mean(counts)
        
        for student in students:
            count = getattr(student, count_field)
            
            # Facteurs de pondération
            passage_factor = 1 / (count - min_count + 1)  # Équité
            catch_up_factor = 1 + max(0, (avg_count - count) / 2)  # Rattrapage
            
            # Bonus pour ceux qui ne sont jamais passés
            never_passed_bonus = 1.5 if count == 0 else 1
            
            # Poids final
            weight = passage_factor * catch_up_factor * never_passed_bonus
            weights[student.id] = weight

        return weights

    @classmethod
    def get_next_valid_date(
        cls,
        db: Session,
        cohort: Cohort,
        start_date: datetime,
        ritual_type: RitualType
    ) -> datetime:
        """Trouve la prochaine date valide pour un rituel"""
        current_date = start_date

        while True:
            if not cls.is_valid_ritual_day(
                current_date,
                ritual_type,
                cohort.curriculum_type
            ):
                current_date += timedelta(days=1)
                continue

            if cls.is_holiday(db, current_date):
                current_date += timedelta(days=1)
                continue

            if cls.is_cohort_pause(cohort, current_date):
                current_date += timedelta(days=1)
                continue

            return current_date