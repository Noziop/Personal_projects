from abc import ABC, abstractmethod
from typing import List, Dict, Any
from datetime import datetime

class NotificationService(ABC):
    """Classe de base pour les services de notification"""
    
    @abstractmethod
    async def send_notification(
        self,
        recipient_id: str,
        message: str,
        **kwargs
    ) -> bool:
        """Méthode abstraite pour envoyer une notification"""
        pass

    @abstractmethod
    async def send_bulk_notification(
        self,
        recipient_ids: List[str],
        message: str,
        **kwargs
    ) -> Dict[str, bool]:
        """Méthode abstraite pour envoyer des notifications en masse"""
        pass