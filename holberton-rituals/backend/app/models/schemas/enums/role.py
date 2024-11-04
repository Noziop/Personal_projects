# app/models/schemas/enums/role.py
from typing import Dict, List, Set
from pydantic import Field
from ..common.base import BaseSchema, I18nSchema

class RoleSchema(BaseSchema):
    """Schema for user role enumeration ✨"""
    
    value: str = Field(
        ...,
        pattern="^(SUPER_ADMIN|ADMIN|STAFF|STUDENT)$",
        description="Identifiant unique du rôle"
    )
    display_names: I18nSchema = Field(
        ...,
        description="Noms d'affichage"
    )
    descriptions: I18nSchema = Field(
        ...,
        description="Descriptions détaillées"
    )
    permissions: Set[str] = Field(
        ...,
        description="Permissions associées"
    )
    can_manage_roles: Set[str] = Field(
        default_factory=set,
        description="Rôles pouvant être gérés"
    )
    ritual_permissions: Dict[str, List[str]] = Field(
        default_factory=dict,
        description="Permissions par type de rituel"
    )
    dashboard_access: List[str] = Field(
        default_factory=list,
        description="Sections du tableau de bord accessibles"
    )
    emoji: str = Field(
        ...,
        description="Emoji représentatif"
    )
    display_order: int = Field(
        ...,
        ge=0,
        description="Ordre d'affichage"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "value": "STAFF",
                "display_names": {
                    "fr": "Staff",
                    "en": "Staff"
                },
                "descriptions": {
                    "fr": "Membre de l'équipe pédagogique",
                    "en": "Educational team member"
                },
                "permissions": {
                    "manage_students",
                    "validate_absences",
                    "view_reports"
                },
                "can_manage_roles": {"STUDENT"},
                "ritual_permissions": {
                    "SOD": ["validate", "evaluate", "reschedule"],
                    "STANDUP": ["validate", "moderate", "reschedule"]
                },
                "dashboard_access": [
                    "students",
                    "rituals",
                    "reports",
                    "analytics"
                ],
                "emoji": "👩‍🏫",
                "display_order": 2
            }
        }

class RoleHierarchySchema(BaseSchema):
    """Schema for role hierarchy and inheritance"""
    
    role: str = Field(
        ...,
        description="Rôle"
    )
    inherits_from: List[str] = Field(
        default_factory=list,
        description="Hérite des rôles"
    )
    level: int = Field(
        ...,
        ge=0,
        description="Niveau hiérarchique"
    )
    can_be_assigned_by: List[str] = Field(
        ...,
        description="Rôles pouvant assigner ce rôle"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "role": "STAFF",
                "inherits_from": ["STUDENT"],
                "level": 2,
                "can_be_assigned_by": ["SUPER_ADMIN", "ADMIN"]
            }
        }

class RoleTransitionSchema(BaseSchema):
    """Schema for allowed role transitions"""
    
    from_role: str = Field(
        ...,
        description="Rôle de départ"
    )
    to_role: str = Field(
        ...,
        description="Rôle d'arrivée"
    )
    requires_approval_from: List[str] = Field(
        ...,
        description="Approbation requise des rôles"
    )
    conditions: Dict[str, bool] = Field(
        default_factory=dict,
        description="Conditions de transition"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "from_role": "STUDENT",
                "to_role": "STAFF",
                "requires_approval_from": ["ADMIN"],
                "conditions": {
                    "has_completed_training": True,
                    "minimum_time_as_student": True
                }
            }
        }

class RoleSummarySchema(BaseSchema):
    """Schema for role usage statistics"""
    
    role: str = Field(
        ...,
        description="Rôle"
    )
    user_count: int = Field(
        0,
        description="Nombre d'utilisateurs"
    )
    active_count: int = Field(
        0,
        description="Utilisateurs actifs"
    )
    permissions_used: Dict[str, int] = Field(
        default_factory=dict,
        description="Utilisation des permissions"
    )
    most_active_users: List[Dict] = Field(
        default_factory=list,
        max_items=5,
        description="Top 5 utilisateurs les plus actifs"
    )

    class Config:
        json_schema_extra = {
            "example": {
                "role": "STAFF",
                "user_count": 10,
                "active_count": 8,
                "permissions_used": {
                    "validate_absences": 45,
                    "evaluate_sod": 30
                },
                "most_active_users": [
                    {
                        "id": "123e4567-e89b-12d3-a456-426614174000",
                        "name": "Grace Hopper",
                        "actions_count": 150
                    }
                ]
            }
        }