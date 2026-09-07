import { ref, computed } from 'vue'
import { formatDateTime as fmtDateTime, formatDate as fmtDate, formatTime as fmtTime } from '@/Utils/date'

const currentLocale = ref(localStorage.getItem('app_locale') || 'ar')

export function useI18n() {
  const setLocale = (locale) => {
    currentLocale.value = locale
    localStorage.setItem('app_locale', locale)
    document.documentElement.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr')
    document.documentElement.setAttribute('lang', locale)
  }

  const toggleLocale = () => {
    setLocale(currentLocale.value === 'ar' ? 'en' : 'ar')
  }

  const isAr = computed(() => currentLocale.value === 'ar')
  const dir = computed(() => (currentLocale.value === 'ar' ? 'rtl' : 'ltr'))

  // Initialize DOM attributes on load
  if (typeof document !== 'undefined') {
    document.documentElement.setAttribute('dir', currentLocale.value === 'ar' ? 'rtl' : 'ltr')
    document.documentElement.setAttribute('lang', currentLocale.value)
  }

  const formatDateTime = (val, options = {}) => fmtDateTime(val, isAr.value, options)
  const formatDate = (val) => fmtDate(val)
  const formatTime = (val) => fmtTime(val, isAr.value)

  return {
    locale: currentLocale,
    isAr,
    dir,
    setLocale,
    toggleLocale,
    formatDateTime,
    formatDate,
    formatTime,
  }
}

