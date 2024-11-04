
# Holberton Rituals API 🎯

Une API moderne et bienveillante pour gérer les rituels au sein de l'école Holberton - Thonon les bains.

## À Propos du Projet 💫

Holberton Rituals est une application conçue pour :
- Gérer les rituels SOD et Stand-up
- Suivre la progression des étudiants
- Faciliter les feedbacks constructifs
- Créer une expérience d'apprentissage positive

## Technologies Utilisées 🚀

- Python 3.10+
- FastAPI
- SQLAlchemy 2.0
- Alembic
- PostgreSQL
- Pydantic 2.0

## Structure du Projet 📁

```text
backend/
├── app/
│   ├── models/
│   │   ├── domain/         # Modèles SQLAlchemy
│   │   │   ├── enums/     # Énumérations
│   │   │   └── ...
│   │   └── schemas/       # Schémas Pydantic (à venir)
│   ├── data/
│   │   ├── templates/     # Templates JSON
│   │   └── fixtures/      # Données initiales
│   └── api/               # Routes FastAPI (à venir)
├── migrations/            # Migrations Alembic
└── tests/                # Tests unitaires et d'intégration
```

## État d'Avancement 📊

### Phase 1 : Structure de Base ✨
- [x] Configuration initiale du projet
- [x] Modèles SQLAlchemy
  - [x] Modèles de base (User, Student, Cohort)
  - [x] Modèles de rituels (SOD, Standup)
  - [x] Modèles de feedback et statistiques
- [x] Enums et templates JSON
- [ ] Schémas Pydantic (à venir)

### Phase 2 : Base de Données 🗄️
- [x] Structure SQL
- [ ] Configuration Alembic
- [ ] Migrations initiales
- [ ] Seeds de données
- [ ] Tests de base de données

### Phase 3 : API (à venir) 🌐
...

## Installation 🛠️

(À venir)

## Contribution 🤝

(À venir)

## Documentation 📚

### Modèles de Données

#### Enums
Nous utilisons des énumérations bilingues (FR/EN) pour :
- Types de curriculum
- Types de rituels
- Rôles utilisateurs
- Jours de la semaine
- États des indisponibilités
- Types de notifications
- Types de pauses
- Types de métriques

#### Templates JSON
- `sod_template.json` : Grille d'évaluation SOD
- `standup_template.json` : Structure des rapports Stand-up
- `pause_period.json` : Configuration des périodes de pause
- `stats_example.json` : Format des statistiques

## Licence 📜

(À définir)

## Auteurs ✨

- Fassih & Zoé
- Made with 💖 and ☕

## Special Thanks 💖

Un merci spécial à notre première beta-testeuse qui se reconnaîtra, 
pour ses retours précieux et son enthousiasme contagieux ! ✨