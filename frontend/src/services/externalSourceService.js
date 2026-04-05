import api from './api';

export const externalSourceService = {
  async getAll() {
    const response = await api.get('/external-sources');
    return response.data;
  },

  async create(data) {
    const response = await api.post('/external-sources', data);
    return response.data;
  },

  async delete(id) {
    await api.delete(`/external-sources/${id}`);
  },
};
