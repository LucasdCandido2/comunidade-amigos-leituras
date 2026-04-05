import React, { useState, useEffect } from 'react';
import { gamificationService } from '../services/gamificationService';
import { topicService } from '../services/topicService';

export function UserProfile({ user, onViewTopic, onBack }) {
  const [stats, setStats] = useState(null);
  const [userTopics, setUserTopics] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('stats');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [statsData, allTopics] = await Promise.all([
        gamificationService.getStats(),
        topicService.getAll(),
      ]);
      setStats(statsData);
      const filtered = (allTopics.data || []).filter(t => t.user_id === user.id);
      setUserTopics(filtered);
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
    } finally {
      setLoading(false);
    }
  };

  const getLevelInfo = (level) => {
    const levels = {
      'Novato': { icon: '🌱', color: '#22c55e', min: 0 },
      'Iniciante': { icon: '📖', color: '#3b82f6', min: 50 },
      'Participante': { icon: '⭐', color: '#eab308', min: 150 },
      'Veterano': { icon: '🏅', color: '#f97316', min: 400 },
      'Expert': { icon: '💎', color: '#8b5cf6', min: 800 },
      'Lenda': { icon: '👑', color: '#ec4899', min: 1500 },
    };
    return levels[level] || levels['Novato'];
  };

  const levelInfo = stats ? getLevelInfo(stats.level) : getLevelInfo('Novato');
  const nextLevel = getNextLevel(stats?.level);
  const progress = stats ? calculateProgress(stats.reputation, levelInfo.min, nextLevel?.min) : 0;

  function getNextLevel(currentLevel) {
    const order = ['Novato', 'Iniciante', 'Participante', 'Veterano', 'Expert', 'Lenda'];
    const idx = order.indexOf(currentLevel);
    if (idx === -1 || idx === order.length - 1) return null;
    return { min: [50, 150, 400, 800, 1500][idx] };
  }

  function calculateProgress(reputation, currentMin, nextMin) {
    if (!nextMin) return 100;
    const range = nextMin - currentMin;
    const progress = reputation - currentMin;
    return Math.min(100, Math.max(0, Math.round((progress / range) * 100)));
  }

  if (loading) {
    return (
      <div className="profile-loading">
        <div className="spinner" />
        <span>Carregando perfil...</span>
      </div>
    );
  }

  return (
    <div className="profile">
      <button onClick={onBack} className="profile__back btn btn--ghost btn--sm">
        ← Voltar
      </button>

      <div className="profile__header">
        <div className="profile__avatar">
          {user.name.charAt(0).toUpperCase()}
        </div>
        <div className="profile__info">
          <h1 className="profile__name">{user.name}</h1>
          <p className="profile__email">{user.email}</p>
          <div className="profile__level" style={{ '--level-color': levelInfo.color }}>
            <span className="profile__level-icon">{levelInfo.icon}</span>
            <span className="profile__level-name">{stats?.level || 'Novato'}</span>
          </div>
        </div>
      </div>

      <div className="profile__stats-grid">
        <div className="profile__stat-card">
          <span className="profile__stat-value">{stats?.reputation || 0}</span>
          <span className="profile__stat-label">Reputação</span>
        </div>
        <div className="profile__stat-card">
          <span className="profile__stat-value">{userTopics.length}</span>
          <span className="profile__stat-label">Tópicos</span>
        </div>
        <div className="profile__stat-card">
          <span className="profile__stat-value">{stats?.badges_earned || 0}</span>
          <span className="profile__stat-label">Badges</span>
        </div>
      </div>

      {nextLevel && (
        <div className="profile__progress">
          <div className="profile__progress-header">
            <span>Progresso para próximo nível</span>
            <span>{stats?.reputation || 0} / {nextLevel.min} XP</span>
          </div>
          <div className="profile__progress-bar">
            <div
              className="profile__progress-fill"
              style={{ width: `${progress}%`, '--level-color': levelInfo.color }}
            />
          </div>
        </div>
      )}

      <div className="profile__tabs">
        <button
          className={`profile__tab ${activeTab === 'stats' ? 'profile__tab--active' : ''}`}
          onClick={() => setActiveTab('stats')}
        >
          Estatísticas
        </button>
        <button
          className={`profile__tab ${activeTab === 'badges' ? 'profile__tab--active' : ''}`}
          onClick={() => setActiveTab('badges')}
        >
          Badges
        </button>
        <button
          className={`profile__tab ${activeTab === 'topics' ? 'profile__tab--active' : ''}`}
          onClick={() => setActiveTab('topics')}
        >
          Meus Tópicos
        </button>
      </div>

      <div className="profile__content">
        {activeTab === 'stats' && (
          <div className="profile__stats-detail">
            <div className="profile__stats-row">
              <span className="profile__stats-label">Tópicos criados</span>
              <span className="profile__stats-value">{userTopics.length}</span>
            </div>
            <div className="profile__stats-row">
              <span className="profile__stats-label">Badges desbloqueados</span>
              <span className="profile__stats-value">{stats?.badges_earned || 0}</span>
            </div>
            <div className="profile__stats-row">
              <span className="profile__stats-label">Nível atual</span>
              <span className="profile__stats-value">{stats?.level || 'Novato'}</span>
            </div>
            <div className="profile__stats-row">
              <span className="profile__stats-label">Membro desde</span>
              <span className="profile__stats-value">
                {user.created_at ? new Date(user.created_at).toLocaleDateString('pt-BR') : 'N/A'}
              </span>
            </div>
          </div>
        )}

        {activeTab === 'badges' && (
          <div className="profile__badges-grid">
            {stats?.unlocked_badges?.length > 0 ? (
              stats.unlocked_badges.map((badge) => (
                <div key={badge.id} className="profile__badge-card">
                  <span className="profile__badge-icon">{badge.icon}</span>
                  <span className="profile__badge-name">{badge.name}</span>
                  <p className="profile__badge-desc">{badge.description}</p>
                </div>
              ))
            ) : (
              <div className="empty-state">
                <div className="empty-state__icon">🏆</div>
                <p className="empty-state__title">Nenhum badge ainda</p>
                <p className="empty-state__desc">Continue ativo para desbloquear badges!</p>
              </div>
            )}
          </div>
        )}

        {activeTab === 'topics' && (
          <div className="profile__topics">
            {userTopics.length > 0 ? (
              userTopics.map((topic) => (
                <article
                  key={topic.id}
                  className="profile__topic-card"
                  onClick={() => onViewTopic(topic.id)}
                >
                  <div className="profile__topic-header">
                    <span className="profile__topic-date">
                      {new Date(topic.created_at).toLocaleDateString('pt-BR')}
                    </span>
                    {topic.rating && (
                      <span className="stars">{'\u2605'.repeat(Math.round(topic.rating))}</span>
                    )}
                  </div>
                  <h3 className="profile__topic-title">{topic.title}</h3>
                  {topic.work && (
                    <span className="badge badge--primary">{topic.work.title}</span>
                  )}
                </article>
              ))
            ) : (
              <div className="empty-state">
                <div className="empty-state__icon">📝</div>
                <p className="empty-state__title">Nenhum tópico ainda</p>
                <p className="empty-state__desc">Comece compartilhando sua experiência!</p>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
