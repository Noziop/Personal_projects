from sqlalchemy import Column, Integer, String, Date, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from ..core.database import Base

class StudentCohortHistory(Base):
    __tablename__ = "student_cohort_history"

    id = Column(Integer, primary_key=True, index=True)
    student_id = Column(Integer, ForeignKey("students.id"))
    cohort_id = Column(Integer, ForeignKey("cohorts.id"))
    start_date = Column(Date, nullable=False)
    end_date = Column(Date)
    reason = Column(String)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    # Relations
    student = relationship("Student", backref="cohort_history")
    cohort = relationship("Cohort", backref="student_history")