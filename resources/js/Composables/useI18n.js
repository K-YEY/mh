import { ref, computed } from 'vue'

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

  return {
    locale: currentLocale,
    isAr,
    dir,
    setLocale,
    toggleLocale,
  }
}
