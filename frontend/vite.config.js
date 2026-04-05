import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 3000,
    strictPort: true,
    watch: {
      usePolling: true,
    },
    proxy: {
      // Toda chamada a /api é encaminhada internamente para o backend
      // O browser só enxerga localhost:3000 — sem cross-origin, sem preflight
      '/api': {
        target: 'http://web:80',
        changeOrigin: true,
        secure: false,
      },
    },
  },
});