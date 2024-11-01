from datetime import datetime
from typing import Dict, Optional
from sqlalchemy.orm import Session
import statistics

from ..models.statistics import RitualStatistics, StatisticsType
from ..models.students import Student
from ..models.rituals import SODDrawing, StandupAssignment
from .rituals.sod import SODService
from .rituals.standup import StandupService

class StatisticsService:
    """Service dédié au calcul et à la gestion des statistiques"""

    @staticmethod
    def calculate_ritual_statistics(
        db: Session,
        cohort_id: int,
        ritual_type: StatisticsType,
        start_date: Optional[datetime] = None,
        end_date: Optional[datetime] = None,
        save_results: bool = True
    ) -> Dict:
        """Calcule les statistiques pour un type de rituel"""
        if ritual_type == StatisticsType.SOD:
            stats = SODService.get_statistics(db, cohort_id, start_date, end_date)
        elif ritual_type == StatisticsType.STANDUP:
            stats = StandupService.get_statistics(db, cohort_id, start_date, end_date)
        else:  # COMBINED
            stats = {
                "sod": SODService.get_statistics(db, cohort_id, start_date, end_date),
                "standup": StandupService.get_statistics(db, cohort_id, start_date, end_date)
            }

        if save_results:
            stats_record = RitualStatistics(
                cohort_id=cohort_id,
                type=ritual_type,
                period_start=start_date,
                period_end=end_date,
                stats_data=stats
            )
            db.add(stats_record)
            db.commit()

        return stats

    @staticmethod
    def get_historical_statistics(
        db: Session,
        cohort_id: int,
        ritual_type: Optional[StatisticsType] = None,
        limit: int = 10
    ) -> Dict:
        """Récupère l'historique des statistiques"""
        query = db.query(RitualStatistics).filter(
            RitualStatistics.cohort_id == cohort_id
        )
        
        if ritual_type:
            query = query.filter(RitualStatistics.type == ritual_type)
            
        return query.order_by(
            RitualStatistics.created_at.desc()
        ).limit(limit).all()

    @staticmethod
    def calculate_progression(
        db: Session,
        cohort_id: int,
        ritual_type: StatisticsType,
        periods: int = 4
    ) -> Dict:
        """Calcule la progression sur plusieurs périodes"""
        historical_stats = StatisticsService.get_historical_statistics(
            db, cohort_id, ritual_type, periods
        )
        
        progression = {
            "periods": [],
            "trends": {}
        }

        for stats in historical_stats:
            period_data = {
                "period_start": stats.period_start,
                "period_end": stats.period_end,
                "stats": stats.stats_data
            }
            progression["periods"].append(period_data)

        # Calcul des tendances
        if len(historical_stats) >= 2:
            first = historical_stats[-1].stats_data
            last = historical_stats[0].stats_data
            
            progression["trends"] = {
                "fairness_evolution": last.get("fairness_score", 0) - 
                                    first.get("fairness_score", 0),
                "participation_growth": last.get("total_presentations", 0) - 
                                      first.get("total_presentations", 0)
            }

        return progression