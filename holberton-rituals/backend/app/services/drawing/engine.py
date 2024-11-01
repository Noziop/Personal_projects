from sqlalchemy.orm import Session
from typing import List, Optional, Tuple, Dict
from datetime import datetime

from ...models.students import Student
from ..rituals.sod import SODService
from ..rituals.standup import StandupService

class DrawingEngine:
    """
    Classe principale qui coordonne les tirages au sort
    Délègue la logique spécifique aux services appropriés
    """

    @staticmethod
    def draw_rituals(
        db: Session,
        cohort_id: int,
        start_date: datetime,
        end_date: datetime,
        ritual_types: List[str] = ["sod", "standup"],
        force: bool = False
    ) -> Dict[str, Tuple[List, List[str]]]:
        """
        Point d'entrée principal pour effectuer des tirages
        Retourne un dict avec les résultats et warnings pour chaque type de rituel
        """
        results = {}

        if "sod" in ritual_types:
            drawings, warnings = SODService.draw_period(
                db, cohort_id, start_date, end_date, force
            )
            results["sod"] = (drawings, warnings)

        if "standup" in ritual_types:
            drawings, warnings = StandupService.draw_week(
                db, cohort_id, start_date, force
            )
            results["standup"] = (drawings, warnings)

        return results

    @staticmethod
    def get_combined_statistics(
        db: Session,
        cohort_id: int,
        start_date: Optional[datetime] = None,
        end_date: Optional[datetime] = None
    ) -> Dict:
        """
        Récupère les statistiques combinées pour tous les rituels
        """
        return {
            "sod": SODService.get_statistics(db, cohort_id, start_date, end_date),
            "standup": StandupService.get_statistics(db, cohort_id, start_date, end_date)
        }