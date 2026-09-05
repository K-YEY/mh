#!/usr/bin/env node
const fs = require('fs')
const path = require('path')

const repoRoot = path.resolve(__dirname, '..')
const backupDir = path.join(repoRoot, '.codemod', 'backups-shallow')
if (!fs.existsSync(backupDir)) fs.mkdirSync(backupDir, { recursive: true })

function collectVueFiles(dir) {
  const results = []
  const entries = fs.readdirSync(dir, { withFileTypes: true })
  for (const e of entries) {
    const full = path.join(dir, e.name)
    if (e.isDirectory()) results.push(...collectVueFiles(full))
    else if (e.isFile() && full.endsWith('.vue')) results.push(full)
  }
  return results
}

function detectVariant(classAttr) {
  if (!classAttr) return null
  if (/shadcn-btn-primary/.test(classAttr)) return 'primary'
  if (/shadcn-btn-secondary/.test(classAttr)) return 'secondary'
  if (/shadcn-btn-ghost/.test(classAttr)) return 'ghost'
  if (/shadcn-btn-outline/.test(classAttr)) return 'outline'
  return null
}

function replaceButtons(content) {
  const regex = /<button([^>]*)class=("|')([^"']*shadcn-btn-[^"']*)("|')([^>]*)>([\s\S]*?)<\/button>/gi
  return content.replace(regex, (m, attrs1, q1, classAttr, q2, attrs2, inner) => {
    const variant = detectVariant(classAttr)
    const props = variant ? ` variant=\"${variant}\"` : ''
    return `<UiButton${attrs1}${props}${attrs2}>${inner}</UiButton>`
  })
}

function replaceInputs(content) {
  const regex = /<input([^>]*?)class=("|')([^"']*shadcn-input[^"']*)("|')([^>]*)\/?>/gi
  return content.replace(regex, (m, attrs1, q1, classAttr, q2, attrs2) => {
    // Maintain attributes but remove class attribute (UiInput will apply class)
    // Merge attrs1 and attrs2
    const merged = (attrs1 + ' ' + attrs2).replace(/\s+/g, ' ').trim()
    return `<UiInput ${merged} />`
  })
}

function replaceSelects(content) {
  const regex = /<select([^>]*)class=("|')([^"']*shadcn-input[^"']*)("|')([^>]*)>/gi
  return content.replace(regex, (m, attrs1, q1, classAttr, q2, attrs2) => {
    const merged = (attrs1 + ' ' + attrs2).replace(/\s+/g, ' ').trim()
    return `<UiSelect ${merged}>`
  }).replace(/<\/select>/g, '</UiSelect>')
}

function processFile(file) {
  const content = fs.readFileSync(file, 'utf8')
  let changed = content
  changed = replaceButtons(changed)
  changed = replaceInputs(changed)
  changed = replaceSelects(changed)
  if (changed !== content) {
    const rel = path.relative(repoRoot, file).replace(/\\/g, '/')
    const backupPath = path.join(backupDir, rel + '.orig')
    fs.mkdirSync(path.dirname(backupPath), { recursive: true })
    fs.writeFileSync(backupPath, content, 'utf8')
    fs.writeFileSync(file, changed, 'utf8')
    return rel
  }
  return null
}

function main() {
  const files = collectVueFiles(path.join(repoRoot, 'resources', 'js'))
  const applied = []
  for (const f of files) {
    try {
      const r = processFile(f)
      if (r) applied.push(r)
    } catch (e) {
      console.error('fail', f, e && e.message)
    }
  }
  console.log('Applied shallow codemod to', applied.length, 'files')
  applied.forEach(f => console.log('- ' + f))
}

main()
