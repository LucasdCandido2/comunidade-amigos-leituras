import api from './api';

export const roleService = {
    getAll: () => api.get('/roles'),
    getById: (id) => api.get(`/roles/${id}`),
    create: (data) => api.post('/roles', data),
    update: (id, data) => api.put(`/roles/${id}`, data),
    delete: (id) => api.delete(`/roles/${id}`),
    getPermissions: () => api.get('/permissions'),
    assignUser: (roleId, userId) => api.post(`/roles/${roleId}/assign-user`, { user_id: userId }),
    removeUser: (roleId, userId) => api.delete(`/roles/${roleId}/users/${userId}`),
};