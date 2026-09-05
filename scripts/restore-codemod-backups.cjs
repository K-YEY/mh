#!/usr/bin/env node
const fs = require('fs')
const path = require('path')

const repoRoot = path.resolve(__dirname, '..')
const backupRoot = path.join(repoRoot, '.codemod', 'backups')

function collectBackups(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true })
  const results = []
  for (const e of entries) {
    const full = path.join(dir, e.name)
    if (e.isDirectory()) results.push(...collectBackups(full))
    else if (e.isFile() && e.name.endsWith('.orig')) results.push(full)
  }
  return results
}

function restore(backupPath) {
  const rel = path.relative(backupRoot, backupPath).replace(/\\.orig$/, '')
  const target = path.join(repoRoot, rel)
  const dir = path.dirname(target)
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true })
  const content = fs.readFileSync(backupPath, 'utf8')
  fs.writeFileSync(target, content, 'utf8')
  return rel
}

function main() {
  if (!fs.existsSync(backupRoot)) {
    console.error('No backups found at', backupRoot)
    process.exit(1)
  }
  const backups = collectBackups(backupRoot)
  const restored = []
  for (const b of backups) {
    try {
      restored.push(restore(b))
    } catch (e) {
      console.error('Failed to restore', b, e && e.message)
    }
  }
  console.log('Restored', restored.length, 'files')
  restored.forEach(f => console.log('- ' + f))
}

main()
