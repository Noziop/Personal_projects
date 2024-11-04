# models/domain/user.py
from typing import Optional, Dict, List
from datetime import datetime, timezone
from sqlalchemy import Column, String, Boolean, DateTime, ForeignKey, Index, UUID, Integer
from sqlalchemy.orm import relationship
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.user_role import UserRole
from .enums.notification_type import NotificationType

logger = settings.logger  # Notre super logger loguru ! 👑

class User(Base):
    """User model for authentication and base user information ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    email = Column(String(255), nullable=False, unique=True)
    password_hash = Column(String(255), nullable=False)
    role = Column(String(20), ForeignKey('enum_user_role.value'), nullable=False, index=True)
    first_name = Column(String(100))
    last_name = Column(String(100))
    slack_id = Column(String(100))
    preferred_language = Column(String(2), default='fr')
    is_active = Column(Boolean, default=True, index=True)
    last_login = Column(DateTime(timezone=True), nullable=True)
    failed_login_attempts = Column(Integer, default=0)
    last_password_change = Column(DateTime(timezone=True), nullable=True)
    deleted_at = Column(DateTime(timezone=True), nullable=True)

    # Relationships
    student = relationship("Student", back_populates="user", uselist=False)
    notifications = relationship(
        "Notification", 
        back_populates="user",
        order_by="desc(Notification.created_at)"
    )
    notification_preferences = relationship(
        "NotificationPreference", 
        back_populates="user",
        cascade="all, delete-orphan"
    )

    # Indexes
    __table_args__ = (
        Index('idx_user_role', 'role'),
        Index('idx_user_active', 'is_active'),
    )

    def format_datetime(self, dt: datetime) -> str:
        """Format datetime in French"""
        logger.debug(f"🕒 Formatage datetime pour {self.email}")
        if not dt:
            return "Jamais"
        return dt.strftime("%d/%m/%Y %H:%M")

    @property
    def full_name(self) -> str:
        """Returns the user's full name"""
        if not self.first_name and not self.last_name:
            return self.email
        return f"{self.first_name} {self.last_name}".strip()

    @property
    def role_display_name(self) -> str:
        """Get localized role name"""
        return UserRole(self.role).display_names[self.preferred_language]

    @property
    def role_emoji(self) -> str:
        """Get role emoji"""
        return UserRole(self.role).emoji
    
    @property
    def is_super_admin(self) -> bool:
        """Check if user is super admin"""
        return self.role == UserRole.SUPER_ADMIN.value
    
    @property
    def is_admin(self) -> bool:
        """Check if user is admin"""
        return self.role == UserRole.ADMIN.value
    
    @property
    def is_staff(self) -> bool:
        """Check if user is staff"""
        return self.role == UserRole.STAFF.value
    
    @property
    def is_student(self) -> bool:
        """Check if user is student"""
        return self.role == UserRole.STUDENT.value

    @property
    def account_status(self) -> str:
        """Get account status in preferred language"""
        logger.debug(f"🔍 Vérification statut pour {self.email}")
        
        if self.deleted_at:
            return "Supprimé" if self.preferred_language == 'fr' else "Deleted"
        if not self.is_active:
            return "Désactivé" if self.preferred_language == 'fr' else "Disabled"
        if self.failed_login_attempts >= 3:
            return "Bloqué" if self.preferred_language == 'fr' else "Locked"
        return "Actif" if self.preferred_language == 'fr' else "Active"

    @property
    def status_emoji(self) -> str:
        """Get status emoji"""
        if self.deleted_at:
            return "🗑️"
        if not self.is_active:
            return "⭕"
        if self.failed_login_attempts >= 3:
            return "🔒"
        return "✅"
    
    def get_notification_channels(self, notification_type: str) -> List[str]:
        """Get enabled notification channels for a specific type"""
        logger.debug(f"📢 Récupération canaux pour {notification_type}")
        
        pref = next(
            (p for p in self.notification_preferences 
            if p.notification_type == notification_type),
            None
        )
        
        channels = pref.enabled_channels if pref else ["in_app"]
        logger.info(f"📱 Canaux actifs: {channels}")
        return channels

    def validate(self) -> None:
        """Validate user data"""
        logger.debug(f"🔍 Validation données pour {self.email}")
        
        # Vérifier le rôle
        if not UserRole(self.role):
            logger.error(f"❌ Rôle invalide: {self.role}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_USER_ROLE,
                role=self.role
            )
            
        # Vérifier la langue
        if self.preferred_language not in ['fr', 'en']:
            logger.error(f"❌ Langue invalide: {self.preferred_language}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_LANGUAGE,
                language=self.preferred_language
            )

    def to_dict(self) -> Dict:
        """Convert to dictionary for API response"""
        logger.debug(f"🔄 Conversion en dict pour {self.email}")
        return {
            "id": str(self.id),
            "email": self.email,
            "role": self.role,
            "role_display_name": self.role_display_name,
            "role_emoji": self.role_emoji,
            "first_name": self.first_name,
            "last_name": self.last_name,
            "full_name": self.full_name,
            "slack_id": self.slack_id,
            "preferred_language": self.preferred_language,
            "is_active": self.is_active,
            "last_login": self.last_login.isoformat() if self.last_login else None,
            "status": self.account_status,
            "status_emoji": self.status_emoji
        }

    def to_summary_message(self) -> str:
        """Format user summary for notification"""
        logger.debug(f"📝 Génération résumé pour {self.email}")
        return f"""
{self.role_emoji} *{self.full_name}* ({self.role_display_name})
📧 Email : {self.email}
{self.status_emoji} Statut : {self.account_status}
🕒 Dernière connexion : {self.format_datetime(self.last_login)}
🔑 Dernier changement de mot de passe : {self.format_datetime(self.last_password_change)}
        """.strip()

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.role_emoji} {self.full_name} "
            f"({self.role_display_name}) {self.status_emoji}"
        )