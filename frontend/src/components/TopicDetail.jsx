import React, { useState, useEffect, useRef } from "react";
import { topicService } from "../services/topicService";
import { interactionService } from "../services/interactionService";
import { assetService } from "../services/assetService";
import { SpoilerText } from "./SpoilerTag";
import { RichTextEditor } from "./RichTextEditor";
import { sanitizeHtml } from "../utils/sanitize";

export function TopicDetail({ topicId, user, onBack, onTopicDeleted }) {
    const [topic, setTopic]           = useState(null);
    const [interactions, setInteractions] = useState([]);
    const [assets, setAssets]         = useState([]);
    const [loading, setLoading]       = useState(true);
    const [newComment, setNewComment] = useState("");
    const [commentRating, setCommentRating] = useState(5);
    const [submitting, setSubmitting] = useState(false);
    const commentsEndRef = useRef(null);

    const [isEditing, setIsEditing]   = useState(false);
    const [editForm, setEditForm]     = useState({ title: '', content: '' });
    const [editLoading, setEditLoading] = useState(false);
    const [editError, setEditError]   = useState(null);

    const [uploading, setUploading]   = useState(false);
    const [uploadError, setUploadError] = useState(null);
    const [dragActive, setDragActive] = useState(false);

    useEffect(() => {
        loadTopicDetails();
    }, [topicId]);

    useEffect(() => {
        scrollToBottom();
    }, [interactions]);

    const loadTopicDetails = async () => {
        try {
            setLoading(true);
            const topicData = await topicService.getById(topicId);
            setTopic(topicData);
            const interactionsData = await interactionService.getByTopic(topicId);
            setInteractions(interactionsData);
            const assetsData = await assetService.getByTopic(topicId);
            setAssets(assetsData);
        } catch (error) {
            console.error("Erro ao carregar tópico:", error);
        } finally {
            setLoading(false);
        }
    };

    const scrollToBottom = () => {
        commentsEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    const handleSubmitComment = async (e) => {
        e.preventDefault();
        if (!newComment.trim()) return;
        try {
            setSubmitting(true);
            await interactionService.create(topicId, { content: newComment, rating: commentRating });
            setNewComment("");
            setCommentRating(5);
            const interactionsData = await interactionService.getByTopic(topicId);
            setInteractions(interactionsData);
        } catch (error) {
            console.error("Erro ao criar comentário:", error);
            alert("Erro ao criar comentário. Tente novamente.");
        } finally {
            setSubmitting(false);
        }
    };

    const handleStartEdit = () => {
        setEditForm({ title: topic.title, content: topic.content });
        setEditError(null);
        setIsEditing(true);
    };

    const handleCancelEdit = () => {
        setIsEditing(false);
        setEditForm({ title: '', content: '' });
        setEditError(null);
    };

    const handleSaveEdit = async (e) => {
        e.preventDefault();
        if (!editForm.title.trim() || !editForm.content.trim()) return;
        try {
            setEditLoading(true);
            setEditError(null);
            const updated = await topicService.update(topicId, editForm);
            setTopic(updated);
            setIsEditing(false);
        } catch (error) {
            const msg = error.response?.status === 403
                ? 'Você não tem permissão para editar este tópico.'
                : 'Erro ao atualizar tópico.';
            setEditError(msg);
            console.error(error);
        } finally {
            setEditLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!window.confirm('Excluir este tópico? Esta ação não pode ser desfeita.')) return;
        try {
            await topicService.delete(topicId);
            if (onTopicDeleted) onTopicDeleted();
            else onBack();
        } catch (error) {
            const msg = error.response?.status === 403
                ? 'Você não tem permissão para excluir este tópico.'
                : 'Erro ao excluir tópico.';
            alert(msg);
            console.error(error);
        }
    };

    const handleFileUpload = async (files) => {
        if (!files || files.length === 0) return;
        setUploadError(null);
        setUploading(true);

        try {
            for (const file of Array.from(files)) {
                if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
                    setUploadError('Apenas imagens (JPEG, PNG, GIF, WebP) e PDF são permitidos.');
                    continue;
                }
                if (file.size > 10 * 1024 * 1024) {
                    setUploadError('Arquivo muito grande. Máximo: 10MB.');
                    continue;
                }
                const uploaded = await assetService.upload(file, topicId);
                setAssets(prev => [...prev, uploaded]);
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Erro ao fazer upload.';
            setUploadError(msg);
            console.error(error);
        } finally {
            setUploading(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setDragActive(false);
        handleFileUpload(e.dataTransfer.files);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        setDragActive(true);
    };

    const handleDragLeave = () => {
        setDragActive(false);
    };

    const handleDeleteAsset = async (assetId) => {
        if (!window.confirm('Excluir este arquivo?')) return;
        try {
            await assetService.delete(assetId);
            setAssets(prev => prev.filter(a => a.id !== assetId));
        } catch (error) {
            alert('Erro ao excluir arquivo.');
            console.error(error);
        }
    };

    const handleExportPdf = () => {
        window.open(`/api/topics/${topicId}/pdf`, '_blank');
    };

    const isOwner = user && topic && user.id === topic.user_id;

    const formatTime = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) return 'Hoje';
        if (date.toDateString() === yesterday.toDateString()) return 'Ontem';
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    };

    if (loading) {
        return (
            <div className="topic-detail-loading">
                <div className="spinner" />
                <span>Carregando...</span>
            </div>
        );
    }

    if (!topic) {
        return (
            <div className="topic-detail-error">
                <p>Tópico não encontrado</p>
                <button onClick={onBack} className="btn btn--primary">Voltar</button>
            </div>
        );
    }

    return (
        <div className="topic-detail">
            <button onClick={onBack} className="topic-detail__back btn btn--ghost btn--sm">
                ← Voltar
            </button>

            <div className="topic-detail__card card card--elevated">
                {isEditing ? (
                    <form onSubmit={handleSaveEdit} className="topic-detail__edit-form">
                        <h3 className="topic-detail__edit-title">Editar Tópico</h3>

                        {editError && (
                            <div className="alert alert--error">{editError}</div>
                        )}

                        <div className="form-group">
                            <label className="form-label">Título</label>
                            <input
                                type="text"
                                value={editForm.title}
                                onChange={e => setEditForm({ ...editForm, title: e.target.value })}
                                className="form-control"
                                maxLength={255}
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label className="form-label">Conteúdo</label>
                            <textarea
                                value={editForm.content}
                                onChange={e => setEditForm({ ...editForm, content: e.target.value })}
                                className="form-control"
                                rows={6}
                                required
                            />
                        </div>

                        <div className="form-actions">
                            <button type="submit" disabled={editLoading} className="btn btn--primary">
                                {editLoading ? 'Salvando...' : 'Salvar'}
                            </button>
                            <button type="button" onClick={handleCancelEdit} className="btn btn--ghost">
                                Cancelar
                            </button>
                        </div>
                    </form>
                ) : (
                    <>
                        <div className="topic-detail__header">
                            <div className="topic-detail__meta">
                                <span className="topic-detail__author">
                                    {topic.user?.name || 'Usuário'}
                                </span>
                                <span className="topic-detail__date">
                                    {formatDate(topic.created_at)} às {formatTime(topic.created_at)}
                                </span>
                            </div>

                            {isOwner && (
                        <div className="topic-detail__actions">
                            <button onClick={handleExportPdf} className="btn btn--sm btn--action" title="Exportar como PDF">
                                📄 PDF
                            </button>
                            <button onClick={handleStartEdit} className="btn btn--sm btn--warning">
                                ✏️ Editar
                            </button>
                            <button onClick={handleDelete} className="btn btn--sm btn--danger">
                                🗑️ Excluir
                            </button>
                        </div>
                            )}
                        </div>

                        <h1 className="topic-detail__title">{topic.title}</h1>

                        {topic.work && (
                            <span className="badge badge--primary topic-detail__work">
                                📚 {topic.work.title}
                            </span>
                        )}

                        <div className="topic-detail__content rich-content">
                            <div dangerouslySetInnerHTML={{ __html: sanitizeHtml(topic.content) }} />
                        </div>

                        {topic.rating && (
                            <div className="topic-detail__rating">
                                <span className="stars">{'\u2605'.repeat(Math.round(topic.rating))}</span>
                                <span className="topic-detail__rating-text">{topic.rating}/5</span>
                            </div>
                        )}

                        <div className="topic-detail__files">
                            <h3>📎 Arquivos Anexados</h3>

                            {uploadError && (
                                <div className="alert alert--error">{uploadError}</div>
                            )}

                            <div
                                className={`topic-detail__dropzone ${dragActive ? 'topic-detail__dropzone--active' : ''}`}
                                onDrop={handleDrop}
                                onDragOver={handleDragOver}
                                onDragLeave={handleDragLeave}
                            >
                                <input
                                    type="file"
                                    id="file-upload"
                                    className="hidden"
                                    multiple
                                    accept="image/*,.pdf"
                                    onChange={(e) => handleFileUpload(e.target.files)}
                                />
                                <label htmlFor="file-upload" className="topic-detail__dropzone-label">
                                    <span>📤 Arraste arquivos ou clique para selecionar</span>
                                    <span className="topic-detail__dropzone-hint">Imagens ou PDF • Máx. 10MB</span>
                                </label>
                                {uploading && <span className="topic-detail__uploading">Enviando...</span>}
                            </div>

                            {assets.length > 0 ? (
                                <div className="topic-detail__assets-grid">
                                    {assets.map((asset) => (
                                        <div key={asset.id} className="topic-detail__asset-item">
                                            {asset.is_image ? (
                                                <a href={asset.url} target="_blank" rel="noopener noreferrer">
                                                    <img src={asset.url} alt={asset.original_name} />
                                                </a>
                                            ) : (
                                                <a href={asset.url} target="_blank" rel="noopener noreferrer" className="topic-detail__asset-pdf">
                                                    <span>📄</span>
                                                    <span>{asset.original_name}</span>
                                                </a>
                                            )}
                                            {isOwner && (
                                                <button
                                                    onClick={() => handleDeleteAsset(asset.id)}
                                                    className="topic-detail__asset-delete"
                                                    title="Excluir"
                                                >✕</button>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="topic-detail__no-assets">Nenhum arquivo anexado</p>
                            )}
                        </div>
                    </>
                )}
            </div>

            <div className="topic-detail__comments">
                <h2 className="topic-detail__comments-title">
                    💬 Comentários ({interactions.length})
                </h2>

                <div className="topic-detail__chat">
                    {interactions.length === 0 ? (
                        <div className="topic-detail__chat-empty">
                            <span>💭 Nenhum comentário ainda.</span>
                            <span>Seja o primeiro a comentar!</span>
                        </div>
                    ) : (
                        interactions.map((interaction) => {
                            const isOwn = interaction.user_id === user.id;
                            return (
                                <div
                                    key={interaction.id}
                                    className={`chat-bubble ${isOwn ? 'chat-bubble--own' : 'chat-bubble--other'}`}
                                >
                                    {!isOwn && (
                                        <div className="chat-bubble__avatar">
                                            {interaction.user?.name?.charAt(0).toUpperCase() || 'U'}
                                        </div>
                                    )}
                                    <div className={`chat-bubble__content ${isOwn ? 'chat-bubble__content--own' : ''}`}>
                                        {!isOwn && (
                                            <span className="chat-bubble__name">{interaction.user?.name}</span>
                                        )}
                                        <div className="chat-bubble__message rich-content">
                                            {interaction.rating && (
                                                <span className="chat-bubble__rating">
                                                    ⭐ {interaction.rating}/5
                                                </span>
                                            )}
                                            <div dangerouslySetInnerHTML={{ __html: sanitizeHtml(interaction.content) }} />
                                        </div>
                                        <span className="chat-bubble__time">
                                            {formatTime(interaction.created_at)}
                                        </span>
                                    </div>
                                </div>
                            );
                        })
                    )}
                    <div ref={commentsEndRef} />
                </div>

                <form onSubmit={handleSubmitComment} className="topic-detail__comment-form">
                    <div className="topic-detail__comment-editor">
                        <RichTextEditor
                            content={newComment}
                            onChange={setNewComment}
                            placeholder="Digite seu comentário... (cole imagens aqui)"
                            topicId={null}
                        />
                    </div>
                    <div className="topic-detail__comment-actions">
                        <select
                            value={commentRating}
                            onChange={(e) => setCommentRating(Number(e.target.value))}
                            className="topic-detail__rating-select"
                        >
                            <option value={5}>⭐⭐⭐⭐⭐</option>
                            <option value={4}>⭐⭐⭐⭐</option>
                            <option value={3}>⭐⭐⭐</option>
                            <option value={2}>⭐⭐</option>
                            <option value={1}>⭐</option>
                        </select>
                        <button
                            type="submit"
                            disabled={submitting || !newComment.trim() || newComment === '<p></p>'}
                            className="btn btn--primary"
                        >
                            {submitting ? 'Enviando...' : 'Enviar'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
