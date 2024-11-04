# app/models/schemas/auth/token.py
from typing import Optional
from datetime import datetime
from pydantic import Field, UUID4
from app.core.config import settings
from ..common.base import BaseSchema

class Token(BaseSchema):
    """Schema for authentication tokens ✨"""
    
    access_token: str = Field(
        ..., 
        description="Token d'accès JWT"
    )
    token_type: str = Field(
        "bearer",
        description="Type de token"
    )
    expires_in: int = Field(
        default=settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        description="Durée de validité en secondes"
    )
    refresh_token: Optional[str] = Field(
        None,
        description="Token de rafraîchissement"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
                "token_type": "bearer",
                "expires_in": 3600,
                "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
            }
        }

class TokenPayload(BaseSchema):
    """Schema for JWT token payload"""
    
    sub: UUID4 = Field(
        ..., 
        description="ID de l'utilisateur"
    )
    role: str = Field(
        ...,
        description="Rôle de l'utilisateur"
    )
    exp: int = Field(
        ...,
        description="Timestamp d'expiration"
    )
    iat: int = Field(
        ...,
        description="Timestamp de création"
    )
    fresh: bool = Field(
        default=True,
        description="Token fraîchement créé"
    )
    device_id: Optional[str] = Field(
        None,
        description="Identifiant du dispositif"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "sub": "123e4567-e89b-12d3-a456-426614174000",
                "role": "STUDENT",
                "exp": 1707667200,  # 2024-02-11 14:00:00
                "iat": 1707663600,  # 2024-02-11 13:00:00
                "fresh": True,
                "device_id": "web-chrome-mac"
            }
        }

class TokenRefreshRequest(BaseSchema):
    """Schema for token refresh requests"""
    
    refresh_token: str = Field(
        ...,
        description="Token de rafraîchissement"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
            }
        }

class TokenBlacklist(BaseSchema):
    """Schema for blacklisted tokens"""
    
    token: str = Field(
        ...,
        description="Token révoqué"
    )
    revoked_at: datetime = Field(
        ...,
        description="Date de révocation"
    )
    reason: Optional[str] = Field(
        None,
        description="Raison de la révocation"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
                "revoked_at": "2024-02-11T13:00:00+00:00",
                "reason": "Déconnexion utilisateur"
            }
        }