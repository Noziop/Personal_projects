from pydantic import BaseModel
from typing import Optional, Dict, Any
from datetime import date, datetime
from ..models.cohorts import CurriculumType

class CohortBase(BaseModel):
    name: str
    curriculum_type: CurriculumType
    start_date: date
    end_date: date
    slack_channel: Optional[str] = None
    slack_workspace_id: Optional[str] = None
    pause_periods: Optional[Dict[str, Any]] = None

class CohortCreate(CohortBase):
    pass

class CohortUpdate(BaseModel):
    name: Optional[str] = None
    curriculum_type: Optional[CurriculumType] = None
    start_date: Optional[date] = None
    end_date: Optional[date] = None
    slack_channel: Optional[str] = None
    slack_workspace_id: Optional[str] = None
    pause_periods: Optional[Dict[str, Any]] = None
    is_active: Optional[bool] = None

class CohortInDB(CohortBase):
    id: int
    is_active: bool
    created_at: datetime

    class Config:
        from_attributes = True