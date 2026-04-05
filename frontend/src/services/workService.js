import api from './api';

export const workService = {
    // Todas as obras — para gerenciamento (WorkEditor)
    list: async () => {
        const response = await api.get('/works?all=true');
        return response.data;
    },

    // Top 10 por ranking bayesiano — para exibição pública (WorksRanking, CreateTopic)
    getAll: async () => {
        const response = await api.get('/works/top');
        return response.data;
    },

    // Criar nova obra
    create: async (workData) => {
        const response = await api.post('/works', workData);
        return response.data;
    },

    // Buscar obra por ID
    getById: async (id) => {
        const response = await api.get(`/works/${id}`);
        return response.data;
    },

    // Atualizar obra
    update: async (id, workData) => {
        const response = await api.put(`/works/${id}`, workData);
        return response.data;
    },

    // Deletar obra
    delete: async (id) => {
        await api.delete(`/works/${id}`);
    }
};