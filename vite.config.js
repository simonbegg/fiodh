import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import FullReload from "vite-plugin-full-reload";
import { optimizeImages } from "./scripts/optimize-images.mjs";

const rootDir = path.dirname(fileURLToPath(import.meta.url));
const hotFile = path.resolve(rootDir, "dist/hot");
const devHost = process.env.VITE_DEV_HOST || "localhost";
const devPort = Number(process.env.VITE_DEV_PORT || 5173);
const devServer = process.env.VITE_DEV_ORIGIN || `http://${devHost}:${devPort}`;

function wordpressHotFile() {
  return {
    name: "wordpress-hot-file",

    configureServer(server) {
      fs.mkdirSync(path.dirname(hotFile), { recursive: true });
      fs.writeFileSync(hotFile, devServer);

      server.httpServer?.once("close", () => {
        fs.rmSync(hotFile, { force: true });
      });
    },
  };
}

function watchThemeImages() {
  const imagesDir = path.resolve(rootDir, "resources/images");
  let pending = Promise.resolve();
  let timer;
  const queueBuild = (server, reload = true) => {
    pending = pending.catch(() => {}).then(async () => {
      await optimizeImages();
      if (reload) server.ws.send({ type: "full-reload" });
    }).catch((error) => server.config.logger.error(`Image optimization failed: ${error.message}`));
    return pending;
  };
  return {
    name: "watch-theme-images",
    apply: "serve",
    async configureServer(server) {
      await queueBuild(server, false);
      server.watcher.add(imagesDir);
      for (const event of ["add", "change", "unlink"]) {
        server.watcher.on(event, (file) => {
          const relative = path.relative(imagesDir, path.resolve(file));
          if (!relative || relative.startsWith("..") || path.isAbsolute(relative)) return;
          clearTimeout(timer);
          timer = setTimeout(() => queueBuild(server), 200);
        });
      }
      server.httpServer?.once("close", () => clearTimeout(timer));
    },
  };
}

export default defineConfig({
  plugins: [
    tailwindcss(),

    FullReload([
      "resources/views/**/*.twig",
      "src/**/*.php",
      "blocks/**/*.php",
      "blocks/**/block.json",
      "acf-json/**/*.json",
      "*.php",
      "theme.json",
    ]),

    wordpressHotFile(),
    watchThemeImages(),
  ],

  input: {
    app: path.resolve(rootDir, "resources/js/app.js"),
    editor: path.resolve(rootDir, "resources/js/editor.js"),
  },

  build: {
    outDir: "dist",
    emptyOutDir: true,
    manifest: "manifest.json",
    minify: "oxc",
    cssMinify: "lightningcss",
    sourcemap: false,
    reportCompressedSize: true,
  },

  server: {
    host: devHost,
    port: devPort,
    strictPort: true,
    origin: devServer,
    cors: { origin: /^https?:\/\/(localhost|127\.0\.0\.1|[^/]+\.local)(:\d+)?$/ },
  },
});
