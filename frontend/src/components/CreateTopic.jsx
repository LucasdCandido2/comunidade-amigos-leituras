import React, { useState, useEffect, useImperativeHandle, forwardRef } from "react";
import { workService } from "../services/workService";
import { topicService } from "../services/topicService";
import { RichTextEditor } from "./RichTextEditor";

export const CreateTopic = forwardRef(({ onTopicCreated }, ref) => {
    const [works, setWorks] = useState([]);
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

    useEffect(() => {
        loadWorks();
    }, []);

    useImperativeHandle(ref, () => ({
        focus: () => {
            document.querySelector('.create-topic__title')?.focus();
        }
    }));

    const loadWorks = async () => {
        try {
            const data = await workService.getAll();
            setWorks(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error("Erro ao carregar obras:", error);
            setWorks([]);
        }
    };

    const handleCreateWork = async () => {
        try {
            const newWork = await workService.create(newWorkData);
            setWorks(prev => [...prev, newWork]);
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

    return (
        <form onSubmit={handleSubmit} className="create-topic card">
            <h3 className="create-topic__title">✏️ Nova Experiência</h3>

            <div className="form-group">
                <label className="form-label">Obra Relacionada</label>
                <div className="create-topic__work-row">
                    <select
                        className="form-control"
                        value={formData.work_id}
                        onChange={e => setFormData({...formData, work_id: e.target.value})}
                        disabled={showNewWorkForm}
                    >
                        <option value="">Selecione uma obra...</option>
                        {works.map(work => (
                            <option key={work.id} value={work.id}>{work.title}</option>
                        ))}
                    </select>
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
