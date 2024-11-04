# models/domain/base.py
from typing import Any, Dict
from datetime import datetime, timezone
from sqlalchemy.ext.declarative import declared_attr
from sqlalchemy.orm import DeclarativeBase, Session
from sqlalchemy import Column, DateTime, Boolean, event
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class Base(DeclarativeBase):
    """Base class for all database models ✨"""
    
    # Auto-génère le nom de table à partir du nom de classe
    @declared_attr
    def __tablename__(cls) -> str:
        return cls.__name__.lower()
    
    # Colonnes communes à tous les modèles
    created_at = Column(
        DateTime(timezone=True), 
        default=lambda: datetime.now(timezone.utc),
        nullable=False
    )
    updated_at = Column(
        DateTime(timezone=True), 
        default=lambda: datetime.now(timezone.utc),
        onupdate=lambda: datetime.now(timezone.utc),
        nullable=False
    )
    is_active = Column(Boolean, default=True, nullable=False)

    def dict(self) -> Dict[str, Any]:
        """Convert model to dictionary"""
        try:
            logger.debug(f"🔄 Conversion en dict de {self.__class__.__name__}")
            return {
                column.name: getattr(self, column.name)
                for column in self.__table__.columns
            }
        except Exception as e:
            logger.error(f"❌ Erreur lors de la conversion en dict: {str(e)}")
            raise ValidationException(
                error_code=ErrorCode.SERIALIZATION_ERROR,
                model=self.__class__.__name__
            )

    def validate(self) -> None:
        """Validation personnalisée du modèle"""
        pass  # À surcharger dans les classes filles

    @classmethod
    def get_by_id(cls, db: Session, id: Any) -> Any:
        """Récupère une instance par son ID"""
        logger.debug(f"🔍 Recherche {cls.__name__} avec ID {id}")
        instance = db.query(cls).filter(cls.id == id, cls.is_active == True).first()
        
        if not instance:
            logger.warning(f"❌ {cls.__name__} avec ID {id} non trouvé")
            raise ValidationException(
                error_code=ErrorCode.RESOURCE_NOT_FOUND,
                resource_type=cls.__name__,
                resource_id=id
            )
            
        return instance

    def soft_delete(self, db: Session) -> None:
        """Soft delete de l'instance"""
        logger.info(f"🗑️ Soft delete de {self.__class__.__name__} (ID: {self.id})")
        self.is_active = False
        db.add(self)

    def restore(self, db: Session) -> None:
        """Restaure une instance soft-deleted"""
        logger.info(f"✨ Restauration de {self.__class__.__name__} (ID: {self.id})")
        self.is_active = True
        db.add(self)

# Event listeners pour le logging
@event.listens_for(Base, 'after_insert', propagate=True)
def after_insert(mapper, connection, target):
    """Log après insertion"""
    logger.info(f"✨ Création de {target.__class__.__name__} (ID: {target.id})")

@event.listens_for(Base, 'after_update', propagate=True)
def after_update(mapper, connection, target):
    """Log après mise à jour"""
    logger.info(f"📝 Mise à jour de {target.__class__.__name__} (ID: {target.id})")

@event.listens_for(Base, 'after_delete', propagate=True)
def after_delete(mapper, connection, target):
    """Log après suppression"""
    logger.info(f"❌ Suppression de {target.__class__.__name__} (ID: {target.id})")