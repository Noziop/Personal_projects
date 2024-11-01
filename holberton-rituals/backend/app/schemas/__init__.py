from .users import UserBase, UserCreate, UserUpdate, UserInDB, UserResponse
from .cohorts import CohortBase, CohortCreate, CohortUpdate, CohortInDB
from .students import StudentBase, StudentCreate, StudentUpdate, StudentInDB, StudentResponse
from .rituals import (
    CohortRitualDayBase, CohortRitualDayCreate,
    SODDrawingBase, SODDrawingCreate, SODDrawingResponse
)
from .feedbacks import (
    FeedbackTemplateBase, FeedbackTemplateCreate, FeedbackTemplateResponse,
    FeedbackBase, FeedbackCreate, FeedbackResponse
)
from .unavailability import (
    UnavailabilityBase, UnavailabilityCreate,
    UnavailabilityUpdate, UnavailabilityResponse
)

from typing import TYPE_CHECKING
if TYPE_CHECKING:
    from .users import UserResponse
    from .cohorts import CohortInDB
    from typing import TYPE_CHECKING

# Import des schémas de base
if TYPE_CHECKING:
    from .users import UserResponse
    from .cohorts import CohortInDB
    from .students import StudentResponse
    from .feedbacks import FeedbackResponse, FeedbackTemplateResponse
    from .rituals import SODDrawingResponse, CohortRitualDayResponse

# Import des schémas utilisables
from .users import (
    UserBase,
    UserCreate,
    UserUpdate,
    UserInDB,
    UserResponse
)

from .cohorts import (
    CohortBase,
    CohortCreate,
    CohortUpdate,
    CohortInDB
)

from .students import (
    StudentBase,
    StudentCreate,
    StudentUpdate,
    StudentInDB,
    StudentResponse
)

from .rituals import (
    CohortRitualDayBase,
    CohortRitualDayCreate,
    SODDrawingBase,
    SODDrawingCreate,
    SODDrawingResponse
)

from .feedbacks import (
    FeedbackTemplateBase,
    FeedbackTemplateCreate,
    FeedbackTemplateResponse,
    FeedbackBase,
    FeedbackCreate,
    FeedbackResponse
)

from .unavailability import (
    UnavailabilityBase,
    UnavailabilityCreate,
    UnavailabilityUpdate,
    UnavailabilityResponse
)

# Export tous les schémas
__all__ = [
    "UserBase", "UserCreate", "UserUpdate", "UserInDB", "UserResponse",
    "CohortBase", "CohortCreate", "CohortUpdate", "CohortInDB",
    "StudentBase", "StudentCreate", "StudentUpdate", "StudentInDB", "StudentResponse",
    "CohortRitualDayBase", "CohortRitualDayCreate",
    "SODDrawingBase", "SODDrawingCreate", "SODDrawingResponse",
    "FeedbackTemplateBase", "FeedbackTemplateCreate", "FeedbackTemplateResponse",
    "FeedbackBase", "FeedbackCreate", "FeedbackResponse",
    "UnavailabilityBase", "UnavailabilityCreate", "UnavailabilityUpdate", "UnavailabilityResponse"
]