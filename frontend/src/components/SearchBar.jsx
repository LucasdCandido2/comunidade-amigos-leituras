import React, { useState, useEffect, useRef, useCallback } from 'react';
import { searchService } from '../services/searchService';
import { sanitizeHtml } from '../utils/sanitize';

export function SearchBar({ onSelect, placeholder = 'Buscar tópicos e obras...' }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState({ works: [], topics: [] });
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [selectedIndex, setSelectedIndex] = useState(-1);
  const inputRef = useRef(null);
  const dropdownRef = useRef(null);
  const debounceRef = useRef(null);

  const allResults = [...results.topics, ...results.works];
  const totalResults = results.topics.length + results.works.length;

  const handleSearch = useCallback(async (searchQuery) => {
    if (searchQuery.length < 2) {
      setResults({ works: [], topics: [] });
      return;
    }

    setLoading(true);
    try {
      const data = await searchService.search(searchQuery);
      setResults({
        works: data.works || [],
        topics: data.topics || [],
      });
    } catch (error) {
      console.error('Erro na busca:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (debounceRef.current) {
      clearTimeout(debounceRef.current);
    }

    debounceRef.current = setTimeout(() => {
      handleSearch(query);
    }, 300);

    return () => {
      if (debounceRef.current) {
        clearTimeout(debounceRef.current);
      }
    };
  }, [query, handleSearch]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleKeyDown = (e) => {
    if (!isOpen || allResults.length === 0) return;

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        setSelectedIndex((prev) => Math.min(prev + 1, allResults.length - 1));
        break;
      case 'ArrowUp':
        e.preventDefault();
        setSelectedIndex((prev) => Math.max(prev - 1, 0));
        break;
      case 'Enter':
        e.preventDefault();
        if (selectedIndex >= 0 && allResults[selectedIndex]) {
          handleSelect(allResults[selectedIndex]);
        }
        break;
      case 'Escape':
        setIsOpen(false);
        setSelectedIndex(-1);
        break;
    }
  };

  const handleSelect = (item) => {
    if (onSelect) {
      onSelect(item);
    }
    setIsOpen(false);
    setQuery('');
    setResults({ works: [], topics: [] });
    setSelectedIndex(-1);
  };

  return (
    <div className="search-bar" ref={dropdownRef}>
      <div className="search-bar__input-wrapper">
        <span className="search-bar__icon">🔍</span>
        <input
          ref={inputRef}
          type="text"
          value={query}
          onChange={(e) => {
            setQuery(e.target.value);
            setIsOpen(true);
            setSelectedIndex(-1);
          }}
          onFocus={() => setIsOpen(true)}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          className="search-bar__input"
          aria-label="Buscar"
          aria-expanded={isOpen && totalResults > 0}
        />
        {loading && <span className="search-bar__loading" />}
        {query && !loading && (
          <button
            className="search-bar__clear"
            onClick={() => {
              setQuery('');
              setResults({ works: [], topics: [] });
              inputRef.current?.focus();
            }}
            aria-label="Limpar busca"
          >
            ✕
          </button>
        )}
      </div>

      {isOpen && query.length >= 2 && (
        <div className="search-bar__dropdown">
          {totalResults === 0 && !loading ? (
            <div className="search-bar__empty">
              <span>Nenhum resultado para "{query}"</span>
            </div>
          ) : (
            <>
              {results.topics.length > 0 && (
                <div className="search-bar__section">
                  <div className="search-bar__section-title">
                    📝 Tópicos ({results.topics.length})
                  </div>
                  {results.topics.slice(0, 5).map((topic, index) => (
                    <div
                      key={`topic-${topic.id}`}
                      className={`search-bar__item ${selectedIndex === index ? 'search-bar__item--selected' : ''}`}
                      onClick={() => handleSelect({ ...topic, type: 'topic' })}
                      onMouseEnter={() => setSelectedIndex(index)}
                    >
                      <div className="search-bar__item-content">
                        <span
                          className="search-bar__item-title"
                          dangerouslySetInnerHTML={{ __html: sanitizeHtml(topic.title_highlighted || topic.title) }}
                        />
                        {topic.work && (
                          <span className="search-bar__item-badge">
                            📚 {topic.work.title}
                          </span>
                        )}
                      </div>
                      <span className="search-bar__item-type">Tópico</span>
                    </div>
                  ))}
                </div>
              )}

              {results.works.length > 0 && (
                <div className="search-bar__section">
                  <div className="search-bar__section-title">
                    📚 Obras ({results.works.length})
                  </div>
                  {results.works.slice(0, 3).map((work, index) => {
                    const idx = results.topics.length + index;
                    return (
                      <div
                        key={`work-${work.id}`}
                        className={`search-bar__item ${selectedIndex === idx ? 'search-bar__item--selected' : ''}`}
                        onClick={() => handleSelect({ ...work, type: 'work' })}
                        onMouseEnter={() => setSelectedIndex(idx)}
                      >
                      <div className="search-bar__item-content">
                        <span
                          className="search-bar__item-title"
                          dangerouslySetInnerHTML={{ __html: sanitizeHtml(work.title_highlighted || work.title) }}
                        />
                          {work.type && (
                            <span className="search-bar__item-badge">
                              {getTypeIcon(work.type)} {work.type}
                            </span>
                          )}
                        </div>
                        <span className="search-bar__item-type">Obra</span>
                      </div>
                    );
                  })}
                </div>
              )}

              {totalResults > 8 && (
                <div className="search-bar__footer">
                  <span>Pressione Enter para ver mais resultados</span>
                </div>
              )}
            </>
          )}
        </div>
      )}
    </div>
  );
}

function getTypeIcon(type) {
  const icons = { book: '📚', manga: '🈶', anime: '🎌', comic: '💥', hq: '🦸' };
  return icons[type] || '📖';
}
