import React, { createContext, useContext, useEffect, useState } from 'react';

// ============================================================
// ThemeContext — Gerenciamento de tema (dark / light)
// ============================================================
// Uso:
//   const { theme, toggleTheme, isDark } = useTheme();
// ============================================================

const ThemeContext = createContext(null);

const STORAGE_KEY = 'cal-theme'; // cal = Comunidade Amigos Leituras

/**
 * Detecta o tema preferido do SO se não houver preferência salva.
 */
function getInitialTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'dark' || saved === 'light') return saved;

    // Respeitar preferência do sistema operacional
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
    return 'light';
}

/**
 * Aplica o tema ao elemento <html> via data-theme attribute.
 * Os tokens CSS em tokens.css respondem a [data-theme="dark|light"].
 */
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
}

export function ThemeProvider({ children }) {
    const [theme, setTheme] = useState(() => {
        const initial = getInitialTheme();
        applyTheme(initial);
        return initial;
    });

    useEffect(() => {
        applyTheme(theme);
        localStorage.setItem(STORAGE_KEY, theme);
    }, [theme]);

    // Escutar mudanças de preferência do SO (sem override manual do usuário)
    useEffect(() => {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = (e) => {
            // Só aplica mudança do SO se o usuário não tiver definido manualmente
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        };
        mq.addEventListener('change', handler);
        return () => mq.removeEventListener('change', handler);
    }, []);

    const toggleTheme = () => {
        setTheme(prev => prev === 'dark' ? 'light' : 'dark');
    };

    const setLightTheme = () => setTheme('light');
    const setDarkTheme  = () => setTheme('dark');

    const value = {
        theme,
        isDark:  theme === 'dark',
        isLight: theme === 'light',
        toggleTheme,
        setLightTheme,
        setDarkTheme,
    };

    return (
        <ThemeContext.Provider value={value}>
            {children}
        </ThemeContext.Provider>
    );
}

/**
 * Hook para acessar o contexto de tema.
 * @throws se usado fora de <ThemeProvider>
 */
export function useTheme() {
    const ctx = useContext(ThemeContext);
    if (!ctx) {
        throw new Error('useTheme deve ser usado dentro de <ThemeProvider>');
    }
    return ctx;
}
