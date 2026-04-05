import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: false,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

const requestCache = new Map();
const CACHE_DURATION = 30000;

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

const cacheKey = (config) => {
    return `${config.method}:${config.url}:${JSON.stringify(config.params || {})}`;
};

const getCachedResponse = (key) => {
    const cached = requestCache.get(key);
    if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
        return cached.response;
    }
    requestCache.delete(key);
    return null;
};

const setCacheResponse = (key, response) => {
    requestCache.set(key, { response, timestamp: Date.now() });
};

export const clearApiCache = () => {
    requestCache.clear();
};

export const invalidateCache = (pattern) => {
    for (const key of requestCache.keys()) {
        if (key.includes(pattern)) {
            requestCache.delete(key);
        }
    }
};

export default api;