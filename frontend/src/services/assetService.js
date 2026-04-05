import api from './api';

export const assetService = {
    upload: async (file, topicId = null) => {
        const formData = new FormData();
        formData.append('file', file);
        if (topicId) {
            formData.append('topic_id', topicId);
        }

        const response = await api.post('/assets', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    },

    delete: async (id) => {
        const response = await api.delete(`/assets/${id}`);
        return response.data;
    },

    getByTopic: async (topicId) => {
        const response = await api.get(`/topics/${topicId}/assets`);
        return response.data;
    },

    getAllowedTypes: async () => {
        const response = await api.get('/assets/allowed-types');
        return response.data;
    },

    uploadInline: async (file) => {
        const formData = new FormData();
        formData.append('file', file);

        const response = await api.post('/assets/upload-inline', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    },

    getUrl: (id) => `/api/assets/${id}`,
};
