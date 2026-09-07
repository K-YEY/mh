<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { formatDateTime as fmtDateTime } from '@/Utils/date'
import { 
  Printer, ArrowLeft, ArrowRight, Layers, FilePlus2, 
  CheckCircle2, Building2, Ship, ShieldCheck, Share2, 
  Copy, ExternalLink, Hash, Calendar, DollarSign, Globe
} from 'lucide-vue-next'

const { isAr, locale } = useI18n()

const props = defineProps({
  invoice: Object,
})

// Control toggles
const showBackground = ref(true)
// Synchronize invoice language automatically with website language
const invoiceLang = ref(isAr.value ? 'ar' : 'en')
const isCopied = ref(false)

// Keep invoice language in sync with site language whenever user toggles it
watch(isAr, (newVal) => {
  invoiceLang.value = newVal ? 'ar' : 'en'
}, { immediate: true })

const isArabicDoc = computed(() => invoiceLang.value === 'ar')

const formatDocDate = (val) => {
  if (!val) return '—'
  return fmtDateTime(val, isArabicDoc.value)
}

const printInvoice = () => {
  window.print()
}

const copyShareLink = () => {
  navigator.clipboard.writeText(window.location.href)
  isCopied.value = true
  setTimeout(() => isCopied.value = false, 2000)
}

// Convert amount to words in Arabic
const numberToWordsAr = (amount, currency = 'USD') => {
  const num = Math.floor(amount)
  const cents = Math.round((amount - num) * 100)
  
  const currNames = {
    USD: { singular: 'دولار أمريكي', plural: 'دولارات أمريكية', cent: 'سنت' },
    EGP: { singular: 'جنيه مصري', plural: 'جنيهات مصرية', cent: 'قرش' },
    EUR: { singular: 'يورو', plural: 'يوروهات', cent: 'سنت' },
  }
  
  const c = currNames[currency] || currNames.USD
  
  const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر']
  const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون']
  const hundreds = ['', 'مائة', 'مائتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 'سبعمائة', 'ثمانمائة', 'تسعمائة']
  
  function convertGroup(n) {
    let groupStr = ''
    const h = Math.floor(n / 100)
    const rem = n % 100
    if (h > 0) {
      groupStr += hundreds[h]
      if (rem > 0) groupStr += ' و'
    }
    if (rem > 0) {
      if (rem < 20) {
        groupStr += ones[rem]
      } else {
        const o = rem % 10
        const t = Math.floor(rem / 10)
        if (o > 0) groupStr += ones[o] + ' و'
        groupStr += tens[t]
      }
    }
    return groupStr
  }

  let result = ''
  if (num === 0) {
    result = 'صفر'
  } else {
    const thousands = Math.floor(num / 1000)
    const remainder = num % 1000

    if (thousands > 0) {
      if (thousands === 1) result += 'ألف'
      else if (thousands === 2) result += 'ألفان'
      else if (thousands >= 3 && thousands <= 10) result += convertGroup(thousands) + ' آلاف'
      else result += convertGroup(thousands) + ' ألف'

      if (remainder > 0) result += ' و'
    }

    if (remainder > 0) {
      result += convertGroup(remainder)
    }
  }

  let finalPhrase = `فقط ${result} ${num > 2 && num <= 10 ? c.plural : c.singular} لا غير`
  if (cents > 0) {
    finalPhrase += ` و ${cents} ${c.cent}`
  }
  return finalPhrase
}

// Convert amount to words in English (Standard Banking & Export Format)
const numberToWordsEn = (amount, currency = 'USD') => {
  const num = Math.floor(amount)
  const cents = Math.round((amount - num) * 100)

  const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen']
  const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety']

  const convertGroup = (n) => {
    let str = ''
    const h = Math.floor(n / 100)
    const r = n % 100
    if (h > 0) {
      str += ones[h] + ' Hundred '
    }
    if (r > 0) {
      if (r < 20) {
        str += ones[r] + ' '
      } else {
        str += tens[Math.floor(r / 10)] + (r % 10 > 0 ? '-' + ones[r % 10] : '') + ' '
      }
    }
    return str.trim()
  }

  const currNames = {
    USD: { singular: 'US Dollar', plural: 'US Dollars', cent: 'Cents' },
    EGP: { singular: 'Egyptian Pound', plural: 'Egyptian Pounds', cent: 'Piastres' },
    EUR: { singular: 'Euro', plural: 'Euros', cent: 'Cents' },
  }
  const c = currNames[currency] || currNames.USD

  if (num === 0) return `Only Zero ${c.plural}`

  let words = ''
  const millions = Math.floor(num / 1000000)
  const thousands = Math.floor((num % 1000000) / 1000)
  const remainder = num % 1000

  if (millions > 0) words += convertGroup(millions) + ' Million '
  if (thousands > 0) words += convertGroup(thousands) + ' Thousand '
  if (remainder > 0) words += convertGroup(remainder)

  words = words.trim()
  const currencyStr = num === 1 ? c.singular : c.plural
  let result = `Only ${words} ${currencyStr}`
  if (cents > 0) {
    result += ` and ${cents}/100 ${c.cent}`
  }
  return result
}
</script>

<template>
  <Head :title="`${isArabicDoc ? 'فاتورة' : 'Invoice'} ${invoice.invoice_number} | MH For Export`" />

  <div class="min-h-screen bg-slate-100 py-6 px-3 sm:px-6 font-sans text-slate-900 selection:bg-blue-600 selection:text-white print:p-0 print:m-0 print:bg-white print:min-h-0">
    
    <!-- Workbench Action Toolbar (Hidden during Print) -->
    <div class="no-print print:hidden max-w-[210mm] mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
      <div class="flex items-center gap-2">
        <Link 
          :href="route('invoices.index')" 
          class="p-2 rounded-lg bg-slate-50 text-slate-600 hover:text-blue-950 hover:bg-slate-100 border border-slate-200 transition-colors"
          :title="isArabicDoc ? 'الرجوع للقائمة' : 'Back to Invoices'"
        >
          <component :is="isArabicDoc ? ArrowRight : ArrowLeft" class="w-4 h-4" />
        </Link>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-mono font-black text-sm text-blue-950">{{ invoice.invoice_number }}</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-blue-50 text-blue-800 border border-blue-200">
              {{ invoice.status }}
            </span>
          </div>
          <p class="text-xs text-slate-500">
            {{ isArabicDoc ? 'معاينة جاهزة للطباعة على ورق شركة MH For Export' : 'Print-ready A4 sheet on MH For Export Stationery' }}
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2 flex-wrap">
        <!-- Language Switcher for Invoice Sheet -->
        <button
          type="button"
          @click="invoiceLang = invoiceLang === 'ar' ? 'en' : 'ar'"
          class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer transition-colors"
          :title="isArabicDoc ? 'التبديل إلى الفاتورة باللغة الإنجليزية' : 'Switch to Arabic Invoice'"
        >
          <Globe class="w-3.5 h-3.5 text-blue-700" />
          <span>{{ isArabicDoc ? 'اللغة: 🇪🇬 عربي' : 'Lang: 🇬🇧 English' }}</span>
        </button>

        <!-- Background Toggle Button -->
        <button
          type="button"
          @click="showBackground = !showBackground"
          :class="[
            showBackground 
              ? 'bg-blue-50 text-blue-900 border-blue-300' 
              : 'bg-white text-slate-600 border-slate-300',
            'px-3 py-1.5 rounded-lg text-xs font-bold border flex items-center gap-1.5 cursor-pointer hover:border-blue-500 transition-colors'
          ]"
          :title="showBackground ? 'إخفاء ورق الشركة إذا كنت ستطبع على ورق جاهز' : 'إظهار ورق الشركة الملون'"
        >
          <Layers class="w-3.5 h-3.5" />
          <span>{{ showBackground ? (isArabicDoc ? 'الورق المروس: مفعل' : 'Letterhead: ON') : (isArabicDoc ? 'طباعة على ورق جاهز (خلفية بيضاء)' : 'Plain Paper') }}</span>
        </button>

        <!-- Copy Share Link -->
        <button
          type="button"
          @click="copyShareLink"
          class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer transition-colors"
        >
          <component :is="isCopied ? CheckCircle2 : Share2" class="w-3.5 h-3.5" :class="isCopied ? 'text-emerald-600' : ''" />
          <span>{{ isCopied ? (isArabicDoc ? 'تم النسخ!' : 'Copied!') : (isArabicDoc ? 'مشاركة' : 'Share') }}</span>
        </button>

        <!-- New Invoice -->
        <Link
          :href="route('invoices.create')"
          class="hidden sm:flex px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 items-center gap-1.5 transition-colors"
        >
          <FilePlus2 class="w-3.5 h-3.5" />
          <span>{{ isArabicDoc ? 'فاتورة جديدة' : 'New Invoice' }}</span>
        </Link>

        <!-- Print Action -->
        <button
          type="button"
          @click="printInvoice"
          class="px-4 py-1.5 rounded-lg text-xs font-bold bg-blue-700 hover:bg-blue-800 text-white flex items-center gap-2 cursor-pointer shadow-sm active:scale-95 transition-all"
        >
          <Printer class="w-4 h-4" />
          <span>{{ isArabicDoc ? 'طباعة الفاتورة / PDF' : 'Print / Save PDF' }}</span>
        </button>
      </div>
    </div>

    <!-- The A4 Sheet Simulator Container -->
    <div class="invoice-container flex justify-center print:block print:w-[210mm] print:m-0 print:p-0">
      <div 
        :dir="isArabicDoc ? 'rtl' : 'ltr'"
        :class="[
          'invoice-sheet relative bg-white transition-all',
          showBackground ? 'with-letterhead' : 'plain-white'
        ]"
      >
        <!-- Content Area: Positioned strictly within the letterhead safe zone -->
        <div class="invoice-content-box flex flex-col justify-between h-full">
          
          <!-- Top Section -->
          <div>
            <!-- Invoice Title & Document Identification Header -->
            <div class="flex items-start justify-between border-b border-blue-900/20 pb-2 mb-2.5">
              <div>
                <div class="flex items-center gap-2">
                  <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-800"></span>
                  <h1 class="text-xl font-black tracking-tight text-blue-950 font-serif">
                    {{ isArabicDoc ? 'فاتورة مبيعات ضريبية' : 'TAX & COMMERCIAL INVOICE' }}
                  </h1>
                </div>
                <p class="text-[11px] text-blue-800/80 font-bold mt-0.5">
                  {{ isArabicDoc ? 'شركة إم إند إتش للشحن والتصدير البحري' : 'MH For Export Logistics & Ocean Freight Management' }}
                </p>
              </div>

              <!-- Invoice Reference Badges -->
              <div class="text-left rtl:text-left ltr:text-right">
                <div class="inline-block px-2.5 py-1 bg-blue-950 text-white font-mono font-black text-sm rounded shadow-xs">
                  {{ invoice.invoice_number }}
                </div>
                <div class="text-[10px] text-slate-500 font-mono mt-1 font-semibold">
                  {{ isArabicDoc ? 'التاريخ:' : 'Date:' }} 
                  <span class="text-slate-900 font-bold">{{ formatDocDate(invoice.created_at || invoice.issue_date) }}</span>
                </div>
                <div class="text-[10px] text-slate-500 font-mono font-semibold">
                  {{ isArabicDoc ? 'الاستحقاق:' : 'Due:' }} 
                  <span class="text-slate-900 font-bold">{{ formatDocDate(invoice.due_date) }}</span>
                </div>
              </div>
            </div>

            <!-- Two-Column Information Box: Client (Billed To) & Shipment Logistics -->
            <div class="grid grid-cols-2 gap-2.5 mb-2.5">
              <!-- Billed To Box -->
              <div class="p-2 rounded-lg bg-blue-50/60 border border-blue-100 text-xs">
                <div class="flex items-center gap-1.5 text-blue-950 font-black text-[11px] uppercase tracking-wider mb-1 border-b border-blue-200/60 pb-1">
                  <Building2 class="w-3.5 h-3.5 text-blue-700" />
                  <span>{{ isArabicDoc ? 'بيانات العميل (المفوتر إليه)' : 'Billed To (Customer Details)' }}</span>
                </div>
                <div class="space-y-0.5 text-[11px]">
                  <div class="font-black text-slate-900 text-xs truncate">
                    {{ invoice.party?.legal_name || (isArabicDoc ? 'عميل نقدي' : 'Cash Customer') }}
                  </div>
                  <div v-if="invoice.party?.code" class="text-slate-600 font-mono text-[10px]">
                    <span class="text-slate-400">{{ isArabicDoc ? 'كود العميل:' : 'Code:' }}</span> 
                    <strong>{{ invoice.party.code }}</strong>
                  </div>
                  <div v-if="invoice.party?.tax_number" class="text-slate-600 font-mono text-[10px]">
                    <span class="text-slate-400">{{ isArabicDoc ? 'البطاقة الضريبية:' : 'Tax ID:' }}</span> 
                    <strong>{{ invoice.party.tax_number }}</strong>
                  </div>
                  <div class="text-slate-600 text-[10px] truncate">
                    <span class="text-slate-400">{{ isArabicDoc ? 'العنوان:' : 'Address:' }}</span> 
                    {{ invoice.party?.address || (isArabicDoc ? 'الإسكندرية، مصر' : 'Alexandria, Egypt') }}
                  </div>
                </div>
              </div>

              <!-- Ocean Freight & Master Shipment Reference Box -->
              <div class="p-2 rounded-lg bg-slate-50/80 border border-slate-200 text-xs">
                <div class="flex items-center gap-1.5 text-blue-950 font-black text-[11px] uppercase tracking-wider mb-1 border-b border-slate-200 pb-1">
                  <Ship class="w-3.5 h-3.5 text-blue-700" />
                  <span>{{ isArabicDoc ? 'بيانات الشحنة والعملية البحرية' : 'Ocean Logistics Reference' }}</span>
                </div>
                <div class="space-y-0.5 text-[11px]">
                  <div class="flex justify-between font-mono">
                    <span class="text-slate-500 text-[10px]">{{ isArabicDoc ? 'ملف الشحنة:' : 'Master File:' }}</span>
                    <strong class="text-blue-950">{{ invoice.master_shipment?.file_number || (isArabicDoc ? 'خدمات عامة' : 'General Freight') }}</strong>
                  </div>
                  <div v-if="invoice.master_shipment?.vessel_name" class="flex justify-between">
                    <span class="text-slate-500 text-[10px]">{{ isArabicDoc ? 'السفينة / الرحلة:' : 'Vessel/Voy:' }}</span>
                    <strong class="text-slate-900 truncate max-w-[140px]">{{ invoice.master_shipment.vessel_name }} ({{ invoice.master_shipment.voyage_number || 'Voy' }})</strong>
                  </div>
                  <div v-if="invoice.master_shipment?.master_bl_number" class="flex justify-between font-mono">
                    <span class="text-slate-500 text-[10px]">{{ isArabicDoc ? 'بوليصة الشحن (B/L):' : 'B/L No:' }}</span>
                    <strong class="text-slate-900">{{ invoice.master_shipment.master_bl_number }}</strong>
                  </div>
                  <div class="flex justify-between text-[10px]">
                    <span class="text-slate-500">{{ isArabicDoc ? 'خط السير:' : 'Route:' }}</span>
                    <span class="font-bold text-slate-800 truncate max-w-[140px]">
                      {{ invoice.master_shipment?.pol?.name || (isArabicDoc ? 'ميناء الإسكندرية' : 'Alexandria Port') }} ➔ {{ invoice.master_shipment?.pod?.name || (isArabicDoc ? 'ميناء المقصد' : 'Destination') }}
                    </span>
                  </div>
                  <div v-if="invoice.master_shipment?.containers?.length" class="text-[10px] text-slate-600 truncate flex justify-between">
                    <span class="text-slate-500">{{ isArabicDoc ? 'الحاويات:' : 'Containers:' }}</span>
                    <span class="font-mono font-bold text-blue-900 truncate max-w-[140px]">
                      {{ invoice.master_shipment.containers.map(c => c.container_number).join(', ') }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Line Items Table -->
            <div class="border border-blue-950/20 rounded-lg overflow-hidden mb-2.5">
              <table class="w-full text-xs">
                <thead class="bg-blue-950 text-white text-[9px] font-bold uppercase tracking-wider">
                  <tr>
                    <th class="py-1 px-2 text-center w-8">#</th>
                    <th class="py-1 px-3" :class="isArabicDoc ? 'text-right' : 'text-left'">
                      {{ isArabicDoc ? 'بيان البند والخدمة' : 'Description & Charge Item' }}
                    </th>
                    <th class="py-1 px-2 text-center w-16">{{ isArabicDoc ? 'الرمز' : 'Code' }}</th>
                    <th class="py-1 px-2 text-center w-16">{{ isArabicDoc ? 'الكمية' : 'Qty' }}</th>
                    <th class="py-1 px-3 text-center w-24">{{ isArabicDoc ? 'سعر الوحدة' : 'Unit Price' }}</th>
                    <th class="py-1 px-3 text-center w-28">{{ isArabicDoc ? 'الإجمالي' : 'Total Amount' }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                  <tr 
                    v-for="(item, idx) in invoice.items" 
                    :key="item.id || idx"
                    :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/50'"
                  >
                    <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-500">{{ idx + 1 }}</td>
                    <td class="py-1.5 px-3">
                      <div class="font-bold text-slate-900 text-xs">
                        {{ isArabicDoc ? (item.description || item.charge_item?.name_ar || item.charge_item?.name_en) : (item.charge_item?.name_en || item.description || item.charge_item?.name_ar) }}
                      </div>
                      <div v-if="item.charge_item?.name_en && isArabicDoc" class="text-[9px] text-slate-500 font-mono">
                        {{ item.charge_item.name_en }}
                      </div>
                    </td>
                    <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-600 font-bold">
                      {{ item.charge_item?.code || 'SRV' }}
                    </td>
                    <td class="py-1.5 px-2 text-center font-mono font-bold text-slate-900">
                      {{ Number(item.quantity) }}
                    </td>
                    <td class="py-1.5 px-3 text-center font-mono text-slate-700">
                      ${{ Number(item.unit_price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </td>
                    <td class="py-1.5 px-3 text-center font-mono font-black text-blue-950">
                      ${{ Number(item.line_total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </td>
                  </tr>

                  <!-- Fill minimal blank rows if single item -->
                  <tr v-if="invoice.items?.length < 2" v-for="n in (2 - invoice.items.length)" :key="'blank-'+n" class="h-6">
                    <td class="py-1">&nbsp;</td>
                    <td class="py-1"></td>
                    <td class="py-1"></td>
                    <td class="py-1"></td>
                    <td class="py-1"></td>
                    <td class="py-1"></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Financial Summary & Tafqeet Section -->
            <div class="grid grid-cols-12 gap-2.5 mb-2 items-start">
              <!-- Left / Tafqeet & Payment Notice -->
              <div class="col-span-7 space-y-1.5">
                <div class="p-2 rounded-lg bg-blue-50/50 border border-blue-100 text-[10px]">
                  <div class="font-bold text-blue-950 mb-0.5">
                    {{ isArabicDoc ? 'المبلغ بالحروف (فقط لا غير):' : 'Amount in Words:' }}
                  </div>
                  <div class="font-bold text-slate-800 leading-relaxed" :class="isArabicDoc ? 'font-serif' : 'font-sans'">
                    {{ isArabicDoc ? numberToWordsAr(invoice.total_amount, invoice.currency_code) : numberToWordsEn(invoice.total_amount, invoice.currency_code) }}
                  </div>
                </div>

                <div class="text-[9px] text-slate-500 leading-tight space-y-0.5">
                  <p class="font-semibold text-slate-700">
                    {{ isArabicDoc ? '• السداد يتم عن طريق التحويل البنكي لحسابات الشركة المعتمدة الموضحة بالأسفل.' : '• Payments must be remitted via wire transfer to company accounts listed below.' }}
                  </p>
                  <p>
                    {{ isArabicDoc ? '• الخدمات التصديرية معفاة من ضريبة القيمة المضافة بموجب القانون رقم 67 لسنة 2016.' : '• Maritime export services are VAT-exempt pursuant to Egyptian Law No. 67/2016.' }}
                  </p>
                </div>
              </div>

              <!-- Right / Totals Box -->
              <div class="col-span-5 bg-slate-50 rounded-lg border border-slate-200 p-2 space-y-1 text-xs">
                <div class="flex justify-between text-slate-600 text-[10px]">
                  <span>{{ isArabicDoc ? 'المجموع الفرعي:' : 'Subtotal:' }}</span>
                  <span class="font-mono font-bold text-slate-900">
                    ${{ Number(invoice.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                  </span>
                </div>
                <div class="flex justify-between text-slate-600 text-[10px]">
                  <span>{{ isArabicDoc ? 'ضريبة القيمة المضافة (0% تصدير):' : 'VAT (0% Export):' }}</span>
                  <span class="font-mono font-bold text-slate-900">$0.00</span>
                </div>
                <div class="border-t border-slate-300 pt-1 flex justify-between items-center text-blue-950">
                  <span class="font-black text-xs">{{ isArabicDoc ? 'الإجمالي الصافي:' : 'Grand Total:' }}</span>
                  <div class="text-right">
                    <span class="font-mono font-black text-base text-blue-900">
                      ${{ Number(invoice.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-600 ms-1">{{ invoice.currency_code }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Section: Official Stamp, Verification & Signatures -->
          <div class="pt-2 border-t border-slate-200">
            <div class="flex items-center justify-between text-xs">
              
              <!-- Prepared By -->
              <div class="text-center w-36">
                <p class="text-[9px] text-slate-400 font-bold uppercase mb-3">
                  {{ isArabicDoc ? 'إعداد الحسابات' : 'Prepared By' }}
                </p>
                <div class="border-b border-slate-300 pb-0.5">
                  <span class="font-bold text-slate-800 text-[10px]">Financial Accountant</span>
                </div>
              </div>

              <!-- Official Blue Verification Circular Stamp -->
              <div class="flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-full border-2 border-dashed border-blue-700/80 p-0.5 flex items-center justify-center text-center rotate-[-6deg] select-none hover:rotate-0 transition-transform">
                  <div class="w-full h-full rounded-full border border-blue-800 flex flex-col items-center justify-center bg-blue-50/40 text-blue-900">
                    <span class="text-[7px] font-mono font-black tracking-wider uppercase">MH FOR EXPORT</span>
                    <ShieldCheck class="w-3 h-3 text-blue-800 my-0.2" />
                    <span class="text-[5.5px] font-black tracking-widest uppercase">AUDITED & APPROVED</span>
                    <span class="text-[5.5px] font-mono text-blue-700">FINANCIAL CONTROL</span>
                  </div>
                </div>
              </div>

              <!-- Authorized Officer -->
              <div class="text-center w-36">
                <p class="text-[9px] text-slate-400 font-bold uppercase mb-3">
                  {{ isArabicDoc ? 'اعتماد الإدارة المالية' : 'Authorized Signatory' }}
                </p>
                <div class="border-b border-slate-300 pb-0.5">
                  <span class="font-bold text-slate-800 text-[10px]">Financial Controller</span>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* Standard A4 Screen Simulation */
.invoice-sheet {
  width: 210mm;
  height: 297mm;
  max-height: 297mm;
  box-sizing: border-box;
  box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(15, 23, 42, 0.05);
  margin-bottom: 2rem;
  overflow: hidden;
  position: relative;
}

/* When letterhead background is enabled */
.invoice-sheet.with-letterhead {
  background-image: url('/paper_025415.png');
  background-size: 210mm 297mm;
  background-repeat: no-repeat;
  background-position: top center;
  padding-top: 53mm;
  padding-bottom: 53mm;
  padding-left: 14mm;
  padding-right: 14mm;
}

/* When printing on company pre-printed paper stock */
.invoice-sheet.plain-white {
  background: #ffffff;
  padding-top: 53mm;
  padding-bottom: 53mm;
  padding-left: 14mm;
  padding-right: 14mm;
}

/* High Fidelity 100% Exact A4 Print Stylesheet */
@media print {
  @page {
    size: A4 portrait;
    margin: 0 !important;
  }

  html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    width: 210mm !important;
    height: 297mm !important;
    max-height: 297mm !important;
    overflow: hidden !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Completely eliminate sidebars, headers, action toolbars & alerts */
  aside,
  header,
  nav,
  .no-print,
  [role="navigation"] {
    display: none !important;
  }

  main,
  .app-shell,
  #app {
    padding: 0 !important;
    margin: 0 !important;
    overflow: visible !important;
    background: transparent !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    border: none !important;
    box-shadow: none !important;
  }

  .min-h-screen {
    min-height: auto !important;
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .invoice-container {
    padding: 0 !important;
    margin: 0 auto !important;
    display: block !important;
    width: 210mm !important;
    height: 297mm !important;
    max-height: 297mm !important;
  }

  .invoice-sheet {
    width: 210mm !important;
    height: 297mm !important;
    max-height: 297mm !important;
    min-height: 297mm !important;
    margin: 0 auto !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    page-break-after: avoid !important;
    page-break-before: avoid !important;
    page-break-inside: avoid !important;
    break-after: avoid !important;
    break-before: avoid !important;
    break-inside: avoid !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .invoice-sheet.with-letterhead {
    background-image: url('/paper_025415.png') !important;
    background-size: 210mm 297mm !important;
    background-repeat: no-repeat !important;
    background-position: top center !important;
    padding-top: 53mm !important;
    padding-bottom: 53mm !important;
    padding-left: 14mm !important;
    padding-right: 14mm !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .invoice-sheet.plain-white {
    background: #ffffff !important;
    padding-top: 53mm !important;
    padding-bottom: 53mm !important;
    padding-left: 14mm !important;
    padding-right: 14mm !important;
  }
}
</style>

