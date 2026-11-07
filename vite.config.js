import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import FullReload from "vite-plugin-full-reload";

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
