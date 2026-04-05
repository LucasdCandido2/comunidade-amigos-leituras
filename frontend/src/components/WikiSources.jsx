import React, { useState, useEffect } from 'react';
import { externalSourceService } from '../services/externalSourceService';

const TYPE_LABELS = {
  wiki: 'Wikis',
  site: 'Sites',
  forum: 'Fóruns',
  store: 'Lojas',
};

const TYPE_ICONS = {
  wiki: '📖',
  site: '🌐',
  forum: '💬',
  store: '🛒',
};

export function WikiSources() {
  const [sources, setSources] = useState({});
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({ name: '', url: '', type: 'wiki' });
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    loadSources();
  }, []);

  const loadSources = async () => {
    try {
      setLoading(true);
      const data = await externalSourceService.getAll();
      setSources(data.sources || {});
    } catch (error) {
      console.error('Erro ao carregar fontes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      await externalSourceService.create(formData);
      setFormData({ name: '', url: '', type: 'wiki' });
      setShowForm(false);
      loadSources();
    } catch (error) {
      console.error('Erro ao criar fonte:', error);
      alert('Erro ao adicionar fonte.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Remover esta fonte?')) return;
    try {
      await externalSourceService.delete(id);
      loadSources();
    } catch (error) {
      console.error('Erro ao remover fonte:', error);
    }
  };

  if (loading) {
    return (
      <div className="wiki-loading">
        <div className="spinner" />
        <span>Carregando fontes...</span>
      </div>
    );
  }

  const hasSources = Object.keys(sources).length > 0;

  return (
    <div className="wiki">
      <div className="wiki__header">
        <div>
          <h2 className="wiki__title">🔗 Diretório de Fontes</h2>
          <p className="wiki__subtitle">Links úteis para suas pesquisas</p>
        </div>
        <button 
          onClick={() => setShowForm(!showForm)} 
          className="btn btn--primary btn--sm"
        >
          {showForm ? 'Cancelar' : '+ Adicionar'}
        </button>
      </div>

      {showForm && (
        <div className="wiki__form card">
          <h3>Adicionar Nova Fonte</h3>
          <form onSubmit={handleSubmit} className="wiki__form-content">
            <div className="form-group">
              <label className="form-label">Nome</label>
              <input
                type="text"
                className="form-control"
                placeholder="Ex: Fandom de One Piece"
                value={formData.name}
                onChange={e => setFormData({...formData, name: e.target.value})}
                required
              />
            </div>
            <div className="form-group">
              <label className="form-label">Tipo</label>
              <select
                className="form-control"
                value={formData.type}
                onChange={e => setFormData({...formData, type: e.target.value})}
              >
                {Object.entries(TYPE_LABELS).map(([value, label]) => (
                  <option key={value} value={value}>{TYPE_ICONS[value]} {label}</option>
                ))}
              </select>
            </div>
            <div className="form-group">
              <label className="form-label">URL</label>
              <input
                type="url"
                className="form-control"
                placeholder="https://..."
                value={formData.url}
                onChange={e => setFormData({...formData, url: e.target.value})}
                required
              />
            </div>
            <button type="submit" disabled={submitting} className="btn btn--primary">
              {submitting ? 'Salvando...' : 'Adicionar'}
            </button>
          </form>
        </div>
      )}

      {!hasSources ? (
        <div className="empty-state">
          <div className="empty-state__icon">🔗</div>
          <p className="empty-state__title">Nenhuma fonte cadastrada</p>
          <p className="empty-state__desc">Adicione links úteis para a comunidade!</p>
        </div>
      ) : (
        <div className="wiki__grid">
          {Object.entries(sources).map(([type, items]) => (
            <div key={type} className="wiki__section">
              <h3 className="wiki__section-title">
                {TYPE_ICONS[type]} {TYPE_LABELS[type] || type}
              </h3>
              <div className="wiki__list">
                {items.map((source) => (
                  <div key={source.id} className="wiki__item">
                    <a 
                      href={source.url} 
                      target="_blank" 
                      rel="noopener noreferrer"
                      className="wiki__link"
                    >
                      {source.icon} {source.name}
                    </a>
                    <button
                      onClick={() => handleDelete(source.id)}
                      className="wiki__delete"
                      title="Remover"
                    >
                      ✕
                    </button>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
