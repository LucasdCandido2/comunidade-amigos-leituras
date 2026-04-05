import React, { useState, useEffect, useCallback, useRef } from 'react';
import { notificationService } from '../services/notificationService';

export function useNotifications(userId, interval = 30000, enabled = false) {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const previousNotificationsRef = useRef([]);
  const intervalRef = useRef(null);

  const loadNotifications = useCallback(async () => {
    if (!userId || !enabled) return;
    
    try {
      const data = await notificationService.getAll();
      setNotifications(data.notifications || []);
      setUnreadCount(data.unread_count || 0);
      
      const newNotifications = (data.notifications || []).filter(n => 
        !previousNotificationsRef.current.find(p => p.id === n.id)
      );
      
      if (newNotifications.length > 0 && previousNotificationsRef.current.length > 0) {
        newNotifications.forEach(notification => {
          showNotificationToast(notification);
        });
      }
      
      previousNotificationsRef.current = data.notifications || [];
    } catch (error) {
      console.error('Erro ao carregar notificações:', error);
    }
  }, [userId, enabled]);

  const markAsRead = useCallback(async (id) => {
    try {
      await notificationService.markAsRead(id);
      setNotifications(prev => 
        prev.map(n => n.id === id ? { ...n, is_read: true } : n)
      );
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Erro ao marcar como lida:', error);
    }
  }, []);

  const markAllAsRead = useCallback(async () => {
    try {
      await notificationService.markAllAsRead();
      setNotifications(prev => prev.map(n => ({ ...n, is_read: true })));
      setUnreadCount(0);
    } catch (error) {
      console.error('Erro ao marcar todas como lidas:', error);
    }
  }, []);

  const deleteNotification = useCallback(async (id) => {
    try {
      const notification = notifications.find(n => n.id === id);
      await notificationService.delete(id);
      setNotifications(prev => prev.filter(n => n.id !== id));
      if (notification && !notification.is_read) {
        setUnreadCount(prev => Math.max(0, prev - 1));
      }
    } catch (error) {
      console.error('Erro ao deletar notificação:', error);
    }
  }, [notifications]);

  useEffect(() => {
    if (!userId || !enabled) return;

    loadNotifications();
    
    intervalRef.current = setInterval(loadNotifications, interval);
    
    return () => {
      if (intervalRef.current) clearInterval(intervalRef.current);
    };
  }, [userId, interval, loadNotifications, enabled]);

  return {
    notifications,
    unreadCount,
    loading,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    refresh: loadNotifications,
  };
}

function showNotificationToast(notification) {
  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification('Nova notificação', {
      body: notification.message || notification.content,
      icon: '/favicon.ico',
    });
  }
}

export function useNotificationPermission() {
  const [permission, setPermission] = useState(
    typeof window !== 'undefined' && 'Notification' in window
      ? Notification.permission
      : 'denied'
  );

  const requestPermission = useCallback(async () => {
    if (!('Notification' in window)) return 'denied';
    
    try {
      const result = await Notification.requestPermission();
      setPermission(result);
      return result;
    } catch (error) {
      console.error('Erro ao solicitar permissão:', error);
      return 'denied';
    }
  }, []);

  return { permission, requestPermission };
}