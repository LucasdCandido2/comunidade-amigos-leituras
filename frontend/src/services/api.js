import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: false,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// Interceptor: injeta Bearer token automaticamente em todas as requisições
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Interceptor de resposta: trata erros de autenticação globalmente
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Token inválido ou expirado — limpar localStorage
            localStorage.removeItem('token');
        }
        return Promise.reject(error);
    }
);

export default api;