import React, { useState, useEffect, useImperativeHandle, forwardRef, useRef } from "react";
import { useInfiniteQuery } from "@tanstack/react-query";
import { workService } from "../services/workService";
import { topicService } from "../services/topicService";
import { RichTextEditor } from "./RichTextEditor";

export const CreateTopic = forwardRef(({ onTopicCreated }, ref) => {
    const [showNewWorkForm, setShowNewWorkForm] = useState(false);
    const [formData, setFormData] = useState({
        title: '',
        content: '',
        work_id: '',
        rating: 5
    });
    const [newWorkData, setNewWorkData] = useState({
        title: '',
        description: '',
        type: 'book',
        canonical_url: ''
    });
    const [workSearch, setWorkSearch] = useState('');
    const [showWorkDropdown, setShowWorkDropdown] = useState(false);
    const searchRef = useRef(null);
    const dropdownRef = useRef(null);

    // Use React Query for works with search and infinite scroll
    const {
        data: worksData,
        isLoading,
        fetchNextPage,
        hasNextPage,
        isFetchingNextPage,
    } = useInfiniteQuery({
        queryKey: ['works', 'list', workSearch],
        queryFn: ({ pageParam = 1 }) => workService.list({
            pageParam,
            search: workSearch,
            type: null
        }),
        getNextPageParam: (lastPage) => lastPage.nextPage,
        staleTime: 5 * 60 * 1000,
    });

    // Combine all pages into single array
    const works = worksData?.pages?.flatMap(page => page.data) || [];
    const totalWorks = worksData?.pages?.[0]?.total || 0;

    // Scroll handler for dropdown
    const handleDropdownScroll = (e) => {
        const { scrollTop, scrollHeight, clientHeight } = e.target;
        if (scrollHeight - scrollTop - clientHeight < 50 && hasNextPage && !isFetchingNextPage) {
            fetchNextPage();
        }
    };

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (e) => {
            if (searchRef.current && !searchRef.current.contains(e.target)) {
                setShowWorkDropdown(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useImperativeHandle(ref, () => ({
        focus: () => {
            document.querySelector('.create-topic__title')?.focus();
        }
    }));

    const handleCreateWork = async () => {
        try {
            const newWork = await workService.create(newWorkData);
            setFormData(prev => ({ ...prev, work_id: newWork.id }));
            setShowNewWorkForm(false);
            setNewWorkData({ title: '', description: '', type: 'book', canonical_url: '' });
        } catch (error) {
            console.error("Erro ao criar obra:", error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!formData.work_id) {
            alert('Por favor, selecione uma obra antes de publicar sua experiência.');
            return;
        }

        try {
            await topicService.create(formData);
            setFormData({ title: '', content: '', work_id: '', rating: 5 });
            onTopicCreated();
        } catch (error) {
            console.error("Erro ao criar tópico:", error);
            alert('Erro ao criar tópico. Verifique se você está logado.');
        }
    };

    const selectedWork = works.find(w => w.id === formData.work_id);

    return (
        <form onSubmit={handleSubmit} className="create-topic card">
            <h3 className="create-topic__title">✏️ Nova Experiência</h3>

            <div className="form-group">
                <label className="form-label">Obra Relacionada</label>
                <div className="create-topic__work-row">
                    <div className="create-topic__work-select" ref={searchRef}>
                        <input
                            type="text"
                            className="form-control"
                            placeholder={selectedWork ? selectedWork.title : "Buscar obra..."}
                            value={workSearch}
                            onChange={(e) => {
                                setWorkSearch(e.target.value);
                                setShowWorkDropdown(true);
                            }}
                            onFocus={() => setShowWorkDropdown(true)}
                            disabled={showNewWorkForm}
                        />
                        {showWorkDropdown && !showNewWorkForm && (
                            <div 
                                className="create-topic__work-dropdown" 
                                ref={dropdownRef}
                                onScroll={handleDropdownScroll}
                            >
                                {isLoading && works.length === 0 ? (
                                    <div className="create-topic__work-loading">
                                        <div className="spinner" />
                                        <span>Buscando...</span>
                                    </div>
                                ) : works.length > 0 ? (
                                    <>
                                        {works.map(work => (
                                            <div
                                                key={work.id}
                                                className="create-topic__work-option"
                                                onClick={() => {
                                                    setFormData({ ...formData, work_id: work.id });
                                                    setWorkSearch('');
                                                    setShowWorkDropdown(false);
                                                }}
                                            >
                                                <span className="create-topic__work-type">{work.type}</span>
                                                <span className="create-topic__work-title">{work.title}</span>
                                            </div>
                                        ))}
                                        {isFetchingNextPage && (
                                            <div className="create-topic__work-loading">
                                                <div className="spinner" />
                                                <span>Carregando mais...</span>
                                            </div>
                                        )}
                                        {!hasNextPage && works.length > 0 && (
                                            <div className="create-topic__work-hint">
                                                {totalWorks} obras encontradas
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    <div className="create-topic__work-empty">
                                        Nenhuma obra encontrada
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                    <button
                        type="button"
                        onClick={() => setShowNewWorkForm(!showNewWorkForm)}
                        className="btn btn--sm btn--action"
                    >
                        {showNewWorkForm ? 'Cancelar' : '+ Nova'}
                    </button>
                </div>

                {showNewWorkForm && (
                    <div className="create-topic__new-work">
                        <h4>Criar Nova Obra</h4>

                        <div className="create-topic__new-work-grid">
                            <div className="form-group">
                                <label className="form-label">Título</label>
                                <input
                                    type="text"
                                    className="form-control"
                                    value={newWorkData.title}
                                    onChange={e => setNewWorkData({...newWorkData, title: e.target.value})}
                                    placeholder="Título da obra"
                                />
                            </div>

                            <div className="form-group">
                                <label className="form-label">Tipo</label>
                                <select
                                    className="form-control"
                                    value={newWorkData.type}
                                    onChange={e => setNewWorkData({...newWorkData, type: e.target.value})}
                                >
                                    <option value="book">Livro</option>
                                    <option value="manga">Mangá</option>
                                    <option value="anime">Anime</option>
                                    <option value="comic">Quadrinho/HQ</option>
                                </select>
                            </div>
                        </div>

                        <div className="form-group">
                            <label className="form-label">Descrição</label>
                            <textarea
                                className="form-control"
                                value={newWorkData.description}
                                onChange={e => setNewWorkData({...newWorkData, description: e.target.value})}
                                placeholder="Descrição breve da obra"
                                rows={2}
                            />
                        </div>

                        <button
                            type="button"
                            onClick={handleCreateWork}
                            className="btn btn--action"
                        >
                            Criar Obra
                        </button>
                    </div>
                )}
            </div>

            <div className="form-group">
                <label className="form-label">Título</label>
                <input
                    type="text"
                    className="form-control create-topic__title-input"
                    placeholder="Dê um título à sua experiência..."
                    value={formData.title}
                    onChange={e => setFormData({...formData, title: e.target.value})}
                    required
                />
            </div>

            <div className="form-group">
                <label className="form-label">Sua experiência</label>
                <div className="create-topic__editor-hint">
                    Cole imagens diretamente, arraste ou use os botões. Use <code>[spoiler]texto[/spoiler]</code> para spoilers.
                </div>
                <RichTextEditor
                    content={formData.content}
                    onChange={(html) => setFormData({...formData, content: html})}
                    placeholder="O que você achou dessa obra? Compartilhe suas impressões..."
                    topicId={null}
                />
            </div>

            <div className="form-group">
                <label className="form-label">Avaliação</label>
                <select
                    className="form-control"
                    value={formData.rating}
                    onChange={e => setFormData({...formData, rating: parseInt(e.target.value)})}
                    style={{ maxWidth: '200px' }}
                >
                    <option value={5}>⭐⭐⭐⭐⭐ Excelente</option>
                    <option value={4}>⭐⭐⭐⭐ Ótimo</option>
                    <option value={3}>⭐⭐⭐ Bom</option>
                    <option value={2}>⭐⭐ Regular</option>
                    <option value={1}>⭐ Ruim</option>
                </select>
            </div>

            <button 
                type="submit" 
                className="btn btn--primary btn--lg"
                disabled={!formData.work_id}
            >
                📝 Publicar Experiência
            </button>
        </form>
    );
});

CreateTopic.displayName = 'CreateTopic';
