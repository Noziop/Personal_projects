# core/config.py
from typing import List, Dict, Optional
from pydantic_settings import BaseSettings, SettingsConfigDict
from pydantic import AnyHttpUrl, validator, Field, ValidationError
from pathlib import Path
import secrets
from enum import Enum
from functools import lru_cache
import os

class LogLevel(str, Enum):
    DEBUG = "DEBUG"
    INFO = "INFO"
    WARNING = "WARNING"
    ERROR = "ERROR"
    CRITICAL = "CRITICAL"

class Settings(BaseSettings):
    """ULTRA-QUEEN Application Settings ✨"""
    
    # Project Configuration 🏗️
    PROJECT_NAME: str = Field(
        default="Holberton Rituals",
        description="Nom du projet"
    )
    VERSION: str = Field(
        default="1.0.0",
        description="Version de l'application"
    )
    API_V1_STR: str = Field(
        default="/api/v1",
        description="Préfixe de l'API"
    )
    DEBUG: bool = Field(
        default=False,
        description="Mode debug"
    )
    
    # Security Configuration 🔐
    SECRET_KEY: str = Field(
        default_factory=lambda: secrets.token_urlsafe(32),
        description="Clé secrète pour l'application"
    )
    JWT_SECRET_KEY: str = Field(
        ...,  # Required
        description="Clé secrète pour les JWT"
    )
    JWT_ALGORITHM: str = Field(
        default="HS256",
        description="Algorithme de signature JWT"
    )
    ACCESS_TOKEN_EXPIRE_MINUTES: int = Field(
        default=60 * 24,
        description="Durée de validité du token en minutes"
    )
    
    # CORS Configuration 🌐
    BACKEND_CORS_ORIGINS: List[AnyHttpUrl] = Field(
        default=[],
        description="Liste des origines autorisées pour CORS"
    )

    @validator("BACKEND_CORS_ORIGINS", pre=True)
    def assemble_cors_origins(cls, v: str | List[str]) -> List[AnyHttpUrl]:
        if isinstance(v, str) and not v.startswith("["):
            return [i.strip() for i in v.split(",")]
        elif isinstance(v, (list, str)):
            return v
        raise ValueError(v)

    # Database Configuration 🗄️
    DB_USER: str = Field(..., description="Utilisateur de la base de données")
    DB_PASSWORD: str = Field(..., description="Mot de passe de la base de données")
    DB_NAME: str = Field(..., description="Nom de la base de données")
    DATABASE_URL: Optional[str] = Field(
        None,
        description="URL complète de la base de données"
    )

    @validator("DATABASE_URL", pre=True)
    def validate_database_url(cls, v: Optional[str], values: Dict) -> str:
        """Validate and construct database URL"""
        if v:
            return v
        required_vars = ["DB_USER", "DB_PASSWORD", "DB_NAME"]
        missing = [var for var in required_vars if var not in values]
        if missing:
            raise ValueError(f"Missing required environment variables: {', '.join(missing)}")
        return f"mysql+pymysql://{values['DB_USER']}:{values['DB_PASSWORD']}@db:3306/{values['DB_NAME']}"

    # Email Configuration 📧
    SMTP_TLS: bool = Field(default=True, description="Utiliser TLS pour SMTP")
    SMTP_HOST: Optional[str] = Field(None, description="Hôte SMTP")
    SMTP_PORT: Optional[int] = Field(None, description="Port SMTP")
    SMTP_USER: Optional[str] = Field(None, description="Utilisateur SMTP")
    SMTP_PASSWORD: Optional[str] = Field(None, description="Mot de passe SMTP")
    EMAILS_FROM_EMAIL: Optional[str] = Field(None, description="Email d'envoi")
    EMAILS_FROM_NAME: Optional[str] = Field(None, description="Nom d'envoi")

    @validator("SMTP_PORT")
    def validate_smtp_port(cls, v: Optional[int]) -> Optional[int]:
        if v is not None and not (0 <= v <= 65535):
            raise ValueError("SMTP port must be between 0 and 65535")
        return v

    # Slack Configuration 💬
    SLACK_BOT_TOKEN: Optional[str] = Field(
        None,
        description="Token du bot Slack"
    )
    SLACK_SIGNING_SECRET: Optional[str] = Field(
        None,
        description="Secret de signature Slack"
    )
    
    # Logging Configuration 📝
    LOG_LEVEL: LogLevel = Field(
        default=LogLevel.INFO,
        description="Niveau de logging"
    )
    LOG_PATHS: Dict[str, Path] = Field(
        default={
            "main": Path("logs/app.log"),
            "error": Path("logs/error.log"),
            "access": Path("logs/access.log"),
            "sql": Path("logs/sql.log"),
            "security": Path("logs/security.log"),
            "rituals": Path("logs/rituals.log")
        },
        description="Chemins des fichiers de logs"
    )
    LOG_FORMAT: str = Field(
        default=(
            "<green>{time:YYYY-MM-DD HH:mm:ss.SSS}</green> | "
            "<level>{level: <8}</level> | "
            "<cyan>{name}</cyan>:<cyan>{function}</cyan>:<cyan>{line}</cyan> - "
            "<level>{message}</level>"
        ),
        description="Format des logs"
    )
    LOG_ROTATION: str = Field(
        default="500 MB",
        description="Taille de rotation des logs"
    )
    LOG_RETENTION: str = Field(
        default="1 month",
        description="Durée de rétention des logs"
    )
    LOG_COMPRESSION: str = Field(
        default="zip",
        description="Format de compression des logs"
    )

    # Model Configuration ⚙️
    model_config = SettingsConfigDict(
        env_file=[".env", "../.env"],  # Cherche dans le dossier courant ET parent
        env_file_encoding="utf-8",
        case_sensitive=True
    )

    # Validators and Helpers 🔍
    @validator("LOG_PATHS")
    def create_log_directories(cls, v: Dict[str, Path]) -> Dict[str, Path]:
        for path in v.values():
            path.parent.mkdir(parents=True, exist_ok=True)
        return v

    def check_email_config(self) -> bool:
        """Vérifie si la configuration email est complète"""
        return all([
            self.SMTP_HOST,
            self.SMTP_PORT,
            self.SMTP_USER,
            self.SMTP_PASSWORD,
            self.EMAILS_FROM_EMAIL
        ])

    def check_slack_config(self) -> bool:
        """Vérifie si la configuration Slack est complète"""
        return all([
            self.SLACK_BOT_TOKEN,
            self.SLACK_SIGNING_SECRET
        ])

@lru_cache
def get_settings() -> Settings:
    """Get cached settings"""
    try:
        return Settings()
    except ValidationError as e:
        print("❌ Erreur de configuration :")
        for error in e.errors():
            print(f"- {error['loc'][0]}: {error['msg']}")
        raise

# Instance globale des settings
settings = get_settings()