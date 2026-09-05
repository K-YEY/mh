#!/usr/bin/env node
const fs = require('fs')
const path = require('path')

const repoRoot = path.resolve(__dirname, '..')
const previewDir = path.join(repoRoot, '.codemod', 'preview')
const backupDir = path.join(repoRoot, '.codemod', 'backups')
if (!fs.existsSync(backupDir)) fs.mkdirSync(backupDir, { recursive: true })

function collectPatches(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true })
  const results = []
  for (const e of entries) {
    const full = path.join(dir, e.name)
    if (e.isDirectory()) results.push(...collectPatches(full))
    else if (e.isFile() && e.name.endsWith('.patch')) results.push(full)
  }
  return results
}

function applyPatchFile(patchPath) {
  const raw = fs.readFileSync(patchPath, 'utf8')
  const lines = raw.split(/\r?\n/)
  if (lines.length < 2) return null
  const plusLine = lines[1]
  if (!plusLine.startsWith('+++ b/')) return null
  const rel = plusLine.replace('+++ b/', '').trim().replace(/\\/g, '/')
  const target = path.join(repoRoot, rel)

  const lastPlus = raw.lastIndexOf('\n+++\n')
  if (lastPlus === -1) return null
  const changed = raw.slice(lastPlus + '\n+++\n'.length)

  // backup original
  const backupPath = path.join(backupDir, rel + '.orig')
  const backupDirPath = path.dirname(backupPath)
  if (!fs.existsSync(backupDirPath)) fs.mkdirSync(backupDirPath, { recursive: true })
  if (fs.existsSync(target)) {
    fs.copyFileSync(target, backupPath)
  } else {
    fs.writeFileSync(backupPath, '', 'utf8')
  }

  // ensure target dir exists
  const targetDir = path.dirname(target)
  if (!fs.existsSync(targetDir)) fs.mkdirSync(targetDir, { recursive: true })

  fs.writeFileSync(target, changed, 'utf8')
  return rel
}

function main() {
  const patches = collectPatches(previewDir)
  const applied = []
  for (const p of patches) {
    try {
      const rel = applyPatchFile(p)
      if (rel) applied.push(rel)
    } catch (e) {
      console.error('Failed to apply', p, e && e.message)
    }
  }
  console.log('Applied patches to', applied.length, 'files')
  applied.forEach(f => console.log('- ' + f))
}

main()
