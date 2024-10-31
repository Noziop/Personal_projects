from pydantic import BaseModel
from typing import Optional, Dict, Any
from datetime import date, datetime
from ..models.rituals import RitualType

class FeedbackTemplateBase(BaseModel):
    ritual_type: RitualType
    template: Dict[str, Any]

class FeedbackTemplateCreate(FeedbackTemplateBase):
    pass

class FeedbackTemplateResponse(FeedbackTemplateBase):
    id: int
    is_active: bool
    created_at: datetime

    class Config:
        from_attributes = True

class FeedbackBase(BaseModel):
    ritual_type: RitualType
    template_id: int
    evaluator_id: int
    evaluated_id: int
    feedback_data: Dict[str, Any]
    presentation_url: Optional[str] = None
    session_date: date

class FeedbackCreate(FeedbackBase):
    pass

class FeedbackResponse(FeedbackBase):
    id: int
    created_at: datetime

    class Config:
        from_attributes = True