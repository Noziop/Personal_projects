# app/models/schemas/common/base.py
from datetime import datetime
from typing import Optional, Generic, TypeVar, List, Dict, Any
from pydantic import BaseModel, ConfigDict, Field, UUID4
from pydantic.generics import GenericModel

T = TypeVar('T')

class BaseSchema(BaseModel):
    """Base schema with common configurations ✨"""
    
    model_config = ConfigDict(
        from_attributes=True,  # Pour la conversion SQLAlchemy -> Pydantic
        json_encoders={
            datetime: lambda dt: dt.isoformat(),
            UUID4: str
        },
        arbitrary_types_allowed=True
    )

class TimeStampedSchema(BaseSchema):
    """Base schema with timestamp fields"""
    
    created_at: datetime = Field(..., description="Date de création")
    updated_at: Optional[datetime] = Field(None, description="Date de dernière modification")
    is_active: bool = Field(True, description="Statut d'activation")

class IDSchema(BaseSchema):
    """Base schema with ID field"""
    
    id: UUID4 = Field(..., description="Identifiant unique")

class ResponseSchema(TimeStampedSchema, IDSchema):
    """Base schema for API responses"""
    
    class Config:
        json_schema_extra = {
            "example": {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "created_at": "2024-02-11T12:00:00+00:00",
                "updated_at": "2024-02-11T12:00:00+00:00",
                "is_active": True
            }
        }

class PaginatedResponse(GenericModel, Generic[T]):
    """Base schema for paginated responses"""
    
    total: int = Field(..., description="Nombre total d'éléments")
    page: int = Field(..., ge=1, description="Page courante")
    size: int = Field(..., ge=1, le=100, description="Taille de la page")
    pages: int = Field(..., description="Nombre total de pages")
    has_next: bool = Field(..., description="Page suivante disponible")
    has_prev: bool = Field(..., description="Page précédente disponible")
    items: List[T] = Field(..., description="Liste des éléments")

    class Config:
        json_schema_extra = {
            "example": {
                "total": 100,
                "page": 1,
                "size": 10,
                "pages": 10,
                "has_next": True,
                "has_prev": False,
                "items": []
            }
        }

class ErrorResponse(BaseSchema):
    """Base schema for error responses"""
    
    error_code: str = Field(..., description="Code d'erreur")
    message: str = Field(..., description="Message d'erreur")
    details: Optional[Dict[str, Any]] = Field(None, description="Détails supplémentaires")

    class Config:
        json_schema_extra = {
            "example": {
                "error_code": "VALIDATION_ERROR",
                "message": "Erreur de validation des données",
                "details": {
                    "field": "email",
                    "error": "Format d'email invalide"
                }
            }
        }

class I18nSchema(BaseSchema):
    """Base schema for internationalized fields"""
    
    fr: str = Field(..., description="Version française")
    en: str = Field(..., description="Version anglaise")

    class Config:
        json_schema_extra = {
            "example": {
                "fr": "Bonjour le monde",
                "en": "Hello world"
            }
        }