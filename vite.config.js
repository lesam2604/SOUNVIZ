import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

const getAllFiles = (dirPath, arrayOfFiles = []) => {
  const files = fs.readdirSync(dirPath);

  files.forEach((file) => {
    const full = path.join(dirPath, file);
    if (fs.statSync(full).isDirectory()) {
      arrayOfFiles = getAllFiles(full, arrayOfFiles);
    } else {
      arrayOfFiles.push(full);
    }
  });

  return arrayOfFiles;
};

// On prend tous les .js / .css sous resources/
const resourceFiles = getAllFiles('resources').filter(
  (file) => file.endsWith('.js') || file.endsWith('.css')
);

export default defineConfig({
  plugins: [
    laravel({
      // tu sers depuis public_html (pas public)
      publicDirectory: 'public_html',
      input: resourceFiles,
      refresh: true,
    }),
  ],

  // 🔌 Dev server (Windows/WAMP)
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://127.0.0.1:5173',
    hmr: {
      host: '127.0.0.1',
      port: 5173,
      protocol: 'ws',
    },
    // Windows: watcher en polling pour éviter les ratés de reload
    watch: {
      usePolling: true,
      interval: 150,
    },
  },

  // 🔎 Résolution pratique d’imports
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources'),
    },
  },

  // ⚡ Pré-optimisation (vite) — utile si tu utilises jQuery/DataTables
  optimizeDeps: {
    include: [
      'jquery',
      'datatables.net',
      'datatables.net-bs5',
    ],
  },

  // 🏗️ Build vers public_html/build + manifest (cohérent avec laravel-vite-plugin)
  build: {
    outDir: 'public_html/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resourceFiles,
    },
  },
});
