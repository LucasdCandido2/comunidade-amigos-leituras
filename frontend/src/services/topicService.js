import api from './api';

export const topicService = {
    // Listar todos os tópicos
    getAll: async () => {
        const response = await api.get('/topics');
        return response.data;
    },

    // Buscar um tópico específico
    getById: async (id) => {
        const response = await api.get(`/topics/${id}`);
        return response.data;
    },

    // Criar novo tópico
    create: async (topicData) => {
        const response = await api.post('/topics', topicData);
        return response.data;
    },

    // Atualizar tópico
    update: async (id, topicData) => {
        const response = await api.put(`/topics/${id}`, topicData);
        return response.data;
    },

    // Deletar tópico
    delete: async (id) => {
        const response = await api.delete(`/topics/${id}`);
        return response.data;
    }
};