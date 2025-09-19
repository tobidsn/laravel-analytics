import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { resolve } from 'path'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'

export default defineConfig({
  plugins: [react()],
  
  // Build configuration for Laravel package distribution
  build: {
    // Output directory for built assets (will be included in package)
    outDir: 'public/vendor/analytics',
    
    // Clear output directory before build
    emptyOutDir: true,
    
    // Generate manifest for Laravel asset loading
    manifest: true,
    
    // Rollup options for optimization
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'resources/js/app.jsx')
      },
      output: {
        // Use IIFE format for direct browser loading without module type
        format: 'iife',
        // Generate hashed filenames for cache busting
        entryFileNames: 'app-[hash].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'app-[hash].css'
          }
          return 'assets/[name]-[hash][extname]'
        },
        // Global variable name for IIFE
        name: 'AnalyticsDashboard'
      }
    },
    
    // Source maps for debugging (disabled for production)
    sourcemap: false,
    
    // Minification
    minify: 'terser',
    
    // Target modern browsers
    target: 'es2015',
    
    // CSS code splitting
    cssCodeSplit: false
  },
  
  // Disable public directory copying to avoid recursion
  publicDir: false,
  
  // Development server configuration
  server: {
    port: 5173,
    host: true,
    hmr: {
      port: 5173
    }
  },
  
  // Path resolution
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '@components': resolve(__dirname, 'resources/js/Components'),
      '@analytics': resolve(__dirname, 'resources/js/Components/Analytics')
    }
  },
  
  // CSS processing
  css: {
    postcss: {
      plugins: [
        tailwindcss,
        autoprefixer
      ]
    }
  },
  
  // Base URL for assets (will be served from Laravel)
  base: '/vendor/analytics/',
  
  // Define global constants
  define: {
    __APP_VERSION__: JSON.stringify(process.env.npm_package_version || '1.0.0')
  }
})