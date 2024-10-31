from sqlalchemy import Column, Integer, String, Date, Boolean, DateTime, Enum, JSON
from sqlalchemy.sql import func
import enum
from ..core.database import Base

class CurriculumType(str, enum.Enum):
    FUNDAMENTALS = "fundamentals"
    SPECIALIZATION = "specialization"

class Cohort(Base):
    __tablename__ = "cohorts"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), nullable=False)
    curriculum_type = Column(Enum(CurriculumType), nullable=False)
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    slack_channel = Column(String(100))
    slack_workspace_id = Column(String(100))
    pause_periods = Column(JSON)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())