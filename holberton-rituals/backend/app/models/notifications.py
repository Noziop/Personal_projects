# Pour plus tard, dans backend/app/models/notifications.py
from sqlalchemy import Column, Integer, String, DateTime, Boolean, Enum, ForeignKey
from sqlalchemy.orm import relationship
import enum
from ..core.database import Base
from sqlalchemy.sql import func

class NotificationType(str, enum.Enum):
    UNAVAILABILITY_REQUEST = "unavailability_request"
    SOD_FEEDBACK = "sod_feedback"
    STANDUP_FEEDBACK = "standup_feedback"
    RITUAL_REMINDER = "ritual_reminder"

class InAppNotification(Base):
    __tablename__ = "in_app_notifications"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"))
    type = Column(Enum(NotificationType))
    title = Column(String)
    message = Column(String)
    link = Column(String)  # Lien vers le formulaire ou la page concernée
    is_read = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    expires_at = Column(DateTime(timezone=True))

    user = relationship("User", backref="notifications")