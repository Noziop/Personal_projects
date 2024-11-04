# app/models/domain/enums/user_role.py
from enum import Enum
from typing import Dict, Optional, Set
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode

logger = settings.logger  # Notre super logger loguru ! 👑

class UserRole(str, Enum):
    """User roles in the system ✨"""
    
    SUPER_ADMIN = "SUPER_ADMIN"  # 👑 Déesse du système
    ADMIN = "ADMIN"              # 👑 Admin puissant
    STAFF = "STAFF"              # 🎓 Staff
    STUDENT = "STUDENT"          # 🌟 Étudiant

    @property
    def display_names(self) -> Dict[str, str]:
        """Get display names in both languages"""
        return {
            self.SUPER_ADMIN: {
                "fr": "Super Administrateur",
                "en": "Super Administrator"
            },
            self.ADMIN: {
                "fr": "Administrateur",
                "en": "Administrator"
            },
            self.STAFF: {
                "fr": "Staff",
                "en": "Staff"
            },
            self.STUDENT: {
                "fr": "Étudiant",
                "en": "Student"
            }
        }[self]

    @property
    def descriptions(self) -> Dict[str, str]:
        """Get descriptions in both languages"""
        return {
            self.SUPER_ADMIN: {
                "fr": "Contrôle total du système avec droits exclusifs",
                "en": "Full system control with exclusive rights"
            },
            self.ADMIN: {
                "fr": "Administration système avec restrictions",
                "en": "System administration with restrictions"
            },
            self.STAFF: {
                "fr": "Staff Holberton - Gestion quotidienne",
                "en": "Holberton Staff - Daily management"
            },
            self.STUDENT: {
                "fr": "Étudiant en formation",
                "en": "Student in training"
            }
        }[self]

    @property
    def display_order(self) -> int:
        """Get display order for UI"""
        return {
            self.SUPER_ADMIN: 0,  # Toujours en premier
            self.ADMIN: 1,
            self.STAFF: 2,
            self.STUDENT: 3
        }[self]

    @property
    def emoji(self) -> str:
        """Get emoji representation"""
        return {
            self.SUPER_ADMIN: "👑",  # Crown for super admin
            self.ADMIN: "⚜️",        # Fleur de lys for admin
            self.STAFF: "🎓",        # Graduation cap for staff
            self.STUDENT: "🌟"       # Star for student
        }[self]

    @property
    def permissions(self) -> Set[str]:
        """Get role permissions"""
        base_permissions = {"view_own_profile", "edit_own_profile"}
        
        role_permissions = {
            self.SUPER_ADMIN: {
                "manage_all",              # Tout gérer
                "create_super_admin",      # Créer d'autres super admins
                "manage_system_critical",  # Gérer configs critiques
                "lock_system",            # Verrouiller le système
                "view_all",               # Tout voir
                "edit_all"                # Tout modifier
            },
            self.ADMIN: {
                "manage_users",           # Sauf SUPER_ADMIN
                "manage_roles",           # Sauf SUPER_ADMIN
                "manage_cohorts",
                "validate_unavailability",
                "view_statistics",
                "manage_rituals",
                "manage_system",          # Sauf configs critiques
                "view_all_profiles",
                "edit_all_profiles"       # Sauf SUPER_ADMIN
            },
            self.STAFF: {
                "manage_students",        # CRUD complet sur étudiants
                "manage_all_cohorts",     # Gestion de toutes les cohortes
                "validate_unavailability",
                "view_all_statistics",
                "manage_rituals",
                "view_all_profiles",      # Voir tous les profils
                "provide_feedback"
            },
            self.STUDENT: {
                "participate_rituals",
                "submit_unavailability",
                "view_own_statistics",
                "view_cohort_standup_statistics",
                "view_cohort_info",
                "view_cohort_profiles"    # Voir profils de sa cohorte
            }
        }

        return base_permissions.union(role_permissions[self])

    @property
    def ritual_permissions(self) -> Dict[str, list]:
        """Get ritual-specific permissions"""
        return {
            self.SUPER_ADMIN: {
                "sod": ["present", "evaluate", "manage", "configure"],
                "standup": ["participate", "lead", "manage", "configure"]
            },
            self.ADMIN: {
                "sod": ["present", "evaluate", "manage"],
                "standup": ["participate", "lead", "manage"]
            },
            self.STAFF: {
                "sod": ["evaluate", "manage"],
                "standup": ["participate", "manage"]
            },
            self.STUDENT: {
                "sod": ["present", "evaluate"],
                "standup": ["participate", "lead"]
            }
        }[self]

    @property
    def can_be_revoked_by(self) -> Set["UserRole"]:
        """Get roles that can revoke this role"""
        return {
            self.SUPER_ADMIN: set(),  # Personne ne peut révoquer
            self.ADMIN: {self.SUPER_ADMIN},
            self.STAFF: {self.SUPER_ADMIN, self.ADMIN},
            self.STUDENT: {self.SUPER_ADMIN, self.ADMIN}
        }[self]

    @classmethod
    def from_string(cls, value: str) -> Optional["UserRole"]:
        """Convert string to enum value with validation"""
        try:
            logger.debug(f"🔍 Conversion de '{value}' en UserRole")
            return cls(value.upper())
        except ValueError:
            logger.error(f"❌ Rôle utilisateur invalide: {value}", exc_info=True)
            raise ValidationException(
                error_code=ErrorCode.INVALID_USER_ROLE,
                role=value
            )

    def can_manage_user(self, target_role: "UserRole") -> bool:
        """Check if can manage user with target role"""
        logger.debug(f"🔍 Vérification gestion: {self.value} -> {target_role.value}")
        
        if self == self.SUPER_ADMIN:
            return True
        
        if self == self.ADMIN:
            return target_role not in [self.SUPER_ADMIN]
        
        if self == self.STAFF:
            return target_role == self.STUDENT
        
        return False

    def has_permission(self, permission: str) -> bool:
        """Check if role has specific permission"""
        logger.debug(f"🔍 Vérification permission '{permission}' pour {self.value}")
        has_perm = permission in self.permissions
        logger.debug(f"{'✅' if has_perm else '❌'} Permission {permission}")
        return has_perm

    def can_manage_ritual(self, ritual_type: str, action: str) -> bool:
        """Check if role can perform specific ritual action"""
        logger.debug(f"🔍 Vérification action rituel: {ritual_type}.{action}")
        allowed_actions = self.ritual_permissions.get(ritual_type.lower(), [])
        
        if action not in allowed_actions:
            logger.warning(
                f"⚠️ Action non autorisée: {action} pour {self.value} "
                f"sur rituel {ritual_type}"
            )
            return False
            
        return True

    def __str__(self) -> str:
        """String representation"""
        return f"{self.emoji} {self.display_names['fr']}"

    def to_dict(self, lang: str = "fr") -> dict:
        """Convert to dictionary for API responses"""
        logger.debug(f"🔄 Conversion en dict de {self.value} (lang: {lang})")
        return {
            "value": self.value,
            "name": self.display_names[lang],
            "description": self.descriptions[lang],
            "display_order": self.display_order,
            "emoji": self.emoji,
            "permissions": list(self.permissions),
            "ritual_permissions": self.ritual_permissions
        }