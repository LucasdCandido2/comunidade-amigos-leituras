import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'https://comunidade-amigos-leituras-production.up.railway.app/api';

const api = axios.create({
    baseURL: API_URL,
    withCredentials: false,
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
        }
        return Promise.reject(error);
    }
);

export default api;