#!/usr/bin/env node
const fs = require('fs')
const path = require('path')
const glob = require('glob')

const repoRoot = path.resolve(__dirname, '..')
const outDir = path.join(repoRoot, '.codemod', 'preview')
if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true })

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
  let m
  let out = content
  let count = 0
  while ((m = regex.exec(content)) !== null) {
    count++
    const before = m[0]
    const attrs1 = m[1] || ''
    const classAttr = m[3] || ''
    const attrs2 = m[5] || ''
    const inner = m[6] || ''
    const variant = detectVariant(classAttr)
    const props = variant ? ` variant=\"${variant}\"` : ''
    const newTag = `<UiButton${attrs1}${props}${attrs2}>${inner}</UiButton>`
    out = out.replace(before, newTag)
  }
  return { out, count }
}

function replaceInputs(content) {
  // replace <input ... class="...shadcn-input..." /> with <UiInput ... />
  const regex = /<input([^>]*?)class=("|')([^"']*shadcn-input[^"']*)("|')([^>]*)\/?>/gi
  let m
  let out = content
  let count = 0
  while ((m = regex.exec(content)) !== null) {
    count++
    const before = m[0]
    const attrs1 = m[1] || ''
    const attrs2 = m[5] || ''
    const newTag = `<UiInput${attrs1}${attrs2} />`
    out = out.replace(before, newTag)
  }
  return { out, count }
}

function replaceSelects(content) {
  const regex = /<select([^>]*)class=("|')([^"']*shadcn-input[^"']*)("|')([^>]*)>/gi
  let m
  let out = content
  let count = 0
  while ((m = regex.exec(content)) !== null) {
    count++
    const before = m[0]
    const attrs1 = m[1] || ''
    const attrs2 = m[5] || ''
    const newOpen = `<UiSelect${attrs1}${attrs2}>`
    out = out.replace(before, newOpen)
    // closing tag replacement later
  }
  out = out.replace(/<\/select>/g, '</UiSelect>')
  return { out, count }
}

function replaceCards(content) {
  const regex = /<div([^>]*)class=("|')([^"']*shadcn-card[^"']*)("|')([^>]*)>/gi
  let m
  let out = content
  let count = 0
  while ((m = regex.exec(content)) !== null) {
    count++
    const before = m[0]
    const attrs1 = m[1] || ''
    const attrs2 = m[5] || ''
    const newOpen = `<UiCard${attrs1}${attrs2}>`
    out = out.replace(before, newOpen)
  }
  out = out.replace(/<\/div>/g, (s, offset, str) => {
    // naive replacement: only replace if closing a UiCard opening earlier in file
    // write simple heuristic: if file contains '<UiCard' then replace first matching '</div>' after it with '</UiCard>'
    return s
  })
  // This codemod only replaces openings for cards; closing tags are left for manual review.
  return { out, count }
}

function processFile(file) {
  const abs = path.join(repoRoot, file)
  const content = fs.readFileSync(abs, 'utf8')
  let total = 0
  let changed = content
  let r
  r = replaceButtons(changed); changed = r.out; total += r.count
  r = replaceInputs(changed); changed = r.out; total += r.count
  r = replaceSelects(changed); changed = r.out; total += r.count
  r = replaceCards(changed); changed = r.out; total += r.count

  if (total > 0 && changed !== content) {
    const relOut = path.relative(repoRoot, file).replace(/\\/g, '/')
    const outFile = path.join(outDir, relOut + '.patch')
    const dir = path.dirname(outFile)
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true })
    const patch = ['--- a/' + relOut, '+++ b/' + relOut, '@@', content, '---', '+++', changed].join('\n')
    fs.writeFileSync(outFile, patch, 'utf8')
    return { file: relOut, changes: total, patch: outFile }
  }
  return null
}

function main() {
  const files = glob.sync('resources/js/**/*.vue', { cwd: repoRoot, nodir: true })
  const results = []
  for (const f of files) {
    const res = processFile(f)
    if (res) results.push(res)
  }
  const summary = { date: new Date().toISOString(), results }
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2), 'utf8')
  console.log('Preview patches written to', outDir)
  console.log('Summary:', summary.results.length, 'files with proposed changes')
}

main()
