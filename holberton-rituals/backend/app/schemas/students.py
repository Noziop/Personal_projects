from pydantic import BaseModel
from typing import Optional
from datetime import datetime

class StudentBase(BaseModel):
    user_id: int
    current_cohort_id: int

class StudentCreate(StudentBase):
    pass

class StudentUpdate(BaseModel):
    current_cohort_id: Optional[int] = None
    sod_count: Optional[int] = None
    standup_count: Optional[int] = None

class StudentInDB(StudentBase):
    id: int
    sod_count: int
    standup_count: int
    created_at: datetime

    class Config:
        from_attributes = True

class StudentResponse(StudentInDB):
    user: 'UserResponse'
    cohort: 'CohortInDB'