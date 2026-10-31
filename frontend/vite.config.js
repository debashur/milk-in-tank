import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

// Браузер ходит на адрес Vite с префиксом /api.
// Vite пересылает запрос на Yii и убирает этот префикс: /api/tanks → http://127.0.0.1:8080/tanks.
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8080',
        rewrite: (path) => path.replace(/^\/api/, ''),
      },
    },
  },
})
