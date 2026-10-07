import {defineConfig} from 'vite';
export default defineConfig({
  base: './',
  build: {
    outDir: 'theme/assets/bundle',
    emptyOutDir: true,
    manifest: 'manifest.json',
    rollupOptions: {
      input: 'frontend/app.js',
      output: {entryFileNames: 'app-[hash].js', chunkFileNames: '[name]-[hash].js', assetFileNames: '[name]-[hash][extname]'}
    }
  }
});
