import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

// __dirname for ES-Module
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Source: node_modules/monaco-editor/min/vs
const srcDir = path.join(__dirname, "../../node_modules/monaco-editor/min/vs");
// Target: assets/vs im Plugin
const destDir = path.join(__dirname, "../../assets/vs");

// copy recursive
function copyRecursiveSync(src, dest) {
  if (!fs.existsSync(src)) return;

  if (fs.statSync(src).isDirectory()) {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true });
    }
    for (const file of fs.readdirSync(src)) {
      copyRecursiveSync(path.join(src, file), path.join(dest, file));
    }
  } else {
    fs.copyFileSync(src, dest);
  }
}

console.log(`Copy Monaco from '${srcDir}' to '${destDir}'`);
copyRecursiveSync(srcDir, destDir);
console.log("Monaco-Worker copied!");
