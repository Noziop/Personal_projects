from pydantic import BaseModel
from typing import Optional
from datetime import date, datetime
from ..models.rituals import DayOfWeek, RitualType

class CohortRitualDayBase(BaseModel):
    cohort_id: int
    day: DayOfWeek
    ritual_type: RitualType

class CohortRitualDayCreate(CohortRitualDayBase):
    pass

class SODDrawingBase(BaseModel):
    student_id: int
    presentation_date: date
    evaluator_id: int

class SODDrawingCreate(SODDrawingBase):
    pass

class SODDrawingResponse(SODDrawingBase):
    id: int
    created_at: datetime

    class Config:
        from_attributes = True