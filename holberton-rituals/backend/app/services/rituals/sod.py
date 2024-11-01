from datetime import datetime, timedelta
from typing import List, Optional, Tuple, Dict
from sqlalchemy.orm import Session
from sqlalchemy import and_

from ...models.students import Student
from ...models.rituals import SODDrawing
from ...models.unavailability import StudentUnavailability, UnavailabilityStatus
from ..drawing.rules import DrawingRules
from ..notifications.slack import SlackNotificationService
from ..statistics import StatisticsService
from ...models.statistics import StatisticsType

class SODService:
    """Service gérant les tirages et la logique des Speaker of the Day"""

    @staticmethod
    def get_eligible_students(
        db: Session,
        cohort_id: int,
        date: datetime
    ) -> List[Student]:
        """
        Récupère les étudiants éligibles pour le SOD en tenant compte :
        - Des indisponibilités
        - Du délai minimum entre passages
        - Des jours autorisés selon le type de cursus
        """
        # Exclusion des passages récents
        recent_date = date - timedelta(days=DrawingRules.MIN_DAYS_BETWEEN_SOD)
        recent_speakers = db.query(SODDrawing.student_id).filter(
            SODDrawing.presentation_date >= recent_date
        ).subquery()

        # Exclusion des indisponibles
        unavailable = db.query(StudentUnavailability.student_id).filter(
            StudentUnavailability.start_date <= date,
            StudentUnavailability.end_date >= date,
            StudentUnavailability.status == UnavailabilityStatus.VALIDATED
        ).subquery()

        return db.query(Student).join(Student.user).filter(
            Student.current_cohort_id == cohort_id,
            Student.user.is_active == True,
            Student.id.notin_(recent_speakers),
            Student.id.notin_(unavailable)
        ).all()

    @staticmethod
    def get_eligible_evaluators(
        db: Session,
        cohort_id: int,
        speaker_id: int,
        date: datetime
    ) -> List[Student]:
        """Récupère les étudiants éligibles pour être évaluateur"""
        # Un évaluateur ne doit pas :
        # - Être le speaker
        # - Être indisponible
        # - Avoir déjà évalué récemment (7 jours)
        recent_date = date - timedelta(days=7)
        recent_evaluators = db.query(SODDrawing.evaluator_id).filter(
            SODDrawing.presentation_date >= recent_date
        ).subquery()

        unavailable = db.query(StudentUnavailability.student_id).filter(
            StudentUnavailability.start_date <= date,
            StudentUnavailability.end_date >= date,
            StudentUnavailability.status == UnavailabilityStatus.VALIDATED
        ).subquery()

        return db.query(Student).join(Student.user).filter(
            Student.current_cohort_id == cohort_id,
            Student.user.is_active == True,
            Student.id != speaker_id,
            Student.id.notin_(unavailable),
            Student.id.notin_(recent_evaluators)
        ).all()

    @staticmethod
    def draw_sod(
        db: Session,
        cohort_id: int,
        date: datetime,
        force: bool = False
    ) -> Tuple[Optional[SODDrawing], List[str]]:
        """Effectue le tirage pour un SOD"""
        warnings = []

        # Vérification date valide
        is_valid, errors = DrawingRules.validate_drawing_period(date, date)
        if not is_valid:
            return None, errors

        # Vérification tirage existant
        existing = db.query(SODDrawing).join(Student).filter(
            Student.current_cohort_id == cohort_id,
            SODDrawing.presentation_date == date
        ).first()

        if existing and not force:
            warnings.append(f"Un SOD est déjà programmé pour le {date}")
            return None, warnings

        if existing and force:
            warnings.append(f"Remplacement du SOD existant pour le {date}")
            db.delete(existing)
            db.commit()

        # Récupération des étudiants éligibles
        eligible_speakers = SODService.get_eligible_students(db, cohort_id, date)
        if not eligible_speakers:
            warnings.append("Aucun étudiant éligible trouvé pour présenter")
            return None, warnings

        # Tirage du speaker
        chosen_speaker = SODService._weighted_draw(eligible_speakers)

        # Tirage de l'évaluateur
        eligible_evaluators = SODService.get_eligible_evaluators(
            db, cohort_id, chosen_speaker.id, date
        )
        if not eligible_evaluators:
            warnings.append("Aucun évaluateur éligible trouvé")
            return None, warnings

        chosen_evaluator = SODService._weighted_draw(eligible_evaluators)

        # Création du tirage
        drawing = SODDrawing(
            student_id=chosen_speaker.id,
            evaluator_id=chosen_evaluator.id,
            presentation_date=date
        )

        chosen_speaker.sod_count += 1
        
        db.add(drawing)
        db.commit()
        db.refresh(drawing)

        return drawing, warnings

    @staticmethod
    def draw_period(
        db: Session,
        cohort_id: int,
        start_date: datetime,
        end_date: datetime,
        force: bool = False
    ) -> Tuple[List[SODDrawing], List[str]]:
        """Effectue les tirages sur une période donnée"""
        drawings = []
        warnings = []

        current_date = start_date
        while current_date <= end_date:
            drawing, draw_warnings = SODService.draw_sod(
                db, cohort_id, current_date, force
            )
            if drawing:
                drawings.append(drawing)
            warnings.extend(draw_warnings)
            current_date += timedelta(days=1)

        return drawings, warnings

    @staticmethod
    def handle_emergency_replacement(
        db: Session,
        drawing_id: int,
        reason: str
    ) -> Tuple[Optional[SODDrawing], List[str]]:
        """Gère le remplacement d'urgence d'un speaker"""
        warnings = []
        
        original = db.query(SODDrawing).get(drawing_id)
        if not original:
            warnings.append("Tirage non trouvé")
            return None, warnings

        # Trouver un remplaçant
        eligible_speakers = SODService.get_eligible_students(
            db,
            original.student.current_cohort_id,
            original.presentation_date
        )
        
        # Exclure le speaker original
        eligible_speakers = [
            s for s in eligible_speakers 
            if s.id != original.student_id
        ]

        if not eligible_speakers:
            warnings.append("Aucun remplaçant disponible")
            return None, warnings

        # Choisir le remplaçant avec le moins de passages
        replacement = min(eligible_speakers, key=lambda s: s.sod_count)

        # Créer le nouveau tirage
        new_drawing = SODDrawing(
            student_id=replacement.id,
            evaluator_id=original.evaluator_id,
            presentation_date=original.presentation_date,
            is_replacement=True,
            replacement_reason=reason
        )

        replacement.sod_count += 1
        
        db.add(new_drawing)
        db.delete(original)
        db.commit()
        db.refresh(new_drawing)

        warnings.append(
            f"Remplacement effectué : {original.student_id} -> {replacement.id}"
        )
        return new_drawing, warnings

    @staticmethod
    def _weighted_draw(students: List[Student]) -> Student:
        """Effectue un tirage pondéré"""
        import random

        # Calcul des poids
        weighted_students = []
        for student in students:
            weight = 1 / (student.sod_count + 1)  # Évite division par zéro
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
            ritual_type=StatisticsType.SOD,
            start_date=start_date,
            end_date=end_date,
            save_results=False
        )
    
    @staticmethod
    async def notify_replacement(
        db: Session,
        drawing: SODDrawing,
        original_student: Student,
        reason: str
    ) -> List[str]:
        """Notifie les personnes concernées d'un remplacement"""
        notifications = []
        slack = SlackNotificationService()

        # Message pour le remplaçant
        replacement_msg = (
            f"🚨 *Remplacement urgent* 🚨\n"
            f"Tu as été désigné(e) pour remplacer {original_student.user.first_name} "
            f"pour le SOD d'aujourd'hui.\n"
            f"Heure : {drawing.presentation_date.strftime('%H:%M')}\n"
            f"Évaluateur : {drawing.evaluator.user.first_name}"
        )
        
        if await slack.send_notification(
            drawing.student.user.slack_id,
            replacement_msg,
            is_replacement=True,
            reason=reason
        ):
            notifications.append(f"Notification envoyée à {drawing.student.user.email}")

        # Message pour l'évaluateur
        evaluator_msg = (
            f"ℹ️ *Changement de Speaker* ℹ️\n"
            f"{original_student.user.first_name} est remplacé(e) par "
            f"{drawing.student.user.first_name} pour le SOD d'aujourd'hui."
        )
        
        if await slack.send_notification(
            drawing.evaluator.user.slack_id,
            evaluator_msg,
            is_replacement=True,
            reason=reason
        ):
            notifications.append(f"Notification envoyée à {drawing.evaluator.user.email}")

        # Notification à la cohorte
        cohort_msg = (
            f"📢 *Mise à jour SOD* 📢\n"
            f"Speaker : {drawing.student.user.first_name}\n"
            f"Évaluateur : {drawing.evaluator.user.first_name}\n"
            f"Heure : {drawing.presentation_date.strftime('%H:%M')}"
        )
        
        # Récupérer tous les slack_ids de la cohorte
        cohort_members = db.query(Student).filter(
            Student.current_cohort_id == drawing.student.current_cohort_id
        ).all()
        
        slack_ids = [
            s.user.slack_id for s in cohort_members 
            if s.user.slack_id
        ]
        
        results = await slack.send_bulk_notification(
            slack_ids,
            cohort_msg,
            is_replacement=True
        )
        
        notifications.extend(
            f"Notification envoyée à la cohorte : {len(results)} membres"
        )

        return notifications