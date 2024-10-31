from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI(
    title="Holberton Rituals API",
    description="Une API qui déchire tout ! avec plein de vitesse dedans, même qu'elle est cool 🚀",
    version="1.0.0"
)

# Configuration CORS pour communiquer avec ton front Vite
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173"],  # Port par défaut de Vite
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
async def root():
    return {"message": "Bienvenue dans ton API FastAPI ! 🎉"}