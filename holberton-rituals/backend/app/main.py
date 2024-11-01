from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from .api.v1.api import api_router
from .core.config import Settings

settings = Settings()
app = FastAPI(
    title=settings.PROJECT_NAME,
    description="Une API qui déchire tout ! avec plein de vitesse dedans, même qu'elle est cool 🚀",
    version=settings.VERSION
)

# Configuration CORS pour communiquer avec le front Vite 
# (le frontend, hein, pas la partie de ta tête sur laquelle tu tapes pour te souvenir de trucs)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Routes API
app.include_router(api_router, prefix=settings.API_V1_STR)  

@app.get("/")
async def root():
    return {
        "message": "Bienvenue dans ton API FastAPI ! 🎉",
        "version": settings.VERSION,
        "docs_url": "/docs"
    }