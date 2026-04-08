import React, { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { workService } from "../services/workService";

export function WorksByLetter({ onBack, onSelectWork }) {
    const [selectedLetter, setSelectedLetter] = useState(null);
    const [typeFilter, setTypeFilter] = useState('all');

    const allLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

    const workTypes = [
        { value: 'all', label: 'Todas', icon: '🌟' },
        { value: 'anime', label: 'Animes', icon: '🎌' },
        { value: 'manga', label: 'Mangás', icon: '🈶' },
        { value: 'movie', label: 'Filmes', icon: '🎬' },
        { value: 'novel', label: 'Romances', icon: '📖' },
        { value: 'comic', label: 'Comics', icon: '💥' },
        { value: 'hq', label: 'HQs', icon: '🦸' },
        { value: 'book', label: 'Livros', icon: '📚' },
    ];

    const { data: letters = [], isLoading: loadingLetters } = useQuery({
        queryKey: ['works', 'letters'],
        queryFn: () => workService.getAvailableLetters(),
        staleTime: 10 * 60 * 1000,
    });

    const { data: works = [], isLoading: loadingWorks } = useQuery({
        queryKey: ['works', 'letter', selectedLetter, typeFilter],
        queryFn: () => workService.getWorksByLetter(selectedLetter, typeFilter === 'all' ? null : typeFilter),
        enabled: !!selectedLetter,
        staleTime: 5 * 60 * 1000,
    });

    const handleLetterClick = (letter) => {
        setSelectedLetter(letter);
    };

    const handleTypeChange = (type) => {
        setTypeFilter(type);
    };

    const getLetterCount = (letter) => {
        const found = letters.find(l => l.letter === letter);
        return found?.count || 0;
    };

    if (loadingLetters) {
        return (
            <div className="ranking-loading">
                <div className="spinner" />
                <span>Carregando filtro A-Z...</span>
            </div>
        );
    }

    const worksList = works?.works || [];

    return (
        <div className="letter-filter">
            <div className="letter-filter__header">
                <button onClick={onBack} className="btn btn--ghost btn--sm">
                    ← Voltar
                </button>
                <h2 className="letter-filter__title">🔤 Filtrar por Letra</h2>
            </div>

            <div className="works-filters">
                {workTypes.map(type => (
                    <button
                        key={type.value}
                        className={`works-filter-btn ${typeFilter === type.value ? 'works-filter-btn--active' : ''}`}
                        onClick={() => handleTypeChange(type.value)}
                    >
                        {type.icon} {type.label}
                    </button>
                ))}
            </div>

            {!selectedLetter ? (
                <div className="letter-filter__grid">
                    {allLetters.map(letter => {
                        const count = getLetterCount(letter);
                        return (
                            <button
                                key={letter}
                                className={`letter-btn ${count > 0 ? 'letter-btn--active' : 'letter-btn--disabled'}`}
                                onClick={() => count > 0 && handleLetterClick(letter)}
                                disabled={count === 0}
                            >
                                <span className="letter-btn__letter">{letter}</span>
                                <span className="letter-btn__count">{count}</span>
                            </button>
                        );
                    })}
                </div>
            ) : (
                <div className="letter-filter__results">
                    <button
                        className="btn btn--ghost btn--sm"
                        onClick={() => setSelectedLetter(null)}
                    >
                        ← Todas as letras
                    </button>
                    <h3 className="letter-filter__letter-title">
                        Obras que começam com "{selectedLetter}"
                        <span className="badge badge--neutral">{worksList.length}</span>
                    </h3>

                    {loadingWorks ? (
                        <div className="ranking-loading">
                            <div className="spinner" />
                        </div>
                    ) : worksList.length === 0 ? (
                        <p className="text-muted">Nenhuma obra encontrada.</p>
                    ) : (
                        <div className="letter-filter__works">
                            {worksList.map(work => (
                                <div
                                    key={work.id}
                                    className="work-card"
                                    onClick={() => onSelectWork && onSelectWork(work)}
                                >
                                    <div className="work-card__info">
                                        <div className="work-card__type">{work.type}</div>
                                        <h4 className="work-card__title">{work.title}</h4>
                                        {work.bayesian_rating > 0 && (
                                            <span className="work-card__rating">⭐ {Number(work.bayesian_rating).toFixed(2)}</span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
