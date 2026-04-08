import React, { useState, useEffect, useCallback, useRef } from "react";
import { useInfiniteQuery } from "@tanstack/react-query";
import { workService } from "../services/workService";

// Hook para scroll infinito com IntersectionObserver
function useInfiniteScroll(callback, hasMore, isFetching) {
    const observerRef = useRef(null);
    const loadMoreRef = useRef(null);

    useEffect(() => {
        if (!hasMore || isFetching) return;

        observerRef.current = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    callback();
                }
            },
            { threshold: 0.1 }
        );

        if (loadMoreRef.current) {
            observerRef.current.observe(loadMoreRef.current);
        }

        return () => observerRef.current?.disconnect();
    }, [callback, hasMore, isFetching]);

    return loadMoreRef;
}

const WORK_TYPES = [
    { value: 'book',  label: '📚 Livro' },
    { value: 'manga', label: '🈶 Mangá' },
    { value: 'anime', label: '🎌 Anime' },
    { value: 'movie', label: '🎬 Filme' },
    { value: 'novel', label: '📖 Romance' },
    { value: 'comic', label: '💥 Quadrinho/HQ' },
    { value: 'hq',    label: '🦸 HQ Americana' },
];

const WORK_TYPE_FILTERS = [
    { value: 'all', label: 'Todas', icon: '🌟' },
    { value: 'book',  label: 'Livros', icon: '📚' },
    { value: 'manga', label: 'Mangás', icon: '🈶' },
    { value: 'anime', label: 'Animes', icon: '🎌' },
    { value: 'movie', label: 'Filmes', icon: '🎬' },
    { value: 'novel', label: 'Romances', icon: '📖' },
    { value: 'comic', label: 'Quadrinhos', icon: '💥' },
    { value: 'hq',    label: 'HQs', icon: '🦸' },
];

const EXTERNAL_SOURCES = [
    { value: 'jikan', label: 'Jikan (MyAnimeList)', icon: '🔍' },
    { value: 'anilist', label: 'AniList', icon: '📗' },
    { value: 'kitsu', label: 'Kitsu', icon: '📘' },
    { value: 'tmdb', label: 'TMDB (Movies/TV)', icon: '🎬' },
];

const EMPTY_FORM = { 
    title: '', 
    description: '', 
    type: 'book', 
    canonical_url: '',
    external_source_id: '',
    external_id: '',
    external_url: '',
    cover_image_url: '',
    external_references: null,
    category_ids: [],
};

export function WorkEditor({ onWorkSaved, onCancel }) {
    const [categories, setCategories]  = useState([]);
    const [formData, setFormData]       = useState(EMPTY_FORM);
    const [editingWorkId, setEditingWorkId] = useState(null);
    const [loading, setLoading]         = useState(false);
    const [showForm, setShowForm]       = useState(false);
    const [feedback, setFeedback]       = useState(null);
    const [search, setSearch]           = useState('');
    const [debouncedSearch, setDebouncedSearch] = useState('');
    const [typeFilter, setTypeFilter]   = useState('all');
    
    // Busca externa
    const [externalSearch, setExternalSearch] = useState('');
    const [externalSource, setExternalSource] = useState('jikan');
    const [externalResults, setExternalResults] = useState([]);
    const [searchingExternal, setSearchingExternal] = useState(false);

    // Infinite query para scroll infinito
    const {
        data,
        fetchNextPage,
        hasNextPage,
        isFetchingNextPage,
        isLoading,
        refetch,
    } = useInfiniteQuery({
        queryKey: ['works', 'list', debouncedSearch, typeFilter],
        queryFn: ({ pageParam = 1 }) => workService.list({ 
            pageParam, 
            search: debouncedSearch, 
            type: typeFilter === 'all' ? null : typeFilter 
        }),
        getNextPageParam: (lastPage) => lastPage.nextPage,
        staleTime: 5 * 60 * 1000,
    });

    // Combina todas as páginas em uma lista
    const works = data?.pages?.flatMap(page => page.data) || [];

    // Scroll infinito
    const loadMoreRef = useInfiniteScroll(fetchNextPage, hasNextPage, isFetchingNextPage);

    // Debounce para busca local
    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(search);
        }, 300);
        return () => clearTimeout(timer);
    }, [search]);

    useEffect(() => {
        workService.getCategories().then(data => {
            setCategories(Array.isArray(data) ? data : []);
        }).catch(console.error);
    }, []);

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
            external_source_id: work.external_source_id || '',
            external_id: work.external_id || '',
            external_url: work.external_url || '',
            cover_image_url: work.cover_image_url || '',
            external_references: work.external_references || null,
            category_ids: work.categories?.map(c => c.id) || [],
        });
        // Atualiza source selector se houver fonte externa
        if (work.external_source_id) {
            const sourceMap = { 1: 'jikan', 2: 'anilist', 3: 'kitsu', 4: 'tmdb' };
            setExternalSource(sourceMap[work.external_source_id] || 'jikan');
        }
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
            let workId = editingWorkId;
            if (editingWorkId) {
                await workService.update(editingWorkId, formData);
                setFeedback({ type: 'success', msg: 'Obra atualizada com sucesso!' });
            } else {
                const created = await workService.create(formData);
                workId = created.id;
                setFeedback({ type: 'success', msg: 'Obra criada com sucesso!' });
            }
            if (workId && formData.category_ids?.length > 0) {
                await workService.assignCategories(workId, formData.category_ids);
            }
            await refetch();
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
            await refetch();
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

    // Filtro no frontend (para search local que não é paginado)
    const filteredWorks = works.filter(w => {
        const matchesSearch = w.title.toLowerCase().includes(search.toLowerCase()) ||
            (w.description || '').toLowerCase().includes(search.toLowerCase());
        const matchesType = typeFilter === 'all' || w.type === typeFilter;
        return matchesSearch && matchesType;
    });

    // Busca em fontes externas (Jikan, AniList, Kitsu, TMDB)
    const handleExternalSearch = async () => {
        if (!externalSearch.trim()) return;
        try {
            setSearchingExternal(true);
            const mediaType = formData.type === 'anime' || formData.type === 'manga' 
                ? formData.type 
                : 'anime';
            const data = await workService.searchExternal(externalSearch, mediaType, externalSource);
            setExternalResults(data.results || []);
        } catch (error) {
            console.error("Erro na busca externa:", error);
            setFeedback({ type: 'error', msg: 'Erro ao buscar em fontes externas.' });
        } finally {
            setSearchingExternal(false);
        }
    };

    // Importar obra de fonte externa
    const handleImportExternal = async (result) => {
        try {
            setLoading(true);
            const details = await workService.fetchExternal(result.external_id, result.media_type);
            if (details) {
                setFormData({
                    ...formData,
                    title: details.title || result.title,
                    description: details.synopsis || '',
                    type: details.media_type === 'movie' ? 'book' : (details.media_type === 'series' ? 'anime' : details.media_type),
                    external_source_id: result.source,
                    external_id: result.external_id,
                    external_url: details.url || result.url,
                    cover_image_url: details.image_url || result.image_url,
                    external_references: details.external_references ? JSON.stringify(details.external_references) : null,
                });
                setExternalResults([]);
                setExternalSearch('');
                setFeedback({ type: 'success', msg: 'Dados importados! Revise e salve.' });
            }
        } catch (error) {
            console.error("Erro ao importar:", error);
            setFeedback({ type: 'error', msg: 'Erro ao importar detalhes da obra.' });
        } finally {
            setLoading(false);
        }
    };

    // Sincronizar obra com fonte externa
    const handleSyncExternal = async (work) => {
        try {
            setLoading(true);
            await workService.syncExternal(work.id);
            await refetch();
            setFeedback({ type: 'success', msg: 'Obra sincronizada com sucesso!' });
        } catch (error) {
            console.error("Erro ao sincronizar:", error);
            setFeedback({ type: 'error', msg: 'Erro ao sincronizar com fonte externa.' });
        } finally {
            setLoading(false);
        }
    };

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
                        {!editingWorkId && (
                            <div className="external-search-section">
                                <div className="external-search-header">
                                    <span className="external-search-icon">🔗</span>
                                    <span>Buscar em Fontes Externas</span>
                                </div>
                                <div className="external-search-row">
                                    <select
                                        className="form-control"
                                        value={externalSource}
                                        onChange={e => setExternalSource(e.target.value)}
                                        style={{ width: '160px' }}
                                    >
                                        {EXTERNAL_SOURCES.map(s => (
                                            <option key={s.value} value={s.value}>{s.icon} {s.label}</option>
                                        ))}
                                    </select>
                                    <input
                                        type="text"
                                        className="form-control"
                                        placeholder="Buscar por título..."
                                        value={externalSearch}
                                        onChange={e => setExternalSearch(e.target.value)}
                                        onKeyDown={e => e.key === 'Enter' && handleExternalSearch()}
                                    />
                                    <button
                                        type="button"
                                        onClick={handleExternalSearch}
                                        disabled={searchingExternal || !externalSearch.trim()}
                                        className="btn btn--secondary"
                                    >
                                        {searchingExternal ? 'Buscando...' : '🔍'}
                                    </button>
                                </div>
                                {externalResults.length > 0 && (
                                    <div className="external-results">
                                        <p className="external-results-title">Resultados da busca ({externalResults.length}):</p>
                                        <div className="external-results-list">
                                            {externalResults.map((result, idx) => (
                                                <div key={idx} className="external-result-item">
                                                    {result.image_url && (
                                                        <img src={result.image_url} alt={result.title} className="external-result-thumb" />
                                                    )}
                                                    <div className="external-result-info">
                                                        <strong>{result.title}</strong>
                                                        {result.title_english && <span className="text-muted"> ({result.title_english})</span>}
                                                        <span className="external-result-type">{result.type}</span>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleImportExternal(result)}
                                                        className="btn btn--sm btn--primary"
                                                    >
                                                        Importar
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}

                        {editingWorkId && formData.external_id && (
                            <div className="sync-section">
                                <div className="sync-info">
                                    <span>🔗 Fonte externa: {formData.external_source_id} (ID: {formData.external_id})</span>
                                    <button
                                        type="button"
                                        onClick={() => handleSyncExternal({ id: editingWorkId })}
                                        disabled={loading}
                                        className="btn btn--sm btn--secondary"
                                    >
                                        🔄 Sincronizar
                                    </button>
                                </div>
                            </div>
                        )}
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

                        <div className="form-group">
                            <label htmlFor="work-categories" className="form-label">Categorias</label>
                            <select
                                id="work-categories"
                                className="form-control"
                                multiple
                                value={formData.category_ids}
                                onChange={e => {
                                    const selected = Array.from(e.target.selectedOptions, option => parseInt(option.value));
                                    setFormData({ ...formData, category_ids: selected });
                                }}
                                style={{ height: '120px' }}
                            >
                                {categories.map(cat => (
                                    <option key={cat.id} value={cat.id}>{cat.name}</option>
                                ))}
                            </select>
                            <small className="text-muted">Segure Ctrl (ou Cmd) para selecionar múltiplas</small>
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

            {!showForm && works.length > 0 && (
                <div className="works-filters">
                    {WORK_TYPE_FILTERS.map(type => (
                        <button
                            key={type.value}
                            className={`works-filter-btn ${typeFilter === type.value ? 'works-filter-btn--active' : ''}`}
                            onClick={() => setTypeFilter(type.value)}
                        >
                            {type.icon} {type.label}
                        </button>
                    ))}
                </div>
            )}

            {!showForm && (
                <div className="work-editor__list">
                    {isLoading ? (
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
                        <>
                            {filteredWorks.map(work => (
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
                            ))}
                            {hasNextPage && (
                                <div ref={loadMoreRef} className="work-editor__load-more">
                                    {isFetchingNextPage ? (
                                        <div className="work-editor__loading">
                                            <div className="spinner" />
                                            <span>Carregando mais...</span>
                                        </div>
                                    ) : (
                                        <span className="text-muted">Role para carregar mais</span>
                                    )}
                                </div>
                            )}
                        </>
                    )}
                </div>
            )}
        </div>
    );
}
