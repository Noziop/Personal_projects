from sqlalchemy import Column, Integer, DateTime, JSON, ForeignKey, Enum
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import enum
from ..core.database import Base

class StatisticsType(str, enum.Enum):
    SOD = "sod"
    STANDUP = "standup"
    COMBINED = "combined"

class RitualStatistics(Base):
    __tablename__ = "ritual_statistics"

    id = Column(Integer, primary_key=True, index=True)
    cohort_id = Column(Integer, ForeignKey("cohorts.id"))
    type = Column(Enum(StatisticsType))
    period_start = Column(DateTime(timezone=True))
    period_end = Column(DateTime(timezone=True))
    stats_data = Column(JSON)  # Stocke les stats calculées
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    cohort = relationship("Cohort", backref="ritual_statistics")