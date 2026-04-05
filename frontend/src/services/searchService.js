import api from './api';

export const searchService = {
  async search(query, type = 'all', options = {}) {
    const params = new URLSearchParams({ q: query, type });
    
    if (options.work_type) params.append('work_type', options.work_type);
    if (options.sort_by) params.append('sort_by', options.sort_by);
    if (options.highlight !== undefined) params.append('highlight', options.highlight);

    const response = await api.get(`/search?${params.toString()}`);
    return response.data;
  },
};
