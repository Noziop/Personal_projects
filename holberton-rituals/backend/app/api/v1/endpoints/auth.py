from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session
from datetime import timedelta
import bcrypt

from ....core.security import (
    create_access_token,
    get_current_user
)
from ....core.database import get_db
from ....schemas.users import UserCreate, UserResponse
from ....schemas.token import Token, TokenPayload
from ....models.users import User, UserRole
from ....services.users import UserService

router = APIRouter()

@router.post("/login", response_model=Token)
async def login(
    form_data: OAuth2PasswordRequestForm = Depends(),
    db: Session = Depends(get_db)
) -> Token:
    """Login endpoint"""
    user = UserService.authenticate_user(
        db, form_data.username, form_data.password
    )
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Email ou mot de passe incorrect",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    access_token = create_access_token(
        data={"sub": user.email}
    )
    
    return Token(
        access_token=access_token,
        token_type="bearer"
    )

@router.get("/me", response_model=UserResponse)
async def read_users_me(
    current_user: User = Depends(get_current_user)
) -> UserResponse:
    """Get current user info"""
    return current_user
