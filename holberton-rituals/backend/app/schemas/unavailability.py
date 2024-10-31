from pydantic import BaseModel
from typing import Optional
from datetime import date, datetime
from ..models.unavailability import UnavailabilityStatus

class UnavailabilityBase(BaseModel):
    student_id: int
    start_date: date
    end_date: date
    reason: Optional[str] = None

class UnavailabilityCreate(UnavailabilityBase):
    pass

class UnavailabilityUpdate(BaseModel):
    status: UnavailabilityStatus
    validated_by: int

class UnavailabilityResponse(UnavailabilityBase):
    id: int
    status: UnavailabilityStatus
    validated_by: Optional[int] = None
    created_at: datetime

    class Config:
        from_attributes = True