import React, { useMemo } from 'react';
import { sanitizeHtml } from '../utils/sanitize';
import { SpoilerRenderer } from './SpoilerRenderer';

export const TopicsList = React.memo(function TopicsList({ topics, user, onViewTopic }) {
  const sortedTopics = useMemo(() => {
    return [...topics].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }, [topics]);

  const renderContent = (content) => {
    return <SpoilerRenderer content={content} />;
  };

  if (topics.length === 0) {
    return (
      <div className="topics-list-empty">
        <div className="topics-list-empty__icon">📭</div>
        <p className="topics-list-empty__title">Nenhuma experiência ainda</p>
        <p className="topics-list-empty__desc">Seja o primeiro a compartilhar sua leitura!</p>
      </div>
    );
  }

  return (
    <div className="topics-list">
      {sortedTopics.map((topic) => (
        <div
          key={topic.id}
          className={`topics-list__item ${topic.user_id === user.id ? 'topics-list__item--own' : ''}`}
          onClick={() => onViewTopic(topic.id)}
        >
          <div className="topics-list__avatar">
            {topic.user?.name?.charAt(0).toUpperCase() || 'U'}
          </div>
          
          <div className="topics-list__content">
            <div className="topics-list__header">
              <span className="topics-list__name">
                {topic.user_id === user.id ? 'Você' : topic.user?.name || 'Usuário'}
              </span>
              <span className="topics-list__time">
                {formatTime(topic.created_at)}
              </span>
            </div>
            
            <h3 className="topics-list__title">{topic.title}</h3>
            
            <div className="topics-list__preview" style={{ minHeight: '40px' }}>
              {renderContent(topic.content)}
            </div>
            
            <div className="topics-list__meta">
              {topic.work && (
                <span className="topics-list__badge">
                  📚 {topic.work.title}
                </span>
              )}
              {topic.rating && (
                <span className="topics-list__rating">
                  {'\u2605'.repeat(Math.round(topic.rating))}
                </span>
              )}
            </div>
          </div>
          
          {topic.user_id === user.id && (
            <div className="topics-list__own-indicator" />
          )}
        </div>
      ))}
    </div>
  );
});

function formatTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'agora';
  if (diffMins < 60) return `${diffMins}m`;
  if (diffHours < 24) return `${diffHours}h`;
  if (diffDays < 7) return `${diffDays}d`;
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
}