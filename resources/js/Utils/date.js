/**
 * Standardized Date & Time Utility for M&H Agri ERP
 * Formats dates into: YYYY-MM-DD hh:mm AM/PM (or localized ص/م)
 */

export function formatDateTime(val, isAr = false, options = {}) {
  if (!val) return '—'

  let date
  if (val instanceof Date) {
    date = val
  } else if (typeof val === 'string') {
    const trimmed = val.trim()

    // If already in standard formatted pattern, return as is
    if (/^\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}\s*(AM|PM|ص|م)/i.test(trimmed)) {
      return trimmed
    }

    // Pure date string: YYYY-MM-DD
    if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
      const [y, m, d] = trimmed.split('-').map(Number)
      if (options.dateOnly) {
        return `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`
      }
      date = new Date(y, m - 1, d, 12, 0, 0)
    } else {
      // Standard timestamp (e.g. 2026-09-07 11:04:20 or ISO)
      // Normalize space separated SQL datetime for cross-browser parsing
      const isoCandidate = trimmed.replace(' ', 'T')
      date = new Date(isoCandidate)
      if (isNaN(date.getTime())) {
        date = new Date(trimmed)
      }
    }
  } else if (typeof val === 'number') {
    date = new Date(val)
  }

  if (!date || isNaN(date.getTime())) {
    return String(val)
  }

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  let hours = date.getHours()
  const minutes = String(date.getMinutes()).padStart(2, '0')
  const isPm = hours >= 12
  const periodEn = isPm ? 'PM' : 'AM'
  const periodAr = isPm ? 'م' : 'ص'

  // 12-hour format (01-12)
  hours = hours % 12
  if (hours === 0) hours = 12
  const hoursStr = String(hours).padStart(2, '0')

  let period = periodEn
  if (options.pureAmPm) {
    period = periodEn
  } else if (isAr) {
    period = `${periodAr} (${periodEn})`
  }

  return `${year}-${month}-${day} ${hoursStr}:${minutes} ${period}`
}

export function formatDate(val) {
  if (!val) return '—'
  if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val.trim())) {
    return val.trim()
  }
  const date = new Date(val)
  if (isNaN(date.getTime())) return String(val)
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export function formatTime(val, isAr = false) {
  if (!val) return '—'
  const date = new Date(val)
  if (isNaN(date.getTime())) return String(val)
  let hours = date.getHours()
  const minutes = String(date.getMinutes()).padStart(2, '0')
  const isPm = hours >= 12
  const periodEn = isPm ? 'PM' : 'AM'
  const periodAr = isPm ? 'م' : 'ص'
  hours = hours % 12
  if (hours === 0) hours = 12
  const period = isAr ? `${periodAr} (${periodEn})` : periodEn
  return `${String(hours).padStart(2, '0')}:${minutes} ${period}`
}
