from typing import List, Dict, Optional
from datetime import datetime
import aiohttp
from ...core.config import settings
from .base import NotificationService

class SlackNotificationService(NotificationService):
    """Service de notification via Slack"""

    def __init__(self):
        self.bot_token = settings.SLACK_BOT_TOKEN
        self.base_url = "https://slack.com/api"

    async def send_notification(
        self,
        recipient_id: str,
        message: str,
        **kwargs
    ) -> bool:
        """Envoie un message privé à un utilisateur sur Slack"""
        blocks = self._format_message(message, **kwargs)
        
        async with aiohttp.ClientSession() as session:
            async with session.post(
                f"{self.base_url}/chat.postMessage",
                headers={
                    "Authorization": f"Bearer {self.bot_token}",
                    "Content-Type": "application/json"
                },
                json={
                    "channel": recipient_id,
                    "blocks": blocks,
                    "text": message  # Fallback text
                }
            ) as response:
                return response.status == 200

    async def send_bulk_notification(
        self,
        recipient_ids: List[str],
        message: str,
        **kwargs
    ) -> Dict[str, bool]:
        """Envoie des messages à plusieurs utilisateurs"""
        results = {}
        for recipient_id in recipient_ids:
            results[recipient_id] = await self.send_notification(
                recipient_id, message, **kwargs
            )
        return results

    def _format_message(self, message: str, **kwargs) -> List[Dict]:
        """Formate le message en blocks Slack"""
        blocks = []
        
        # En-tête avec icône selon le type
        header_text = "🔄 *Changement de planning*" if kwargs.get('is_replacement') \
            else "📅 *Notification de rituel*"
        
        blocks.append({
            "type": "header",
            "text": {
                "type": "plain_text",
                "text": header_text
            }
        })

        # Message principal
        blocks.append({
            "type": "section",
            "text": {
                "type": "mrkdwn",
                "text": message
            }
        })

        # Informations supplémentaires si c'est un remplacement
        if kwargs.get('is_replacement'):
            blocks.append({
                "type": "context",
                "elements": [{
                    "type": "mrkdwn",
                    "text": f"Raison : {kwargs.get('reason', 'Non spécifiée')}"
                }]
            })

        return blocks