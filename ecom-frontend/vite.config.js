import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  
  return {
    plugins: [vue()],
    server: {
      proxy: {
        '/csrf-token': {
          target: 'http://localhost:8101',
          changeOrigin: true
        },
        '/api': {
          target: 'http://localhost:8101',
          changeOrigin: true,
          rewrite: (path) => path.replace(/^\/api/, '')
        },
        '/broadcasting': {
          target: 'http://localhost:8101',
          changeOrigin: true
        }
      }
    }
  }
})