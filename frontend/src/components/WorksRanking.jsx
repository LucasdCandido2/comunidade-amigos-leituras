import React, { useState, useEffect } from "react";
import { workService } from "../services/workService";

export function WorksRanking({ onBack }) {
    const [works, setWorks] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadTopWorks();
    }, []);

    const loadTopWorks = async () => {
        try {
            setLoading(true);
            const data = await workService.getAll();
            setWorks(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error("Erro ao carregar obras:", error);
            setWorks([]);
        } finally {
            setLoading(false);
        }
    };

    const getTypeIcon = (type) => {
        const icons = { book: '📚', manga: '🈶', anime: '🎌', comic: '💥', hq: '🦸' };
        return icons[type] || '📖';
    };

    if (loading) {
        return (
            <div className="ranking-loading">
                <div className="spinner" />
                <span>Carregando ranking...</span>
            </div>
        );
    }

    return (
        <div className="ranking">
            <div className="ranking__header">
                <button onClick={onBack} className="btn btn--ghost btn--sm ranking__back">
                    ← Voltar
                </button>
                <div className="ranking__title-row">
                    <h1 className="ranking__title">🏆 Ranking de Obras</h1>
                    <span className="badge badge--neutral">{works.length} obras</span>
                </div>
                <p className="ranking__subtitle">As obras mais bem avaliadas pela comunidade</p>
            </div>

            {works.length === 0 ? (
                <div className="empty-state">
                    <div className="empty-state__icon">📚</div>
                    <p className="empty-state__title">Nenhuma obra encontrada</p>
                    <p className="empty-state__desc">Cadastre obras ao criar novos tópicos!</p>
                </div>
            ) : (
                <div className="ranking__grid">
                    {works.map((work, index) => (
                        <div key={work.id} className="ranking__card card">
                            <div className="ranking__card-position">
                                <span className={`ranking__position ranking__position--${index + 1}`}>
                                    #{index + 1}
                                </span>
                            </div>

                            <div className="ranking__card-header">
                                <span className="ranking__type-badge">
                                    {getTypeIcon(work.type)} {work.type}
                                </span>
                                <div className="ranking__rating">
                                    <span className="stars">
                                        {'\u2605'.repeat(Math.min(5, Math.floor(Number(work.bayesian_rating) || 0)))}
                                    </span>
                                    <span className="ranking__rating-value">
                                        {Number(work.bayesian_rating || 0).toFixed(1)}
                                    </span>
                                </div>
                            </div>

                            <h3 className="ranking__work-title">{work.title}</h3>
                            
                            {work.description && (
                                <p className="ranking__work-desc">{work.description}</p>
                            )}

                            {work.canonical_url && (
                                <a
                                    href={work.canonical_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="ranking__external-link"
                                >
                                    🔗 Ver fonte externa
                                </a>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
