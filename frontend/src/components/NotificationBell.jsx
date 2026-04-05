import React, { useState, useRef, useEffect } from 'react';
import { useNotifications, useNotificationPermission } from '../hooks/useNotifications';

export function NotificationBell({ user }) {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef(null);
  const { permission, requestPermission } = useNotificationPermission();
  
  const {
    notifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
  } = useNotifications(user?.id, 10000);

  useEffect(() => {
    if (permission === 'default') {
      requestPermission();
    }
  }, [permission, requestPermission]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleNotificationClick = (notification) => {
    if (!notification.is_read) {
      markAsRead(notification.id);
    }
    if (notification.link) {
      window.location.href = notification.link;
    }
  };

  const getNotificationIcon = (type) => {
    switch (type) {
      case 'new_topic': return '📝';
      case 'new_comment': return '💬';
      case 'new_rating': return '⭐';
      case 'badge_earned': return '🏆';
      default: return '🔔';
    }
  };

  return (
    <div className="notification-bell" ref={dropdownRef}>
      <button
        className="notification-bell__trigger"
        onClick={() => setIsOpen(!isOpen)}
        aria-label={`Notificações (${unreadCount} não lidas)`}
      >
        <span className="notification-bell__icon">🔔</span>
        {unreadCount > 0 && (
          <span className="notification-bell__badge">
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {isOpen && (
        <div className="notification-bell__dropdown">
          <div className="notification-bell__header">
            <h3>Notificações</h3>
            {unreadCount > 0 && (
              <button onClick={markAllAsRead} className="notification-bell__mark-all">
                Marcar todas como lidas
              </button>
            )}
          </div>

          <div className="notification-bell__list">
            {notifications.length === 0 ? (
              <div className="notification-bell__empty">
                <span>🔔</span>
                <span>Nenhuma notificação</span>
              </div>
            ) : (
              notifications.map((notification) => (
                <div
                  key={notification.id}
                  className={`notification-bell__item ${!notification.is_read ? 'notification-bell__item--unread' : ''}`}
                  onClick={() => handleNotificationClick(notification)}
                >
                  <span className="notification-bell__item-icon">
                    {getNotificationIcon(notification.type)}
                  </span>
                  <div className="notification-bell__item-content">
                    <p className="notification-bell__item-message">
                      {notification.message || notification.content}
                    </p>
                    <span className="notification-bell__item-time">
                      {formatTimeAgo(notification.created_at)}
                    </span>
                  </div>
                  <button
                    className="notification-bell__item-delete"
                    onClick={(e) => {
                      e.stopPropagation();
                      deleteNotification(notification.id);
                    }}
                    aria-label="Excluir"
                  >
                    ✕
                  </button>
                </div>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}

function formatTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'agora';
  if (diffMins < 60) return `${diffMins}min`;
  if (diffHours < 24) return `${diffHours}h`;
  if (diffDays < 7) return `${diffDays}d`;
  return date.toLocaleDateString('pt-BR');
}
