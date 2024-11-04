# models/domain/feedback.py
from typing import List, Dict, Optional
from sqlalchemy import Column, String, JSON, Boolean, ForeignKey, Index, Text, Date, UUID, Integer
from sqlalchemy.orm import relationship
from datetime import date
from app.core.config import settings
from app.core.exceptions import ValidationException, ErrorCode
from .base import Base
from .enums.ritual_type import RitualType

logger = settings.logger  # Notre super logger loguru ! 👑

class FeedbackTemplate(Base):
    """Template for ritual feedback forms ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    ritual_type = Column(String(20), ForeignKey('enum_ritual_type.value'), nullable=False)
    template = Column(JSON, nullable=False)
    is_active = Column(Boolean, default=True, index=True)
    version = Column(Integer, nullable=False, default=1, index=True)

    # Indexes
    __table_args__ = (
        Index('idx_template_active', 'is_active'),
        Index('idx_template_version', 'version'),
    )

    @property
    def is_sod_template(self) -> bool:
        """Check if template is for SOD"""
        return self.ritual_type == RitualType.SOD.value

    @property
    def is_standup_template(self) -> bool:
        """Check if template is for Standup"""
        return self.ritual_type == RitualType.STANDUP.value

    @property
    def ritual_display_name(self) -> str:
        """Get localized ritual name"""
        return RitualType(self.ritual_type).display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return RitualType(self.ritual_type).emoji

    def validate_feedback_data(self, feedback_data: Dict) -> bool:
        """Validate if provided feedback data matches template structure"""
        logger.debug(f"🔍 Validation feedback pour template {self.ritual_type}")
        
        try:
            template_fields = set(self.template.get('fields', {}).keys())
            feedback_fields = set(feedback_data.keys())
            is_valid = template_fields.issubset(feedback_fields)
            
            if not is_valid:
                logger.warning(f"⚠️ Champs manquants: {template_fields - feedback_fields}")
                
            return is_valid
            
        except Exception as e:
            logger.error(f"❌ Erreur validation feedback: {str(e)}")
            raise ValidationException(
                error_code=ErrorCode.INVALID_FEEDBACK_DATA,
                template_id=str(self.id)
            )

    def get_required_fields(self) -> List[str]:
        """Get list of required fields from template"""
        logger.debug(f"📝 Récupération champs requis pour {self.ritual_type}")
        return [
            field_name
            for field_name, field_data in self.template.get('fields', {}).items()
            if field_data.get('required', False)
        ]

    def calculate_total_score(self, feedback_data: Dict) -> int:
        """Calculate total score from feedback data"""
        logger.debug(f"🔢 Calcul score pour {self.ritual_type}")
        
        if not self.validate_feedback_data(feedback_data):
            logger.error("❌ Données de feedback invalides")
            raise ValidationException(
                error_code=ErrorCode.INVALID_FEEDBACK_DATA,
                template_id=str(self.id)
            )
            
        total = 0
        for field, value in feedback_data.items():
            if isinstance(value, (int, float)):
                max_points = self.template['fields'][field].get('max_points', 0)
                total += min(value, max_points)
                
        logger.info(f"✨ Score total calculé: {total}")
        return total

    def validate_score(self, field: str, score: int) -> bool:
        """Validate if a score is within allowed range for a field"""
        logger.debug(f"🔍 Validation score {score} pour {field}")
        
        field_config = self.template['fields'].get(field)
        if not field_config:
            logger.warning(f"⚠️ Champ {field} non trouvé dans le template")
            return False
            
        max_points = field_config.get('max_points', 0)
        is_valid = 0 <= score <= max_points
        
        if not is_valid:
            logger.warning(f"⚠️ Score {score} hors limites pour {field} (max: {max_points})")
            
        return is_valid

    def __str__(self) -> str:
        """String representation"""
        status = "✅" if self.is_active else "❌"
        return f"{self.ritual_emoji} {self.ritual_display_name} Template {status} - v{self.version}"

class Feedback(Base):
    """Feedback records for rituals ✨"""
    
    id = Column(UUID, primary_key=True, server_default='uuid_generate_v4()')
    ritual_type = Column(String(20), ForeignKey('enum_ritual_type.value'), nullable=False)
    template_id = Column(UUID, ForeignKey('feedback_templates.id'), nullable=False)
    evaluator_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    evaluated_id = Column(UUID, ForeignKey('students.id'), nullable=False)
    feedback_data = Column(JSON, nullable=False)
    presentation_url = Column(Text, nullable=True)
    session_date = Column(Date, nullable=False)

    # Relationships
    template = relationship("FeedbackTemplate")
    evaluator = relationship(
        "Student",
        foreign_keys=[evaluator_id],
        backref="given_feedbacks"
    )
    evaluated = relationship(
        "Student",
        foreign_keys=[evaluated_id],
        backref="received_feedbacks"
    )

    # Indexes
    __table_args__ = (
        Index('idx_feedback_dates', 'session_date'),
        Index('idx_feedback_participants', 'evaluator_id', 'evaluated_id'),
    )

    @property
    def ritual_display_name(self) -> str:
        """Get localized ritual name"""
        return RitualType(self.ritual_type).display_names['fr']

    @property
    def ritual_emoji(self) -> str:
        """Get ritual emoji"""
        return RitualType(self.ritual_type).emoji

    @property
    def total_score(self) -> int:
        """Calculate total score from feedback data"""
        logger.debug(f"🔢 Calcul score total pour feedback {self.id}")
        return self.template.calculate_total_score(self.feedback_data)

    @property
    def is_valid(self) -> bool:
        """Check if feedback data is valid according to template"""
        logger.debug(f"🔍 Vérification validité feedback {self.id}")
        return self.template.validate_feedback_data(self.feedback_data)

    def get_missing_fields(self) -> List[str]:
        """Get list of required fields that are missing"""
        logger.debug(f"📝 Recherche champs manquants pour feedback {self.id}")
        required_fields = self.template.get_required_fields()
        missing = [
            field for field in required_fields 
            if field not in self.feedback_data
        ]
        
        if missing:
            logger.warning(f"⚠️ Champs manquants dans feedback {self.id}: {missing}")
            
        return missing

    def validate(self) -> None:
        """Validate feedback data"""
        logger.debug(f"🔍 Validation complète feedback {self.id}")
        
        missing_fields = self.get_missing_fields()
        if missing_fields:
            logger.error(f"❌ Champs requis manquants: {missing_fields}")
            raise ValidationException(
                error_code=ErrorCode.MISSING_REQUIRED_FIELDS,
                fields=missing_fields
            )
            
        if not self.is_valid:
            logger.error("❌ Structure de feedback invalide")
            raise ValidationException(
                error_code=ErrorCode.INVALID_FEEDBACK_DATA,
                feedback_id=str(self.id)
            )

    def __str__(self) -> str:
        """String representation"""
        return (
            f"{self.ritual_emoji} {self.ritual_display_name} Feedback - "
            f"From {self.evaluator.full_name} to {self.evaluated.full_name} "
            f"on {self.session_date}"
        )