import React, { useState, useEffect } from 'react';
import { topicService } from '../services/topicService';
import { gamificationService } from '../services/gamificationService';
import { sanitizeHtml } from '../utils/sanitize';
import { SpoilerRenderer } from './SpoilerRenderer';

export function Home({ user, onViewTopic, onShowRanking, onShowWorks, onShowLetters, onNewTopic }) {
  const [stats, setStats] = useState(null);
  const [recentTopics, setRecentTopics] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [topicsData, statsData] = await Promise.all([
        topicService.getAll(),
        gamificationService.getStats().catch(() => null),
      ]);
      setRecentTopics(Array.isArray(topicsData.data) ? topicsData.data.slice(0, 5) : []);
      setStats(statsData);
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="home-loading">
        <div className="spinner" />
        <span>Carregando...</span>
      </div>
    );
  }

  return (
    <div className="home">
      <div className="home__welcome">
        <div className="home__welcome-text">
          <h1>Bem-vindo, {user.name}!</h1>
          <p>Compartilhe suas experiências de leitura, anime e mangá com a comunidade.</p>
        </div>
        {stats && (
          <div className="home__user-stats">
            <div className="home__stat">
              <span className="home__stat-value">{stats.reputation || 0}</span>
              <span className="home__stat-label">Reputação</span>
            </div>
            <div className="home__stat">
              <span className="home__stat-value">{stats.level || 'Novato'}</span>
              <span className="home__stat-label">Nível</span>
            </div>
            <div className="home__stat">
              <span className="home__stat-value">{stats.badges_earned || 0}</span>
              <span className="home__stat-label">Badges</span>
            </div>
          </div>
        )}
      </div>

      <div className="home__grid">
        <div className="home__section home__section--topics">
          <div className="home__section-header">
            <h2>Atividade Recente</h2>
            <span className="badge badge--neutral">{recentTopics.length} tópicos</span>
          </div>

          {recentTopics.length === 0 ? (
            <div className="empty-state">
              <div className="empty-state__icon">📭</div>
              <p className="empty-state__title">Nenhuma atividade ainda</p>
              <p className="empty-state__desc">Seja o primeiro a compartilhar!</p>
            </div>
          ) : (
            <div className="home__topic-list">
              {recentTopics.map((topic) => (
                <article
                  key={topic.id}
                  className={`home__topic-card ${topic.user_id === user.id ? 'home__topic-card--own' : ''}`}
                  onClick={() => onViewTopic(topic.id)}
                >
                  <div className="home__topic-header">
                    <span className="home__topic-author">
                      {topic.user_id === user.id ? 'Você' : topic.user?.name}
                      {topic.user_id === user.id && <span className="home__topic-own-badge">seu</span>}
                    </span>
                    <span className="home__topic-date">
                      {new Date(topic.created_at).toLocaleDateString('pt-BR')}
                    </span>
                  </div>
                  <h3 className="home__topic-title">{topic.title}</h3>
                  <p className="home__topic-preview">
                    <SpoilerRenderer content={topic.content} />
                  </p>
                  <div className="home__topic-footer">
                    {topic.work && (
                      <span className="badge badge--primary">{topic.work.title}</span>
                    )}
                    {topic.rating && (
                      <span className="stars">{'\u2605'.repeat(Math.round(topic.rating))}</span>
                    )}
                  </div>
                </article>
              ))}
            </div>
          )}
        </div>

        <div className="home__sidebar">
          <div className="home__section">
            <h2>Ações Rápidas</h2>
            <div className="home__quick-actions">
              <button className="home__action-btn home__action-btn--primary" onClick={onNewTopic}>
                <span className="home__action-icon">✏️</span>
                <span>Novo Tópico</span>
              </button>
              <button className="home__action-btn" onClick={onShowWorks}>
                <span className="home__action-icon">📚</span>
                <span>Minhas Obras</span>
              </button>
              <button className="home__action-btn" onClick={onShowRanking}>
                <span className="home__action-icon">🏆</span>
                <span>Ranking</span>
              </button>
              <button className="home__action-btn" onClick={onShowLetters}>
                <span className="home__action-icon">🔤</span>
                <span>A-Z</span>
              </button>
            </div>
          </div>

          {stats?.unlocked_badges?.length > 0 && (
            <div className="home__section">
              <h2>Seus Badges</h2>
              <div className="home__badges">
                {stats.unlocked_badges.map((badge) => (
                  <div key={badge.id} className="home__badge" title={badge.description}>
                    <span className="home__badge-icon">{badge.icon}</span>
                    <span className="home__badge-name">{badge.name}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          <div className="home__section home__section--info">
            <h2>Sobre a Comunidade</h2>
            <div className="home__info-item">
              <span className="home__info-icon">📖</span>
              <div>
                <strong>Obras Cadastradas</strong>
                <p>Acesse o acervo completo</p>
              </div>
            </div>
            <div className="home__info-item">
              <span className="home__info-icon">💬</span>
              <div>
                <strong>Debates</strong>
                <p>Participe de conversas</p>
              </div>
            </div>
            <div className="home__info-item">
              <span className="home__info-icon">⭐</span>
              <div>
                <strong>Avaliações</strong>
                <p>Ajude a ranquear obras</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
