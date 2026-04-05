import React, { useState } from "react";
import { authService } from "../services/authService";

export function Login({ onLogin }) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        setLoading(true);
        try {
            await authService.login({ email, password });
            await onLogin();
        } catch (err) {
            setError('Credenciais inválidas. Verifique seu email e senha.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="auth-container">
            <div className="auth-card card card--elevated">
                <div className="auth-card__header">
                    <div className="auth-card__icon">📚</div>
                    <h1 className="auth-card__title">Entrar</h1>
                    <p className="auth-card__subtitle">Bem-vindo de volta à comunidade</p>
                </div>

                <form onSubmit={handleSubmit} className="auth-form">
                    {error && (
                        <div className="alert alert--error">
                            <span className="alert__icon">⚠️</span>
                            <span>{error}</span>
                        </div>
                    )}

                    <div className="form-group">
                        <label className="form-label">Email</label>
                        <input
                            type="email"
                            className="form-control"
                            placeholder="seu@email.com"
                            value={email}
                            onChange={e => setEmail(e.target.value)}
                            required
                            autoComplete="email"
                        />
                    </div>

                    <div className="form-group">
                        <label className="form-label">Senha</label>
                        <input
                            type="password"
                            className="form-control"
                            placeholder="Sua senha"
                            value={password}
                            onChange={e => setPassword(e.target.value)}
                            required
                            autoComplete="current-password"
                        />
                    </div>

                    <button 
                        type="submit" 
                        className="btn btn--primary btn--lg auth-form__submit"
                        disabled={loading}
                    >
                        {loading ? (
                            <>
                                <span className="spinner" style={{ width: '1rem', height: '1rem' }} />
                                Entrando...
                            </>
                        ) : (
                            'Entrar'
                        )}
                    </button>
                </form>

                <div className="auth-card__footer">
                    <div className="auth-card__info">
                        <span className="auth-card__info-icon">💡</span>
                        <span>Use <code>teste1234@teste.com</code> e <code>teste12345</code> para testar</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
