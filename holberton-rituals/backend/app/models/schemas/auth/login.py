# app/models/schemas/auth/login.py
from typing import Optional
from pydantic import EmailStr, Field, field_validator
from app.core.config import settings
from app.core.security import validate_password
from ..common.base import BaseSchema, ErrorResponse
from .token import Token
from ..users.user import UserResponse

class LoginRequest(BaseSchema):
    """Schema for login requests ✨"""
    
    email: EmailStr = Field(..., description="Adresse email")
    password: str = Field(
        ..., 
        min_length=8,
        description="Mot de passe",
        examples=["SuperSecretPassword123!"]
    )

    class Config:
        json_schema_extra = {
            "example": {
                "email": "queen@holberton.fr",
                "password": "SuperSecretPassword123!"
            }
        }

class LoginResponse(BaseSchema):
    """Schema for login responses"""
    
    token: Token
    user: UserResponse
    last_login: Optional[str] = Field(None, description="Dernière connexion")

    class Config:
        json_schema_extra = {
            "example": {
                "token": {
                    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
                    "token_type": "bearer",
                    "expires_in": settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60
                },
                "user": {
                    "id": "123e4567-e89b-12d3-a456-426614174000",
                    "email": "queen@holberton.fr",
                    "role": "STUDENT",
                    "full_name": "Queen Holberton"
                },
                "last_login": "2024-02-11T12:00:00+00:00"
            }
        }

class LoginError(ErrorResponse):
    """Schema for login errors"""
    
    attempts_remaining: Optional[int] = Field(
        None, 
        description="Tentatives restantes avant blocage"
    )
    locked_until: Optional[str] = Field(
        None,
        description="Date de déblocage du compte"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "error_code": "INVALID_CREDENTIALS",
                "message": "Identifiants invalides",
                "attempts_remaining": 2,
                "locked_until": "2024-02-11T13:00:00+00:00"
            }
        }

class PasswordResetRequest(BaseSchema):
    """Schema for password reset requests"""
    
    email: EmailStr = Field(..., description="Adresse email")

    class Config:
        json_schema_extra = {
            "example": {
                "email": "queen@holberton.fr"
            }
        }

class PasswordResetConfirm(BaseSchema):
    """Schema for password reset confirmation"""
    
    token: str = Field(..., description="Token de réinitialisation")
    new_password: str = Field(
        ..., 
        min_length=8,
        description="Nouveau mot de passe",
        examples=["NewSuperSecretPassword123!"]
    )
    confirm_password: str = Field(
        ..., 
        min_length=8,
        description="Confirmation du nouveau mot de passe"
    )

    @field_validator('new_password')
    def validate_password_strength(cls, v: str) -> str:
        """Validate password strength"""
        if not validate_password(v):
            raise ValueError(
                "Le mot de passe doit contenir au moins 8 caractères, "
                "une majuscule, une minuscule, un chiffre et un caractère spécial"
            )
        return v

    @field_validator('confirm_password')
    def passwords_match(cls, v: str, values: dict) -> str:
        """Validate password confirmation"""
        if 'new_password' in values.data and v != values.data['new_password']:
            raise ValueError("Les mots de passe ne correspondent pas")
        return v

    class Config:
        json_schema_extra = {
            "example": {
                "token": "reset-token-123",
                "new_password": "NewSuperSecretPassword123!",
                "confirm_password": "NewSuperSecretPassword123!"
            }
        }

class PasswordChangeRequest(BaseSchema):
    """Schema for password change requests"""
    
    current_password: str = Field(..., description="Mot de passe actuel")
    new_password: str = Field(
        ..., 
        min_length=8,
        description="Nouveau mot de passe"
    )
    confirm_password: str = Field(
        ..., 
        min_length=8,
        description="Confirmation du nouveau mot de passe"
    )

    @field_validator('new_password')
    def validate_password_strength(cls, v: str) -> str:
        """Validate password strength"""
        if not validate_password(v):
            raise ValueError(
                "Le mot de passe doit contenir au moins 8 caractères, "
                "une majuscule, une minuscule, un chiffre et un caractère spécial"
            )
        return v

    @field_validator('confirm_password')
    def passwords_match(cls, v: str, values: dict) -> str:
        """Validate password confirmation"""
        if 'new_password' in values.data and v != values.data['new_password']:
            raise ValueError("Les mots de passe ne correspondent pas")
        return v

    class Config:
        json_schema_extra = {
            "example": {
                "current_password": "OldSuperSecretPassword123!",
                "new_password": "NewSuperSecretPassword123!",
                "confirm_password": "NewSuperSecretPassword123!"
            }
        }