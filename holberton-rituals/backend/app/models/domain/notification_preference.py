# models/domain/notification_preference.py
from typing import List, Dict, Optional
from sqlalchemy import Column, Integer, String, Boolean, ForeignKey, UniqueConstraint, JSON, UUID
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.notification_type import NotificationType

logger = settings.logger  # Notre super logger loguru ! 👑

class NotificationPreference(Base):
    """User preferences for different notification types ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    user_id = Column(UUID, ForeignKey('users.id'), nullable=False)
    notification_type = Column(String(30), ForeignKey('enum_notification_type.value'), nullable=False)
    email_enabled = Column(Boolean, default=True)
    in_app_enabled = Column(Boolean, default=True)
    slack_enabled = Column(Boolean, default=True)
    quiet_hours = Column(JSON, nullable=True, 
                        comment='{"start": "HH:MM", "end": "HH:MM", "timezone": "Europe/Paris"}')

    # Relationships
    user = relationship("User", back_populates="notification_preferences")

    # Constraints
    __table_args__ = (
        UniqueConstraint('user_id', 'notification_type', 
                        name='uq_user_notif_pref'),
    )

    @property
    def type_display_name(self) -> str:
        """Get localized notification type name"""
        return NotificationType(self.notification_type).display_names['fr']

    @property
    def type_description(self) -> str:
        """Get localized notification type description"""
        return NotificationType(self.notification_type).descriptions['fr']

    @property
    def priority(self) -> int:
        """Get notification priority"""
        return NotificationType(self.notification_type).priority

    @property
    def requires_immediate_attention(self) -> bool:
        """Check if this notification type requires immediate attention"""
        return NotificationType(self.notification_type).requires_immediate_attention

    @property
    def has_any_enabled(self) -> bool:
        """Check if any notification method is enabled"""
        logger.debug(f"🔍 Vérification canaux actifs pour {self.notification_type}")
        return any([self.email_enabled, self.in_app_enabled, self.slack_enabled])

    @property
    def enabled_channels(self) -> List[str]:
        """Get list of enabled notification channels"""
        logger.debug(f"📋 Liste des canaux actifs pour {self.notification_type}")
        channels = []
        if self.email_enabled:
            channels.append("email")
        if self.in_app_enabled:
            channels.append("in_app")
        if self.slack_enabled:
            channels.append("slack")
        return channels

    def is_in_quiet_hours(self, current_time: str) -> bool:
        """Check if current time is within quiet hours"""
        logger.debug(f"🔍 Vérification quiet hours pour {current_time}")
        
        if not self.quiet_hours:
            return False
            
        try:
            start = self.quiet_hours.get('start')
            end = self.quiet_hours.get('end')
            if not (start and end):
                return False
                
            return start <= current_time <= end
            
        except Exception as e:
            logger.error(f"❌ Erreur vérification quiet hours: {str(e)}")
            return False

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour {self.notification_type}")
        notif_type = NotificationType(self.notification_type)
        return {
            "notification_type": self.notification_type,
            "display_name": notif_type.display_names['fr'],
            "description": notif_type.descriptions['fr'],
            "priority": notif_type.priority,
            "channels": {
                "email": self.email_enabled,
                "in_app": self.in_app_enabled,
                "slack": self.slack_enabled
            },
            "quiet_hours": self.quiet_hours
        }

    def update_preferences(self, 
                         email: Optional[bool] = None, 
                         in_app: Optional[bool] = None, 
                         slack: Optional[bool] = None,
                         quiet_hours: Optional[Dict] = None) -> None:
        """Update notification preferences"""
        logger.debug(f"📝 Mise à jour préférences pour {self.notification_type}")
        
        if email is not None:
            self.email_enabled = email
        if in_app is not None:
            self.in_app_enabled = in_app
        if slack is not None:
            self.slack_enabled = slack
        if quiet_hours is not None:
            self.quiet_hours = quiet_hours
            
        if not self.has_any_enabled:
            logger.warning(f"⚠️ Aucun canal actif pour {self.notification_type}")

    def validate(self) -> None:
        """Validate notification preferences"""
        logger.debug(f"🔍 Validation préférences pour {self.notification_type}")
        
        if not self.has_any_enabled:
            logger.error("❌ Aucun canal de notification actif")
            raise ValidationException(
                error_code=ErrorCode.NO_NOTIFICATION_CHANNEL,
                notification_type=self.type_display_name
            )

    def __str__(self) -> str:
        """String representation"""
        channels = ", ".join(self.enabled_channels) or "none"
        priority_emoji = "🚨" if self.requires_immediate_attention else "📢"
        return (
            f"{priority_emoji} {self.type_display_name}: "
            f"{channels} enabled (Priority: {self.priority})"
        )