# core/exceptions.py
from fastapi import HTTPException, status
from typing import Any, Dict, Optional

class RitualException(HTTPException):
    """Base exception pour notre application"""
    def __init__(
        self,
        status_code: int,
        detail: str,
        error_code: str = None,
        headers: Dict[str, str] = None
    ):
        super().__init__(status_code=status_code, detail=detail, headers=headers)
        self.error_code = error_code

class AuthenticationException(RitualException):
    """Exceptions liées à l'authentification"""
    def __init__(
        self,
        detail: str = "Authentification invalide",
        error_code: str = "AUTH_ERROR"
    ):
        super().__init__(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail=detail,
            error_code=error_code,
            headers={"WWW-Authenticate": "Bearer"}
        )

class PermissionDeniedException(RitualException):
    """Exceptions liées aux permissions"""
    def __init__(
        self,
        detail: str = "Permission refusée",
        error_code: str = "PERMISSION_DENIED"
    ):
        super().__init__(
            status_code=status.HTTP_403_FORBIDDEN,
            detail=detail,
            error_code=error_code
        )

class ResourceNotFoundException(RitualException):
    """Exceptions pour les ressources non trouvées"""
    def __init__(
        self,
        resource_type: str,
        resource_id: Any,
        error_code: str = "NOT_FOUND"
    ):
        super().__init__(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"{resource_type} avec l'ID {resource_id} non trouvé",
            error_code=error_code
        )

class ValidationException(RitualException):
    """Exceptions de validation"""
    def __init__(
        self,
        detail: str = "Erreur de validation",
        errors: Optional[Dict] = None,
        error_code: str = "VALIDATION_ERROR"
    ):
        super().__init__(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail={"message": detail, "errors": errors} if errors else detail,
            error_code=error_code
        )

class RitualBusinessException(RitualException):
    """Exceptions métier spécifiques aux rituels"""
    def __init__(
        self,
        detail: str,
        error_code: str = "RITUAL_ERROR"
    ):
        super().__init__(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=detail,
            error_code=error_code
        )

# Exceptions spécifiques aux rituels
class SODException(RitualBusinessException):
    """Exceptions spécifiques aux SOD"""
    def __init__(self, detail: str):
        super().__init__(detail=detail, error_code="SOD_ERROR")

class StandupException(RitualBusinessException):
    """Exceptions spécifiques aux Standups"""
    def __init__(self, detail: str):
        super().__init__(detail=detail, error_code="STANDUP_ERROR")

# Utilisation :
"""
# Exemple d'utilisation dans un service
if not user_exists:
    raise ResourceNotFoundException("User", user_id)

if not user.can_present_sod():
    raise SODException("L'étudiant ne peut pas présenter de SOD aujourd'hui")

if not valid_feedback:
    raise ValidationException(
        detail="Feedback invalide",
        errors={
            "score": "Le score doit être entre 0 et 100",
            "comments": "Les commentaires sont requis"
        }
    )
"""