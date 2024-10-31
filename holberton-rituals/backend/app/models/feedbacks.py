from sqlalchemy import Column, Integer, Text, Date, DateTime, Enum, ForeignKey, JSON, Boolean
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from .rituals import RitualType
from ..core.database import Base

class FeedbackTemplate(Base):
    __tablename__ = "feedback_templates"

    id = Column(Integer, primary_key=True, index=True)
    ritual_type = Column(Enum(RitualType), nullable=False)
    template = Column(JSON, nullable=False)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

class Feedback(Base):
    __tablename__ = "feedbacks"

    id = Column(Integer, primary_key=True, index=True)
    ritual_type = Column(Enum(RitualType), nullable=False)
    template_id = Column(Integer, ForeignKey("feedback_templates.id"))
    evaluator_id = Column(Integer, ForeignKey("students.id"))
    evaluated_id = Column(Integer, ForeignKey("students.id"))
    feedback_data = Column(JSON, nullable=False)
    presentation_url = Column(Text)
    session_date = Column(Date, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    template = relationship("FeedbackTemplate")
    evaluator = relationship("Student", foreign_keys=[evaluator_id], backref="given_feedbacks")
    evaluated = relationship("Student", foreign_keys=[evaluated_id], backref="received_feedbacks")