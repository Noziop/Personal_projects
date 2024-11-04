# main.py
from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from fastapi.exceptions import RequestValidationError
from contextlib import asynccontextmanager
from sqlalchemy.orm import Session

from app.core.config import settings
from app.core.exceptions import RitualException
from app.api.v1.router import api_router
from app.models.domain.base import Base
from app.core.database import engine, SessionLocal

# Gestion du démarrage/arrêt de l'app 🚀
@asynccontextmanager
async def lifespan(app: FastAPI):
    """Événements de démarrage et d'arrêt"""
    # Démarrage
    print("✨ Démarrage de l'application Holberton Rituals...")
    Base.metadata.create_all(bind=engine)
    print("📚 Base de données initialisée")
    yield
    # Arrêt
    print("👋 Arrêt de l'application...")

# Création de l'application FastAPI 👑
app = FastAPI(
    title=settings.PROJECT_NAME,
    version=settings.VERSION,
    description="API pour la gestion des rituels Holberton",
    lifespan=lifespan,
    docs_url=f"{settings.API_V1_STR}/docs",
    openapi_url=f"{settings.API_V1_STR}/openapi.json"
)

# Middleware CORS 🌐
app.add_middleware(
    CORSMiddleware,
    allow_origins=[str(origin) for origin in settings.BACKEND_CORS_ORIGINS],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Gestion des exceptions 🎯
@app.exception_handler(RitualException)
async def ritual_exception_handler(request: Request, exc: RitualException):
    """Handler pour nos exceptions personnalisées"""
    return JSONResponse(
        status_code=exc.status_code,
        content={"detail": exc.detail}
    )

@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request: Request, exc: RequestValidationError):
    """Handler pour les erreurs de validation Pydantic"""
    return JSONResponse(
        status_code=422,
        content={
            "detail": "Erreur de validation des données",
            "errors": [
                {
                    "loc": err["loc"],
                    "msg": err["msg"],
                    "type": err["type"]
                }
                for err in exc.errors()
            ]
        }
    )

# Dependency pour la DB 🗄️
def get_db():
    """Fournit une session DB pour les endpoints"""
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# Routes de test 🛠️
@app.get("/")
async def root():
    """Route racine pour vérifier que l'API fonctionne"""
    return {
        "message": "Bienvenue sur l'API Holberton Rituals",
        "version": settings.VERSION,
        "docs": f"{settings.API_V1_STR}/docs"
    }

@app.get("/health")
async def health_check():
    """Route de healthcheck"""
    return {
        "status": "healthy",
        "version": settings.VERSION,
        "environment": "development" if settings.DEBUG else "production"
    }

# Inclusion des routes de l'API 🛣️
app.include_router(
    api_router,
    prefix=settings.API_V1_STR
)

# Message de démarrage 📢
if __name__ == "__main__":
    import uvicorn
    print(f"🚀 Démarrage de {settings.PROJECT_NAME} v{settings.VERSION}")
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=8000,
        reload=settings.DEBUG
    )