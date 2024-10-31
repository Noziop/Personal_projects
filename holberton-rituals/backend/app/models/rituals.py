from sqlalchemy import Column, Integer, String, Date, DateTime, Enum, ForeignKey, JSON
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import enum
from ..core.database import Base

class DayOfWeek(str, enum.Enum):
    MONDAY = "monday"
    TUESDAY = "tuesday"
    WEDNESDAY = "wednesday"
    THURSDAY = "thursday"
    FRIDAY = "friday"

class RitualType(str, enum.Enum):
    SOD = "sod"
    STANDUP = "standup"

class CohortRitualDay(Base):
    __tablename__ = "cohort_ritual_days"

    id = Column(Integer, primary_key=True, index=True)
    cohort_id = Column(Integer, ForeignKey("cohorts.id"))
    day = Column(Enum(DayOfWeek), nullable=False)
    ritual_type = Column(Enum(RitualType), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    cohort = relationship("Cohort", backref="ritual_days")

class SODDrawing(Base):
    __tablename__ = "sod_drawings"

    id = Column(Integer, primary_key=True, index=True)
    student_id = Column(Integer, ForeignKey("students.id"))
    presentation_date = Column(Date, nullable=False)
    evaluator_id = Column(Integer, ForeignKey("students.id"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    student = relationship("Student", foreign_keys=[student_id], backref="sod_presentations")
    evaluator = relationship("Student", foreign_keys=[evaluator_id], backref="sod_evaluations")