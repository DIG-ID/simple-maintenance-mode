// vite.config.mjs — build and dev server for the plugin's stylesheets.
//
//   npm run prod  → dist/ with hashed files and dist/.vite/manifest.json
//   npm run dev   → dev server with HMR; WordPress loads from it while it runs
//
// Based on the migrate-to-vite skill template (dig.id).

import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { defineConfig, loadEnv } from 'vite';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

// Entry points — resolved by key in dist/.vite/manifest.json from
// includes/smm-vite.php (e.g. smm_vite_enqueue_style( 'smm', 'public/sass/smm-public.sass' )).
const entries = [
  'public/sass/smm-public.sass', // Maintenance page.
  'assets/sass/smm-common.sass', // Admin bar button.
];

const outDir = 'dist';

// File the dev server writes on start (and removes on exit) so WordPress
// knows to load assets from it instead of the build. Never committed.
const hotFile = path.resolve(outDir, 'hot');

// Local by WPEngine certificates, reused so the dev server runs on HTTPS
// with the same trusted cert as the site (no mixed-content errors).
const certPath = path.join(
  os.homedir(),
  os.platform() === 'win32'
    ? 'AppData/Roaming/Local/run/router/nginx/certs'
    : 'Library/Application Support/Local/run/router/nginx/certs'
);

/**
 * Returns the HTTPS options for the dev server, or false when the Local
 * certificates for the host are not found (falls back to plain HTTP).
 *
 * @param {string} host Local dev hostname (e.g. testing.digid).
 * @returns {{key: Buffer, cert: Buffer}|false}
 */
function localHttps(host) {
  const key = path.join(certPath, `${host}.key`);
  const cert = path.join(certPath, `${host}.crt`);

  if (!fs.existsSync(key) || !fs.existsSync(cert)) {
    console.warn(`\n[vite] Local certificates for "${host}" not found in ${certPath} — dev server falls back to HTTP.\n`);
    return false;
  }

  return { key: fs.readFileSync(key), cert: fs.readFileSync(cert) };
}

/**
 * WordPress integration: writes/removes the hot file and triggers a full
 * page reload whenever a PHP file changes.
 *
 * @returns {import('vite').Plugin}
 */
function wordpress() {
  return {
    name: 'smm-wordpress',
    apply: 'serve',
    configureServer(server) {
      const { origin } = server.config.server;

      server.httpServer?.once('listening', () => {
        fs.mkdirSync(path.dirname(hotFile), { recursive: true });
        fs.writeFileSync(hotFile, origin);
      });

      const clean = () => {
        if (fs.existsSync(hotFile)) {
          fs.rmSync(hotFile);
        }
      };

      process.on('exit', clean);
      ['SIGINT', 'SIGTERM', 'SIGHUP'].forEach((signal) => {
        process.on(signal, () => {
          clean();
          process.exit();
        });
      });

      server.watcher.on('change', (file) => {
        if (file.endsWith('.php')) {
          server.ws.send({ type: 'full-reload', path: '*' });
        }
      });
    },
  };
}

export default defineConfig(({ mode }) => {
  // Local dev hostname and port — override per machine in an untracked
  // .env.local (gitignored):  DEV_HOST=control-client.digid  DEV_PORT=5174
  const env = loadEnv(mode, process.cwd(), '');
  const host = env.DEV_HOST || 'testing.digid';
  const port = Number(env.DEV_PORT) || 5173;

  const https = localHttps(host);
  const origin = `${https ? 'https' : 'http'}://${host}:${port}`;

  return {
    // Relative base so url() references inside the compiled CSS resolve
    // against the build folder wherever the plugin lives.
    base: './',
    publicDir: false,

    plugins: [wordpress()],

    dev: {
      sourcemap: { css: true },
    },

    css: {
      preprocessorOptions: {
        sass: {
          silenceDeprecations: ['import'],
          quietDeps: true,
        },
        scss: {
          silenceDeprecations: ['import'],
          quietDeps: true,
        },
      },
      postcss: {
        plugins: [tailwindcss('./tailwind.config.js'), autoprefixer()],
      },
    },

    server: {
      host: '127.0.0.1',
      port,
      strictPort: true,
      https,
      origin,
      cors: { origin: [`https://${host}`, `http://${host}`] },
      allowedHosts: [host],
      hmr: { host },
      watch: {
        ignored: ['**/vendor/**', '**/node_modules/**', `**/${outDir}/**`],
      },
    },

    build: {
      outDir,
      emptyOutDir: true,
      manifest: true,
      sourcemap: mode !== 'production',
      assetsInlineLimit: (file) => (file.endsWith('.svg') ? false : undefined),
      rolldownOptions: {
        input: entries,
        output: {
          entryFileNames: 'js/[name]-[hash].js',
          chunkFileNames: 'js/[name]-[hash].js',
          assetFileNames: ({ names }) =>
            names.some((name) => name.endsWith('.css'))
              ? 'css/[name]-[hash][extname]'
              : 'assets/[name]-[hash][extname]',
        },
      },
    },
  };
});
