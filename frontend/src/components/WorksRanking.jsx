import React, { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { workService } from "../services/workService";

export function WorksRanking({ onBack }) {
    const [activeFilter, setActiveFilter] = useState('all');

    const { data: works = [], isLoading, error } = useQuery({
        queryKey: ['works', 'ranking'],
        queryFn: () => workService.getAll(),
        staleTime: 5 * 60 * 1000,
    });

    const getTypeIcon = (type) => {
        const icons = { book: '📚', manga: '🈶', anime: '🎌', comic: '💥', hq: '🦸', movie: '🎬', novel: '📖' };
        return icons[type] || '📖';
    };

    const workTypes = ['all', 'anime', 'manga', 'movie', 'novel', 'comic', 'hq', 'book'];
    
    const filteredWorks = activeFilter === 'all' 
        ? works 
        : works.filter(work => work.type === activeFilter);

    const getTypeLabel = (type) => {
        const labels = {
            all: 'Todas',
            anime: 'Animes',
            manga: 'Mangás',
            movie: 'Filmes',
            novel: 'Romances',
            comic: 'Comics',
            hq: 'HQs',
            book: 'Livros'
        };
        return labels[type] || type;
    };

    if (isLoading) {
        return (
            <div className="ranking-loading">
                <div className="spinner" />
                <span>Carregando ranking...</span>
            </div>
        );
    }

    if (error) {
        return (
            <div className="empty-state">
                <div className="empty-state__icon">⚠️</div>
                <p className="empty-state__title">Erro ao carregar ranking</p>
                <button className="btn btn--primary" onClick={() => window.location.reload()}>Tentar novamente</button>
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
                    <span className="badge badge--neutral">{filteredWorks.length} obras</span>
                </div>
                <p className="ranking__subtitle">As obras mais bem avaliadas pela comunidade</p>
            </div>

            <div className="works-filters">
                {workTypes.map(type => (
                    <button
                        key={type}
                        className={`works-filter-btn ${activeFilter === type ? 'works-filter-btn--active' : ''}`}
                        onClick={() => setActiveFilter(type)}
                    >
                        {type === 'all' ? '🌟' : getTypeIcon(type)} {getTypeLabel(type)}
                    </button>
                ))}
            </div>

            {filteredWorks.length === 0 ? (
                <div className="empty-state">
                    <div className="empty-state__icon">📚</div>
                    <p className="empty-state__title">Nenhuma obra encontrada</p>
                    <p className="empty-state__desc">Cadastre obras ao criar novos tópicos!</p>
                </div>
            ) : (
                <div className="ranking__grid">
                    {filteredWorks.map((work, index) => (
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
