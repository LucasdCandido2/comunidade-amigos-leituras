import api from './api';

export const registrationRequestService = {
  getAll: async () => {
    const response = await api.get('/registration-requests');
    return response;
  },

  approve: async (id, roleId = null) => {
    const response = await api.post(`/registration-requests/${id}/approve`, {
      role_id: roleId,
    });
    return response;
  },

  reject: async (id) => {
    const response = await api.post(`/registration-requests/${id}/reject`);
    return response;
  },

  create: async (data) => {
    const response = await api.post('/registration-request', data);
    return response;
  },
};