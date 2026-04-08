import api from './api';

export const assetService = {
    upload: async (file, topicId = null) => {
        const formData = new FormData();
        formData.append('file', file);
        if (topicId) {
            formData.append('topic_id', topicId);
        }

        const response = await api.post('/assets', formData);
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
        
        const token = localStorage.getItem('token');
        
        const response = await fetch('/api/assets/upload-inline', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
            body: formData,
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Upload failed');
        }
        
        return response.json();
    },

    getUrl: (id) => `/api/assets/${id}`,
};
