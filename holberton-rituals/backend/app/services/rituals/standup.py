from datetime import datetime, timedelta
from typing import List, Optional, Tuple, Dict
from sqlalchemy.orm import Session

from ...models.students import Student
from ...models.rituals import StandupAssignment
from ...models.unavailability import StudentUnavailability, UnavailabilityStatus
from ..drawing.rules import DrawingRules
from ..statistics import StatisticsService
from ...models.statistics import StatisticsType

class StandupService:
    """Service gérant les tirages et la logique des Stand-ups"""

    @staticmethod
    def get_eligible_students(
        db: Session,
        cohort_id: int,
        date: datetime
    ) -> List[Student]:
        """Récupère les étudiants éligibles pour le Stand-up"""
        # Vérification du jour
        if date.strftime("%A").lower() not in DrawingRules.STANDUP_DAYS:
            return []

        # Exclusion des indisponibles
        unavailable = db.query(StudentUnavailability.student_id).filter(
            StudentUnavailability.start_date <= date,
            StudentUnavailability.end_date >= date,
            StudentUnavailability.status == UnavailabilityStatus.VALIDATED
        ).subquery()

        return db.query(Student).join(Student.user).filter(
            Student.current_cohort_id == cohort_id,
            Student.user.is_active == True,
            Student.id.notin_(unavailable)
        ).all()

    @staticmethod
    def draw_standup(
        db: Session,
        cohort_id: int,
        date: datetime,
        force: bool = False
    ) -> Tuple[Optional[StandupAssignment], List[str]]:
        """Effectue le tirage pour un Stand-up"""
        warnings = []

        # Vérification date valide
        is_valid, errors = DrawingRules.validate_drawing_period(date, date)
        if not is_valid:
            return None, errors

        # Vérification tirage existant
        existing = db.query(StandupAssignment).join(Student).filter(
            Student.current_cohort_id == cohort_id,
            StandupAssignment.assignment_date == date
        ).first()

        if existing and not force:
            warnings.append(f"Un Scrum Master est déjà assigné pour le {date}")
            return None, warnings

        if existing and force:
            warnings.append(f"Remplacement de l'assignation existante pour le {date}")
            db.delete(existing)
            db.commit()

        # Récupération des étudiants éligibles
        eligible_students = StandupService.get_eligible_students(db, cohort_id, date)
        if not eligible_students:
            warnings.append("Aucun étudiant éligible trouvé")
            return None, warnings

        # Calcul des poids et tirage
        chosen_student = StandupService._weighted_draw(eligible_students)
        
        # Création de l'assignation
        assignment = StandupAssignment(
            student_id=chosen_student.id,
            assignment_date=date
        )
        
        chosen_student.standup_count += 1
        
        db.add(assignment)
        db.commit()
        db.refresh(assignment)
        
        return assignment, warnings

    @staticmethod
    def draw_week(
        db: Session,
        cohort_id: int,
        start_date: datetime,
        force: bool = False
    ) -> Tuple[List[StandupAssignment], List[str]]:
        """Effectue les tirages pour une semaine entière"""
        assignments = []
        all_warnings = []
        
        # Ajustement au lundi
        while start_date.strftime("%A").lower() != "monday":
            start_date -= timedelta(days=1)

        # Tirage pour chaque jour de la semaine
        for day_offset in range(1, 5):  # 1=Mardi à 4=Vendredi
            draw_date = start_date + timedelta(days=day_offset)
            assignment, warnings = StandupService.draw_standup(
                db, cohort_id, draw_date, force
            )
            if assignment:
                assignments.append(assignment)
            all_warnings.extend(warnings)

        return assignments, all_warnings

    @staticmethod
    def _weighted_draw(students: List[Student]) -> Student:
        """Effectue un tirage pondéré"""
        import random

        # Calcul du minimum de passages
        min_count = min(s.standup_count for s in students)
        
        # Calcul des poids
        weights = {}
        for student in students:
            difference = student.standup_count - min_count
            # Plus sophistiqué que le SOD
            weight = 1 + (3 * (1 / (difference + 1)))
            # Bonus pour les nouveaux
            if student.standup_count == 0:
                weight *= 1.5
            weights[student.id] = weight

        # Création de la liste pondérée
        weighted_students = []
        for student in students:
            weight = weights[student.id]
            weighted_students.extend([student] * int(weight * 10))

        return random.choice(weighted_students)

    @staticmethod
    def get_statistics(
        db: Session,
        cohort_id: int,
        start_date: Optional[datetime] = None,
        end_date: Optional[datetime] = None
    ) -> Dict:
        """Récupère les statistiques des SOD"""
        return StatisticsService.calculate_ritual_statistics(
            db=db,
            cohort_id=cohort_id,
            ritual_type=StatisticsType.STANDUP,
            start_date=start_date,
            end_date=end_date,
            save_results=False
        )