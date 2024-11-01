from pydantic import BaseModel
from typing import Optional, Dict, Any
from datetime import datetime
from ..models.statistics import StatisticsType

class StatisticsBase(BaseModel):
    cohort_id: int
    type: StatisticsType
    period_start: datetime
    period_end: datetime
    stats_data: Dict[str, Any]

class StatisticsCreate(StatisticsBase):
    pass

class StatisticsInDB(StatisticsBase):
    id: int
    created_at: datetime

    class Config:
        from_attributes = True

class StatisticsResponse(StatisticsInDB):
    pass