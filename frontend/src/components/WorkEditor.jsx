import React, { useState, useEffect } from "react";
import { workService } from "../services/workService";

const WORK_TYPES = [
    { value: 'book',  label: '📚 Livro' },
    { value: 'manga', label: '🈶 Mangá' },
    { value: 'anime', label: '🎌 Anime' },
    { value: 'comic', label: '💥 Quadrinho/HQ' },
    { value: 'hq',    label: '🦸 HQ Americana' },
];

const EMPTY_FORM = { title: '', description: '', type: 'book', canonical_url: '' };

export function WorkEditor({ onWorkSaved, onCancel }) {
    const [works, setWorks]             = useState([]);
    const [formData, setFormData]       = useState(EMPTY_FORM);
    const [editingWorkId, setEditingWorkId] = useState(null);
    const [loading, setLoading]         = useState(false);
    const [listLoading, setListLoading] = useState(true);
    const [showForm, setShowForm]       = useState(false);
    const [feedback, setFeedback]       = useState(null);
    const [search, setSearch]           = useState('');

    useEffect(() => {
        loadWorks();
    }, []);

    const loadWorks = async () => {
        try {
            setListLoading(true);
            const data = await workService.list();
            setWorks(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error("Erro ao carregar obras:", error);
            setWorks([]);
        } finally {
            setListLoading(false);
        }
    };

    const openCreate = () => {
        setEditingWorkId(null);
        setFormData(EMPTY_FORM);
        setFeedback(null);
        setShowForm(true);
    };

    const openEdit = (work) => {
        setEditingWorkId(work.id);
        setFormData({
            title:         work.title         || '',
            description:   work.description   || '',
            type:          work.type          || 'book',
            canonical_url: work.canonical_url || '',
        });
        setFeedback(null);
        setShowForm(true);
    };

    const closeForm = () => {
        setShowForm(false);
        setEditingWorkId(null);
        setFormData(EMPTY_FORM);
        setFeedback(null);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            setLoading(true);
            if (editingWorkId) {
                await workService.update(editingWorkId, formData);
                setFeedback({ type: 'success', msg: 'Obra atualizada com sucesso!' });
            } else {
                await workService.create(formData);
                setFeedback({ type: 'success', msg: 'Obra criada com sucesso!' });
            }
            await loadWorks();
            closeForm();
            if (onWorkSaved) onWorkSaved();
        } catch (error) {
            const msg = error.response?.data?.message
                || error.response?.data?.errors
                || `Erro ao ${editingWorkId ? 'atualizar' : 'criar'} obra.`;
            setFeedback({ type: 'error', msg: typeof msg === 'object' ? Object.values(msg).flat().join(' ') : msg });
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (work) => {
        if (!window.confirm(`Excluir "${work.title}"? Esta ação não pode ser desfeita.`)) return;
        try {
            setLoading(true);
            await workService.delete(work.id);
            await loadWorks();
        } catch (error) {
            const msg = error.response?.status === 403
                ? 'Você não tem permissão para excluir esta obra.'
                : 'Erro ao excluir obra.';
            alert(msg);
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const filteredWorks = works.filter(w =>
        w.title.toLowerCase().includes(search.toLowerCase()) ||
        (w.description || '').toLowerCase().includes(search.toLowerCase())
    );

    return (
        <div className="work-editor">
            <div className="work-editor__header">
                <div>
                    <h2 className="work-editor__title">📚 Gerenciar Obras</h2>
                    <p className="work-editor__subtitle">{works.length} obra{works.length !== 1 ? 's' : ''} cadastrada{works.length !== 1 ? 's' : ''}</p>
                </div>
                <div className="work-editor__actions">
                    <button onClick={openCreate} className="btn btn--primary">
                        + Nova Obra
                    </button>
                    {onCancel && (
                        <button onClick={onCancel} className="btn btn--ghost">
                            Fechar
                        </button>
                    )}
                </div>
            </div>

            {showForm && (
                <div className="work-editor__form-container">
                    <div className="work-editor__form-header">
                        <h3>{editingWorkId ? '✏️ Editar Obra' : '➕ Nova Obra'}</h3>
                        <button onClick={closeForm} className="btn btn--icon btn--ghost" aria-label="Fechar">✕</button>
                    </div>

                    {feedback && (
                        <div className={`alert alert--${feedback.type}`}>
                            {feedback.msg}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="work-form">
                        <div className="form-group">
                            <label htmlFor="work-title" className="form-label">Título *</label>
                            <input
                                id="work-title"
                                type="text"
                                className="form-control"
                                value={formData.title}
                                onChange={e => setFormData({ ...formData, title: e.target.value })}
                                placeholder="Título da obra"
                                required
                                maxLength={255}
                            />
                        </div>

                        <div className="form-group">
                            <label htmlFor="work-type" className="form-label">Tipo *</label>
                            <select
                                id="work-type"
                                className="form-control"
                                value={formData.type}
                                onChange={e => setFormData({ ...formData, type: e.target.value })}
                                required
                            >
                                {WORK_TYPES.map(t => (
                                    <option key={t.value} value={t.value}>{t.label}</option>
                                ))}
                            </select>
                        </div>

                        <div className="form-group">
                            <label htmlFor="work-description" className="form-label">Descrição</label>
                            <textarea
                                id="work-description"
                                className="form-control"
                                value={formData.description}
                                onChange={e => setFormData({ ...formData, description: e.target.value })}
                                placeholder="Descrição breve da obra (opcional)"
                                rows={3}
                            />
                        </div>

                        <div className="form-group">
                            <label htmlFor="work-url" className="form-label">URL Canônica</label>
                            <input
                                id="work-url"
                                type="url"
                                className="form-control"
                                value={formData.canonical_url}
                                onChange={e => setFormData({ ...formData, canonical_url: e.target.value })}
                                placeholder="https://..."
                            />
                        </div>

                        <div className="form-actions">
                            <button type="submit" disabled={loading} className="btn btn--primary">
                                {loading ? 'Salvando...' : (editingWorkId ? 'Atualizar Obra' : 'Criar Obra')}
                            </button>
                            <button type="button" onClick={closeForm} className="btn btn--ghost">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {!showForm && works.length > 3 && (
                <div className="work-editor__search">
                    <input
                        type="search"
                        className="form-control"
                        placeholder="Buscar obras..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                    />
                </div>
            )}

            {!showForm && (
                <div className="work-editor__list">
                    {listLoading ? (
                        <div className="work-editor__empty">
                            <div className="spinner" />
                            <span>Carregando obras...</span>
                        </div>
                    ) : filteredWorks.length === 0 ? (
                        <div className="empty-state">
                            <div className="empty-state__icon">📚</div>
                            <p className="empty-state__title">
                                {search ? 'Nenhuma obra encontrada' : 'Nenhuma obra cadastrada'}
                            </p>
                            <p className="empty-state__desc">
                                {search ? 'Tente outro termo de busca.' : 'Cadastre sua primeira obra!'}
                            </p>
                        </div>
                    ) : (
                        filteredWorks.map(work => (
                            <div key={work.id} className="work-card">
                                <div className="work-card__info">
                                    <div className="work-card__type">
                                        {WORK_TYPES.find(t => t.value === work.type)?.label || work.type}
                                    </div>
                                    <h4 className="work-card__title">{work.title}</h4>
                                    {work.description && (
                                        <p className="work-card__desc">{work.description}</p>
                                    )}
                                    {work.bayesian_rating > 0 && (
                                        <span className="work-card__rating">⭐ {Number(work.bayesian_rating).toFixed(2)}</span>
                                    )}
                                </div>
                                <div className="work-card__actions">
                                    <button
                                        onClick={() => openEdit(work)}
                                        className="btn btn--sm btn--warning"
                                        disabled={loading}
                                    >
                                        ✏️ Editar
                                    </button>
                                    <button
                                        onClick={() => handleDelete(work)}
                                        className="btn btn--sm btn--danger"
                                        disabled={loading}
                                    >
                                        🗑️ Excluir
                                    </button>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            )}
        </div>
    );
}
