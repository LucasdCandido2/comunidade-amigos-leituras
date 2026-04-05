import api from './api';

export const gamificationService = {
  async getStats() {
    const response = await api.get('/gamification/stats');
    return response.data;
  },

  async getLeaderboard() {
    const response = await api.get('/leaderboard');
    return response.data;
  },

  async getBadges() {
    const response = await api.get('/badges');
    return response.data;
  },
};
