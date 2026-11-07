import { readFile, readdir, stat } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { gzipSync } from "node:zlib";

const themeRoot = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  "..",
);
const distRoot = path.join(themeRoot, "dist");
const manifestPath = path.join(distRoot, "manifest.json");
const requiredEntries = ["resources/js/app.js", "resources/js/editor.js"];
const forbiddenRasterExtensions = new Set([".jpg", ".jpeg", ".png"]);

async function collectFiles(directory) {
  const entries = await readdir(directory, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const entryPath = path.join(directory, entry.name);

    if (entry.isDirectory()) {
      files.push(...(await collectFiles(entryPath)));
    } else if (entry.isFile()) {
      files.push(entryPath);
    }
  }

  return files;
}

const manifest = JSON.parse(await readFile(manifestPath, "utf8"));
const imageManifest = JSON.parse(await readFile(path.join(distRoot, "images", "manifest.json"), "utf8"));
for (const asset of Object.values(imageManifest)) {
  if (typeof asset !== "string" || !asset.startsWith("images/") || asset.includes("..")) {
    throw new Error("Invalid image manifest path.");
  }
  await stat(path.join(distRoot, asset));
}
const builtAssets = new Set();

for (const entryName of requiredEntries) {
  const entry = manifest[entryName];

  if (!entry) {
    throw new Error(`Production manifest is missing ${entryName}.`);
  }

  if (entry.file) {
    builtAssets.add(entry.file);
  }

  for (const stylesheet of entry.css ?? []) {
    builtAssets.add(stylesheet);
  }
}

if (![...builtAssets].some((asset) => asset.endsWith(".css"))) {
  throw new Error("Production manifest contains no compiled CSS asset.");
}

for (const asset of builtAssets) {
  await stat(path.join(distRoot, asset));
}

const distFiles = await collectFiles(distRoot);
const legacyRaster = distFiles.find((file) =>
  forbiddenRasterExtensions.has(path.extname(file).toLowerCase()),
);

if (legacyRaster) {
  throw new Error(
    `Unconverted raster image found in production output: ${path.relative(distRoot, legacyRaster)}`,
  );
}

if (distFiles.some((file) => path.basename(file) === "hot")) {
  throw new Error("dist/hot must not be present in a production build.");
}

if (distFiles.some((file) => path.extname(file) === ".map")) {
  throw new Error("Source maps must not be present in the production build.");
}

console.log("Production assets verified:");

for (const asset of [...builtAssets].sort()) {
  const contents = await readFile(path.join(distRoot, asset));
  const gzipBytes = gzipSync(contents).byteLength;
  console.log(`- ${asset}: ${contents.byteLength} bytes (${gzipBytes} bytes gzip)`);
}

console.log("- manifest entries present");
console.log("- no development hot marker present");
console.log("- no production source maps present");
console.log("- no unconverted JPEG or PNG files in dist");
