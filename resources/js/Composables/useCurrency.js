import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const currencyFlags = {
  USD: '🇺🇸',
  EGP: '🇪🇬',
  EUR: '🇪🇺',
  GBP: '🇬🇧',
  SAR: '🇸🇦',
  AED: '🇦🇪',
  CNY: '🇨🇳',
  KWD: '🇰🇼',
  QAR: '🇶🇦',
  JOD: '🇯🇴',
}

export function useCurrency() {
  const page = usePage()

  const baseCurrency = computed(() => {
    return page.props.base_currency || {
      code: 'EGP',
      name: 'Egyptian Pound',
      symbol: 'E£',
      decimal_places: 2,
      rate_to_base: 1.0,
    }
  })

  const currentCurrency = computed(() => {
    return page.props.current_currency || baseCurrency.value
  })

  const activeCurrencies = computed(() => {
    return page.props.active_currencies || [baseCurrency.value]
  })

  const ratesMap = computed(() => {
    return page.props.rates_map || { [baseCurrency.value.code]: 1.0 }
  })

  const getFlag = (code) => {
    return currencyFlags[String(code).toUpperCase()] || '🪙'
  }

  /**
   * Convert an amount between currencies.
   * In MH ERP Double-Entry FX:
   * (Amount in Foreign) * (rate_to_base) = (Amount in Base)
   * Therefore:
   * (Amount in Base) / (rate_to_base) = (Amount in Foreign)
   */
  const convertAmount = (amount, fromCode = null, toCode = null) => {
    const val = parseFloat(amount)
    if (isNaN(val)) return 0

    const baseCode = (baseCurrency.value?.code || 'EGP').toUpperCase()
    const from = (fromCode || baseCode).toUpperCase()
    const to = (toCode || currentCurrency.value?.code || baseCode).toUpperCase()

    if (from === to) {
      return val
    }

    const rates = ratesMap.value || {}

    // 1. Convert `from` to system Base Currency
    let amountInBase = val
    if (from !== baseCode) {
      const fromRate = parseFloat(rates[from]) || 1.0
      amountInBase = val * fromRate
    }

    // 2. Convert Base Currency to `to` Target Currency
    if (to === baseCode) {
      return amountInBase
    }

    const toRate = parseFloat(rates[to]) || 1.0
    if (toRate <= 0) return amountInBase

    return amountInBase / toRate
  }

  /**
   * Format money according to the currently active currency.
   * Automatically converts from Base (or specified fromCode) and formats with proper symbol and precision.
   */
  const formatMoney = (amount, fromCode = null, options = {}) => {
    if (amount === null || amount === undefined || amount === '') {
      return '-'
    }

    const num = parseFloat(amount)
    if (isNaN(num)) {
      return '-'
    }

    const targetCode = (options.to || currentCurrency.value?.code || baseCurrency.value?.code || 'EGP').toUpperCase()
    const shouldConvert = options.convert !== false

    const converted = shouldConvert ? convertAmount(num, fromCode, targetCode) : num

    if (options.rawNumber) {
      return converted
    }

    const decimals = options.decimals ?? (currentCurrency.value?.decimal_places ?? 2)
    const formattedNum = converted.toLocaleString('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    })

    const targetCurrencyObj = activeCurrencies.value.find(c => c.code === targetCode)
    const symbol = options.symbol ?? (targetCurrencyObj?.symbol || currentCurrency.value?.symbol || '$')

    if (options.showCode) {
      return `${symbol} ${formattedNum} ${targetCode}`
    }

    return `${symbol} ${formattedNum}`
  }

  /**
   * Switch the active system display currency.
   */
  const switchCurrency = (code) => {
    if (!code) return
    const upperCode = code.toUpperCase()
    try {
      localStorage.setItem('app_currency', upperCode)
    } catch (e) {}

    router.post(route('currencies.switch'), { currency: upperCode }, {
      preserveScroll: true,
      preserveState: false,
    })
  }

  return {
    baseCurrency,
    currentCurrency,
    activeCurrencies,
    ratesMap,
    getFlag,
    convertAmount,
    formatMoney,
    switchCurrency,
  }
}
