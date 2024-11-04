# app/models/schemas/users/user.py
from datetime import datetime
from typing import Optional, List
from pydantic import EmailStr, Field, field_validator, UUID4
from app.core.security import validate_password
from app.core.config import settings
from ..common.base import BaseSchema, ResponseSchema, PaginatedResponse
from ...domain.enums.user_role import UserRole

class UserBase(BaseSchema):
    """Base schema for user data ✨"""
    
    email: EmailStr = Field(..., description="Adresse email")
    first_name: str = Field(
        ..., 
        min_length=2, 
        max_length=100,
        description="Prénom"
    )
    last_name: str = Field(
        ..., 
        min_length=2, 
        max_length=100,
        description="Nom"
    )
    slack_id: Optional[str] = Field(
        None, 
        max_length=100,
        description="Identifiant Slack"
    )
    preferred_language: str = Field(
        default='fr',
        pattern='^(fr|en)$',
        description="Langue préférée"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "email": "queen@holberton.fr",
                "first_name": "Grace",
                "last_name": "Hopper",
                "slack_id": "U123456789",
                "preferred_language": "fr"
            }
        }

class UserCreate(UserBase):
    """Schema for user creation"""
    
    password: str = Field(
        ..., 
        min_length=8,
        description="Mot de passe"
    )
    role: UserRole = Field(
        default=UserRole.STUDENT,
        description="Rôle utilisateur"
    )

    @field_validator('password')
    def validate_password_strength(cls, v: str) -> str:
        """Validate password complexity"""
        if not validate_password(v):
            raise ValueError(
                "Le mot de passe doit contenir au moins :\n"
                "- 8 caractères\n"
                "- Une majuscule\n"
                "- Une minuscule\n"
                "- Un chiffre\n"
                "- Un caractère spécial (!@#$%^&*)"
            )
        return v

    @field_validator('role')
    def validate_role_creation(cls, v: UserRole) -> UserRole:
        """Validate role creation permissions"""
        if v == UserRole.SUPER_ADMIN:
            raise ValueError(
                "La création d'un SUPER_ADMIN n'est pas autorisée"
            )
        return v

    class Config:
        json_schema_extra = {
            "example": {
                "email": "new.queen@holberton.fr",
                "first_name": "Ada",
                "last_name": "Lovelace",
                "password": "SuperSecretPassword123!",
                "role": "STUDENT",
                "preferred_language": "fr"
            }
        }

class UserUpdate(BaseSchema):
    """Schema for user updates"""
    
    first_name: Optional[str] = Field(
        None, 
        min_length=2, 
        max_length=100
    )
    last_name: Optional[str] = Field(
        None, 
        min_length=2, 
        max_length=100
    )
    slack_id: Optional[str] = Field(
        None, 
        max_length=100
    )
    is_active: Optional[bool] = Field(
        None,
        description="Statut d'activation"
    )
    preferred_language: Optional[str] = Field(
        None,
        pattern='^(fr|en)$'
    )

    class Config:
        json_schema_extra = {
            "example": {
                "first_name": "Margaret",
                "last_name": "Hamilton",
                "slack_id": "U987654321",
                "preferred_language": "en"
            }
        }

class UserResponse(ResponseSchema, UserBase):
    """Schema for user responses"""
    
    id: UUID4
    role: UserRole
    is_active: bool
    last_login: Optional[datetime] = None
    failed_login_attempts: int = Field(
        default=0,
        description="Nombre de tentatives échouées"
    )
    status_emoji: str = Field(
        description="Emoji représentant le statut"
    )
    role_emoji: str = Field(
        description="Emoji représentant le rôle"
    )
    
    @property
    def full_name(self) -> str:
        """Get user's full name"""
        return f"{self.first_name} {self.last_name}"

    @property
    def is_locked(self) -> bool:
        """Check if account is locked"""
        return self.failed_login_attempts >= settings.MAX_LOGIN_ATTEMPTS

    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "email": "queen@holberton.fr",
                "first_name": "Grace",
                "last_name": "Hopper",
                "role": "STUDENT",
                "slack_id": "U123456789",
                "preferred_language": "fr",
                "is_active": True,
                "last_login": "2024-02-11T14:30:00Z",
                "failed_login_attempts": 0,
                "status_emoji": "✅",
                "role_emoji": "👩‍💻",
                "created_at": "2024-02-01T12:00:00Z",
                "updated_at": "2024-02-11T14:30:00Z"
            }
        }

class UserList(PaginatedResponse[UserResponse]):
    """Schema for user list responses"""
    
    class Config:
        json_schema_extra = {
            "example": {
                "total": 2,
                "page": 1,
                "size": 10,
                "pages": 1,
                "has_next": False,
                "has_prev": False,
                "items": [
                    {
                        "id": "123e4567-e89b-12d3-a456-426614174000",
                        "email": "grace@holberton.fr",
                        "first_name": "Grace",
                        "last_name": "Hopper",
                        "role": "STUDENT"
                    },
                    {
                        "id": "123e4567-e89b-12d3-a456-426614174001",
                        "email": "ada@holberton.fr",
                        "first_name": "Ada",
                        "last_name": "Lovelace",
                        "role": "STUDENT"
                    }
                ]
            }
        }