import React, { useState } from "react";
import { authService } from "../services/authService";

export function Register({ onRegister }) {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        
        if (password.length < 6) {
            setError('A senha deve ter pelo menos 6 caracteres');
            return;
        }
        
        if (password !== passwordConfirmation) {
            setError('As senhas não coincidem');
            return;
        }
        
        setLoading(true);
        try {
            await authService.register({ name, email, password, password_confirmation: passwordConfirmation });
            await onRegister();
        } catch (err) {
            const msg = err.response?.data?.message || err.response?.data?.error || 'Erro ao registrar. Tente novamente.';
            setError(Array.isArray(msg) ? msg.join(', ') : msg);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="auth-container">
            <div className="auth-card card card--elevated">
                <div className="auth-card__header">
                    <div className="auth-card__icon">📚</div>
                    <h1 className="auth-card__title">Criar Conta</h1>
                    <p className="auth-card__subtitle">Junte-se à comunidade de leitores</p>
                </div>

                <form onSubmit={handleSubmit} className="auth-form">
                    {error && (
                        <div className="alert alert--error">
                            <span className="alert__icon">⚠️</span>
                            <span>{error}</span>
                        </div>
                    )}

                    <div className="form-group">
                        <label className="form-label">Nome</label>
                        <input
                            type="text"
                            className="form-control"
                            placeholder="Seu nome"
                            value={name}
                            onChange={e => setName(e.target.value)}
                            required
                            autoComplete="name"
                        />
                    </div>

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
                            placeholder="Mínimo 6 caracteres"
                            value={password}
                            onChange={e => setPassword(e.target.value)}
                            required
                            autoComplete="new-password"
                        />
                    </div>

                    <div className="form-group">
                        <label className="form-label">Confirmar Senha</label>
                        <input
                            type="password"
                            className="form-control"
                            placeholder="Repita a senha"
                            value={passwordConfirmation}
                            onChange={e => setPasswordConfirmation(e.target.value)}
                            required
                            autoComplete="new-password"
                        />
                    </div>

                    <button 
                        type="submit" 
                        className="btn btn--action btn--lg auth-form__submit"
                        disabled={loading}
                    >
                        {loading ? (
                            <>
                                <span className="spinner" style={{ width: '1rem', height: '1rem' }} />
                                Criando conta...
                            </>
                        ) : (
                            'Criar Conta'
                        )}
                    </button>
                </form>

                <div className="auth-card__footer">
                    <div className="auth-card__terms">
                        <span>Ao criar uma conta, você concorda com os termos da comunidade.</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
