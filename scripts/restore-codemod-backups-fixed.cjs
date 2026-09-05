#!/usr/bin/env node
const fs = require('fs')
const path = require('path')

const repoRoot = path.resolve(__dirname, '..')
const backupRoot = path.join(repoRoot, '.codemod', 'backups')

function collectBackups(dir) {
  const results = []
  const stack = [dir]
  while (stack.length) {
    const cur = stack.pop()
    for (const name of fs.readdirSync(cur)) {
      const full = path.join(cur, name)
      const stat = fs.statSync(full)
      if (stat.isDirectory()) stack.push(full)
      else if (stat.isFile() && name.endsWith('.orig')) results.push(full)
    }
  }
  return results
}

function restoreOne(backupPath) {
  const rel = path.relative(backupRoot, backupPath)
  const withoutOrig = rel.replace(/\.orig$/, '')
  const target = path.join(repoRoot, withoutOrig)
  const content = fs.readFileSync(backupPath, 'utf8')
  fs.mkdirSync(path.dirname(target), { recursive: true })
  fs.writeFileSync(target, content, 'utf8')
  return withoutOrig.replace(/\\/g, '/')
}

function main() {
  if (!fs.existsSync(backupRoot)) {
    console.error('No backups folder found at', backupRoot)
    process.exit(1)
  }
  const backups = collectBackups(backupRoot)
  const restored = []
  for (const b of backups) {
    try {
      restored.push(restoreOne(b))
    } catch (e) {
      console.error('fail restore', b, e && e.message)
    }
  }
  console.log('Restored', restored.length, 'files:')
  restored.forEach(f => console.log('- ' + f))
}

main()
