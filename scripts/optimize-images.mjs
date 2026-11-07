import { mkdir, readdir, readFile, writeFile } from "node:fs/promises";
import { createHash } from "node:crypto";
import path from "node:path";
import { fileURLToPath } from "node:url";
import sharp from "sharp";

const themeRoot = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  "..",
);
const sourceRoot = path.join(themeRoot, "resources", "images");
const outputRoot = path.join(themeRoot, "dist", "images");
const rasterExtensions = new Set([".jpg", ".jpeg", ".png", ".webp"]);

async function collectFiles(directory) {
  const entries = await readdir(directory, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const entryPath = path.join(directory, entry.name);

    if (entry.isDirectory()) {
      files.push(...(await collectFiles(entryPath)));
    } else if (entry.isFile() && entry.name !== ".gitkeep") {
      files.push(entryPath);
    }
  }

  return files;
}

await mkdir(sourceRoot, { recursive: true });
await mkdir(outputRoot, { recursive: true });

const sourceFiles = await collectFiles(sourceRoot);
const outputNames = new Set();
const manifest = {};
let converted = 0;
let copied = 0;

for (const sourceFile of sourceFiles) {
  const relativePath = path.relative(sourceRoot, sourceFile);
  const extension = path.extname(relativePath).toLowerCase();

  if (rasterExtensions.has(extension)) {
    const webpPath = relativePath.slice(0, -extension.length) + ".webp";

    if (outputNames.has(webpPath)) {
      throw new Error(
        `Multiple source images resolve to dist/images/${webpPath}. Rename one source file.`,
      );
    }

    outputNames.add(webpPath);

    const bytes = await sharp(sourceFile)
      .rotate()
      .webp({ quality: 82, effort: 6, smartSubsample: true })
      .toBuffer();
    const hash = createHash("sha256").update(bytes).digest("hex").slice(0, 12);
    const hashedPath = webpPath.slice(0, -5) + `-${hash}.webp`;
    const outputFile = path.join(outputRoot, hashedPath);
    await mkdir(path.dirname(outputFile), { recursive: true });
    await writeFile(outputFile, bytes);
    manifest[relativePath.replaceAll(path.sep, "/")] = `images/${hashedPath.replaceAll(path.sep, "/")}`;

    converted += 1;
    continue;
  }

  if (extension === ".svg") {
    const bytes = await readFile(sourceFile);
    const hash = createHash("sha256").update(bytes).digest("hex").slice(0, 12);
    const hashedPath = relativePath.slice(0, -extension.length) + `-${hash}.svg`;
    const outputFile = path.join(outputRoot, hashedPath);
    await mkdir(path.dirname(outputFile), { recursive: true });
    await writeFile(outputFile, bytes);
    manifest[relativePath.replaceAll(path.sep, "/")] = `images/${hashedPath.replaceAll(path.sep, "/")}`;
    copied += 1;
  }
}

await writeFile(path.join(outputRoot, "manifest.json"), JSON.stringify(manifest, null, 2) + "\n");

console.log(
  `Theme images: ${converted} converted to WebP, ${copied} SVG files copied.`,
);
