# core/database.py
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
from sqlalchemy.pool import QueuePool
from contextlib import contextmanager
from typing import Generator
import logging

from .config import settings

# Configuration du logging 📝
logger = logging.getLogger(__name__)

# Configuration de l'engine SQLAlchemy ⚙️
engine = create_engine(
    settings.get_database_url(),
    poolclass=QueuePool,
    pool_size=5,  # Nombre de connexions dans le pool
    max_overflow=10,  # Connexions supplémentaires si pool plein
    pool_timeout=30,  # Timeout en secondes
    pool_pre_ping=True,  # Vérifie la connexion avant utilisation
    echo=settings.DEBUG,  # SQL logging en mode debug
    echo_pool=settings.DEBUG  # Pool logging en mode debug
)

# Configuration de la session SQLAlchemy 🔧
SessionLocal = sessionmaker(
    autocommit=False,
    autoflush=False,
    bind=engine
)

# Base pour les modèles SQLAlchemy 📚
Base = declarative_base()

@contextmanager
def get_db() -> Generator:
    """Context manager pour obtenir une session DB"""
    db = SessionLocal()
    try:
        logger.debug("🔗 Nouvelle session DB créée")
        yield db
        db.commit()
        logger.debug("✅ Session DB commit et fermée")
    except Exception as e:
        db.rollback()
        logger.error(f"❌ Erreur DB: {str(e)}")
        raise
    finally:
        db.close()

class DatabaseManager:
    """Gestionnaire de base de données QUEEN 👑"""

    @staticmethod
    async def check_connection() -> bool:
        """Vérifie la connexion à la base de données"""
        try:
            with get_db() as db:
                db.execute("SELECT 1")
            return True
        except Exception as e:
            logger.error(f"❌ Erreur de connexion DB: {str(e)}")
            return False

    @staticmethod
    async def get_db_info() -> dict:
        """Obtient des informations sur la base de données"""
        try:
            with get_db() as db:
                return {
                    "status": "connected",
                    "pool_size": engine.pool.size(),
                    "connections_in_use": engine.pool.checkedin(),
                    "overflow_connections": engine.pool.overflow()
                }
        except Exception as e:
            logger.error(f"❌ Erreur lors de la récupération des infos DB: {str(e)}")
            return {
                "status": "error",
                "error": str(e)
            }

    @staticmethod
    @contextmanager
    def transaction():
        """Context manager pour les transactions"""
        with get_db() as db:
            try:
                yield db
                db.commit()
                logger.debug("✅ Transaction commit avec succès")
            except Exception as e:
                db.rollback()
                logger.error(f"❌ Transaction rollback: {str(e)}")
                raise

# Utilisation :
"""
# Dans un service
async def create_user(user_data: UserCreate):
    with DatabaseManager.transaction() as db:
        user = User(**user_data.dict())
        db.add(user)
        return user

# Vérification de la santé DB
async def check_db_health():
    is_connected = await DatabaseManager.check_connection()
    if not is_connected:
        raise DatabaseException("La base de données est inaccessible")
    
    db_info = await DatabaseManager.get_db_info()
    return db_info
"""