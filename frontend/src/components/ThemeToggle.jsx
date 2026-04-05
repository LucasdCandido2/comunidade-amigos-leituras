import React from 'react';
import { useTheme } from '../contexts/ThemeContext';

/**
 * ThemeToggle — Botão de alternância dark/light mode
 *
 * Props:
 *   variant: 'icon' | 'label' | 'full' (padrão: 'full')
 *   className: classes CSS adicionais
 */
export function ThemeToggle({ variant = 'full', className = '' }) {
    const { theme, isDark, toggleTheme } = useTheme();

    const icon  = isDark ? '☀️' : '🌙';
    const label = isDark ? 'Modo Claro' : 'Modo Escuro';
    const ariaLabel = `Alternar para ${label}`;

    if (variant === 'icon') {
        return (
            <button
                onClick={toggleTheme}
                className={`theme-toggle btn btn--ghost btn--icon ${className}`}
                aria-label={ariaLabel}
                title={ariaLabel}
            >
                <span className="theme-toggle__icon">{icon}</span>
            </button>
        );
    }

    if (variant === 'label') {
        return (
            <button
                onClick={toggleTheme}
                className={`theme-toggle ${className}`}
                aria-label={ariaLabel}
            >
                <span className="theme-toggle__icon">{icon}</span>
                <span>{label}</span>
            </button>
        );
    }

    // variant === 'full' (padrão)
    return (
        <button
            onClick={toggleTheme}
            className={`theme-toggle ${className}`}
            aria-label={ariaLabel}
            title={`Tema atual: ${theme === 'dark' ? 'Escuro' : 'Claro'}`}
        >
            <span className="theme-toggle__icon">{icon}</span>
            <span>{label}</span>
        </button>
    );
}
