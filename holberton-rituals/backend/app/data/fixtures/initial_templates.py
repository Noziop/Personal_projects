# app/data/fixtures/initial_templates.py
import json
from pathlib import Path
from app.models.domain.feedback import FeedbackTemplate
from app.db.session import SessionLocal

def load_templates():
    """Load and initialize feedback templates"""
    templates_dir = Path(__file__).parent.parent / 'templates'
    
    # Load SOD template
    with open(templates_dir / 'sod_template.json', 'r', encoding='utf-8') as f:
        sod_template = json.load(f)

    db = SessionLocal()
    try:
        # Initialize SOD template if not exists
        if not db.query(FeedbackTemplate).filter_by(ritual_type='SOD', version=1).first():
            new_template = FeedbackTemplate(
                ritual_type='SOD',
                template=sod_template,
                version=1,
                is_active=True
            )
            db.add(new_template)
            print("✨ SOD template initialized!")
            
        db.commit()
    except Exception as e:
        print(f"❌ Error loading templates: {e}")
        db.rollback()
    finally:
        db.close()