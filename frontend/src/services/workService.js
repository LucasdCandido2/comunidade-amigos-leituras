import api from './api';

export const workService = {
    // Lista paginada de obras (para scroll infinito)
    list: async ({ pageParam = 1, search = '', type = null }) => {
        let url = `/works?all=true&page=${pageParam}&per_page=50`;
        if (search) {
            url += `&search=${encodeURIComponent(search)}`;
        }
        if (type) {
            url += `&type=${type}`;
        }
        const response = await api.get(url);
        return {
            data: response.data.data || response.data,
            nextPage: response.data.next_page_url ? pageParam + 1 : undefined,
            total: response.data.total || 0,
        };
    },

    // Todas as obras — para gerenciamento (WorkEditor)
    // Agora suporta busca e paginação
    listAll: async (search = '', type = null, page = 1) => {
        let url = `/works?all=true&page=${page}&per_page=50`;
        if (search) {
            url += `&search=${encodeURIComponent(search)}`;
        }
        if (type) {
            url += `&type=${type}`;
        }
        const response = await api.get(url);
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
    },

    // Buscar em fontes externas (Jikan API)
    searchExternal: async (query, type = 'anime', source = 'jikan') => {
        const response = await api.get(`/works/search?q=${encodeURIComponent(query)}&type=${type}&source=${source}`);
        return response.data;
    },

    // Buscar detalhes de fonte externa
    fetchExternal: async (externalId, type) => {
        const response = await api.get(`/works/fetch-external?external_id=${externalId}&type=${type}`);
        return response.data;
    },

    // Sincronizar obra com fonte externa
    syncExternal: async (id) => {
        const response = await api.post(`/works/${id}/sync`);
        return response.data;
    },

    // Categorias
    getCategories: async () => {
        const response = await api.get('/categories');
        return response.data;
    },

    getWorksByCategory: async (categoryId) => {
        const response = await api.get(`/categories/${categoryId}/works`);
        return response.data;
    },

    assignCategories: async (workId, categoryIds) => {
        const response = await api.post(`/works/${workId}/categories`, { category_ids: categoryIds });
        return response.data;
    },

    getWorksByLetter: async (letter, type = null) => {
        let url = `/works/letter/${encodeURIComponent(letter)}`;
        if (type) url += `?type=${type}`;
        const response = await api.get(url);
        return response.data;
    },

    getAvailableLetters: async (type = null) => {
        let url = '/works/letters';
        if (type) url += `?type=${type}`;
        const response = await api.get(url);
        return response.data;
    }
};