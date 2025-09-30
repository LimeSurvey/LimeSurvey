import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

const appName = 'adminsidepanel'
const rootDir = path.resolve(__dirname)
const outputDir = process.env.NODE_ENV === 'production' ? 'build.min' : 'build'

export default defineConfig({
  plugins: [
    react({
      include: '**/*.{jsx,js}', // allow JSX in .js and .jsx
    }),
  ],
  base: './',
  build: {
    outDir: outputDir,
    sourcemap: process.env.NODE_ENV !== 'production',
    assetsDir: '',
    emptyOutDir: false,
    rollupOptions: {
      // only externalize your real globals, not React
      external: ['LS', 'jQuery', 'Pjax'],
      input: path.resolve(rootDir, 'src/adminsidepanelmain.jsx'),
      output: {
        format: 'iife',
        name: 'AdminsidepanelApp',
        entryFileNames: `js/${appName}.js`,
        chunkFileNames: `js/${appName}-[hash].js`,
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) return `css/${appName}.css`
          if (assetInfo.name.match(/\.(js|jsx|ts|tsx)$/))
            return `js/${appName}-[hash].js`
          return 'assets/[name]-[hash][extname]'
        },
        // no globals mapping for React needed anymore
        globals: {
          LS: 'LS',
          jQuery: 'jQuery',
          Pjax: 'Pjax',
        },
      },
    },
  },
})
