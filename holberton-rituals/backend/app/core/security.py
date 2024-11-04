# core/security.py
from datetime import datetime, timedelta
from typing import Any, Union, Optional
from jose import jwt, JWTError
from passlib.context import CryptContext
from .config import settings
from fastapi.security import OAuth2PasswordBearer
from fastapi import HTTPException, status

# Configuration du hashing des mots de passe 🔐
pwd_context = CryptContext(
    schemes=["bcrypt"],
    deprecated="auto",
    bcrypt__rounds=12  # Plus c'est haut, plus c'est sécurisé (mais plus lent)
)

# Configuration OAuth2 pour FastAPI 🎟️
oauth2_scheme = OAuth2PasswordBearer(
    tokenUrl=f"{settings.API_V1_STR}/auth/login"
)

class SecurityManager:
    """QUEEN Security Manager ✨"""

    @staticmethod
    def verify_password(plain_password: str, hashed_password: str) -> bool:
        """Vérifie si le mot de passe correspond au hash"""
        return pwd_context.verify(plain_password, hashed_password)

    @staticmethod
    def get_password_hash(password: str) -> str:
        """Génère un hash pour le mot de passe"""
        return pwd_context.hash(password)

    @staticmethod
    def create_access_token(
        subject: Union[str, Any],
        expires_delta: Optional[timedelta] = None,
        scopes: list[str] = None
    ) -> str:
        """Crée un JWT token"""
        if expires_delta:
            expire = datetime.utcnow() + expires_delta
        else:
            expire = datetime.utcnow() + timedelta(
                minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES
            )
        
        to_encode = {
            "exp": expire,
            "sub": str(subject),
            "type": "access_token"
        }
        
        if scopes:
            to_encode["scopes"] = scopes

        return jwt.encode(
            to_encode,
            settings.JWT_SECRET_KEY,
            algorithm=settings.JWT_ALGORITHM
        )

    @staticmethod
    def decode_token(token: str) -> dict:
        """Décode et vérifie un JWT token"""
        try:
            payload = jwt.decode(
                token,
                settings.JWT_SECRET_KEY,
                algorithms=[settings.JWT_ALGORITHM]
            )
            return payload
        except JWTError as e:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Token invalide",
                headers={"WWW-Authenticate": "Bearer"},
            )

    @staticmethod
    def create_password_reset_token(email: str) -> str:
        """Crée un token de réinitialisation de mot de passe"""
        expire = datetime.utcnow() + timedelta(hours=24)
        to_encode = {
            "exp": expire,
            "sub": email,
            "type": "password_reset"
        }
        return jwt.encode(
            to_encode,
            settings.JWT_SECRET_KEY,
            algorithm=settings.JWT_ALGORITHM
        )

    @staticmethod
    def verify_password_reset_token(token: str) -> Optional[str]:
        """Vérifie un token de réinitialisation de mot de passe"""
        try:
            payload = jwt.decode(
                token,
                settings.JWT_SECRET_KEY,
                algorithms=[settings.JWT_ALGORITHM]
            )
            if payload["type"] != "password_reset":
                return None
            return payload["sub"]
        except JWTError:
            return None

    @staticmethod
    def is_password_strong(password: str) -> tuple[bool, str]:
        """Vérifie si le mot de passe est assez fort"""
        min_length = 8
        errors = []

        if len(password) < min_length:
            errors.append(f"Le mot de passe doit faire au moins {min_length} caractères")
        if not any(c.isupper() for c in password):
            errors.append("Le mot de passe doit contenir au moins une majuscule")
        if not any(c.islower() for c in password):
            errors.append("Le mot de passe doit contenir au moins une minuscule")
        if not any(c.isdigit() for c in password):
            errors.append("Le mot de passe doit contenir au moins un chiffre")
        if not any(c in "!@#$%^&*" for c in password):
            errors.append("Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*)")

        return (len(errors) == 0, "\n".join(errors))

# Instance globale pour un accès facile
security = SecurityManager()