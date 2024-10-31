from sqlalchemy import Column, Integer, Text, Date, DateTime, Enum, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import enum
from ..core.database import Base

class UnavailabilityStatus(str, enum.Enum):
    PENDING = "pending"
    VALIDATED = "validated"
    REJECTED = "rejected"

class StudentUnavailability(Base):
    __tablename__ = "student_unavailability"

    id = Column(Integer, primary_key=True, index=True)
    student_id = Column(Integer, ForeignKey("students.id"))
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    reason = Column(Text)
    status = Column(Enum(UnavailabilityStatus), default=UnavailabilityStatus.PENDING)
    validated_by = Column(Integer, ForeignKey("users.id"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    student = relationship("Student", backref="unavailabilities")
    validator = relationship("User", backref="validated_unavailabilities")