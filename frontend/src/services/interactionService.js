import api from './api';

export const interactionService = {
    //Listar interações de um tópico
    getByTopic: async (topicId) => {
        const response = await api.get(`/topics/${topicId}/interactions`);
        return response.data;
    },

    //Criar nova interação (comentário) em um tópico
    create: async (topicId, interactionData) => {
        const response = await api.post(`/topics/${topicId}/interactions`, interactionData);
        return response.data;
    }
};
