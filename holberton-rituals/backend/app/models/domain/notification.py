# models/domain/notification.py
from datetime import datetime
from typing import Dict, Optional
from sqlalchemy import Column, String, Text, Boolean, DateTime, ForeignKey, Index, JSON, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.notification_type import NotificationType

logger = settings.logger  # Notre super logger loguru ! 👑

class Notification(Base):
    """In-app notifications for users ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    user_id = Column(UUID, ForeignKey('users.id'), nullable=False)
    type = Column(String(30), ForeignKey('enum_notification_type.value'), nullable=False)
    title = Column(String(255), nullable=False)
    message = Column(Text, nullable=False)
    link = Column(Text)
    is_read = Column(Boolean, default=False)
    is_urgent = Column(Boolean, default=False)
    channels = Column(JSON, nullable=False, default=lambda: ["in_app"])
    read_at = Column(DateTime(timezone=True))
    expires_at = Column(DateTime(timezone=True), nullable=False)

    # Relationships
    user = relationship("User", back_populates="notifications")

    # Indexes
    __table_args__ = (
        Index('idx_notif_user_unread', 'user_id', 'is_read'),
        Index('idx_notif_expiry', 'expires_at'),
    )

    @property
    def type_display_name(self) -> str:
        """Get localized notification type name"""
        return NotificationType(self.type).display_names['fr']

    @property
    def type_description(self) -> str:
        """Get localized notification type description"""
        return NotificationType(self.type).descriptions['fr']

    @property
    def priority(self) -> int:
        """Get notification priority"""
        return NotificationType(self.type).priority

    @property
    def is_expired(self) -> bool:
        """Check if notification has expired"""
        logger.debug(f"🔍 Vérification expiration pour notification {self.id}")
        now = datetime.now(self.expires_at.tzinfo)
        is_expired = now > self.expires_at
        
        if is_expired:
            logger.info(f"⏰ Notification {self.id} expirée")
            
        return is_expired

    @property
    def is_active(self) -> bool:
        """Check if notification is still active"""
        return not (self.is_read or self.is_expired)

    @property
    def status_emoji(self) -> str:
        """Get emoji representing notification status"""
        if self.is_expired:
            return "⏰"
        if self.is_read:
            return "👁️"
        if self.is_urgent or NotificationType(self.type).requires_immediate_attention:
            return "🚨"
        return "🔔"

    def mark_as_read(self) -> None:
        """Mark notification as read"""
        logger.debug(f"📝 Marquage notification {self.id} comme lue")
        self.is_read = True
        self.read_at = datetime.now(self.created_at.tzinfo)
        logger.info(f"✅ Notification {self.id} marquée comme lue")

    def validate_channels(self) -> None:
        """Validate notification channels"""
        logger.debug(f"🔍 Validation canaux pour notification {self.id}")
        
        valid_channels = {"email", "slack", "in_app"}
        invalid_channels = set(self.channels) - valid_channels
        
        if invalid_channels:
            logger.error(f"❌ Canaux invalides: {invalid_channels}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_NOTIFICATION_CHANNELS,
                channels=list(invalid_channels)
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour notification {self.id}")
        notif_type = NotificationType(self.type)
        return {
            "id": str(self.id),
            "type": self.type,
            "type_display_name": notif_type.display_names['fr'],
            "type_description": notif_type.descriptions['fr'],
            "priority": notif_type.priority,
            "title": self.title,
            "message": self.message,
            "link": self.link,
            "channels": self.channels,
            "is_read": self.is_read,
            "is_urgent": self.is_urgent,
            "is_expired": self.is_expired,
            "created_at": self.created_at.isoformat(),
            "read_at": self.read_at.isoformat() if self.read_at else None,
            "expires_at": self.expires_at.isoformat()
        }

    def to_push_notification(self) -> Dict:
        """Format for push notification"""
        logger.debug(f"📱 Formatage push pour notification {self.id}")
        return {
            "title": f"{self.status_emoji} {self.title}",
            "body": self.message,
            "link": self.link,
            "urgent": self.is_urgent or NotificationType(self.type).requires_immediate_attention,
            "priority": self.priority,
            "channels": self.channels
        }

    def validate(self) -> None:
        """Validate notification"""
        logger.debug(f"🔍 Validation complète notification {self.id}")
        self.validate_channels()
        
        if not self.expires_at:
            logger.error("❌ Date d'expiration manquante")
            raise ValidationException(
                error_code=ErrorCode.MISSING_EXPIRATION_DATE,
                notification_id=str(self.id)
            )

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.status_emoji} {self.type_display_name}: {self.title} "
            f"({'Lu' if self.is_read else 'Non lu'})"
        )