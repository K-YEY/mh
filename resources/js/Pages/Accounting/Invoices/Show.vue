<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Printer, ArrowLeft, ArrowRight, Layers, FilePlus2, 
  CheckCircle2, Building2, Ship, ShieldCheck, Share2, 
  Copy, ExternalLink, Hash, Calendar, DollarSign
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  invoice: Object,
})

// Control toggles
const showBackground = ref(true)
const invoiceLang = ref('ar') // 'ar' or 'en'
const isCopied = ref(false)

const isArabicDoc = computed(() => invoiceLang.value === 'ar')

const printInvoice = () => {
  window.print()
}

const copyShareLink = () => {
  navigator.clipboard.writeText(window.location.href)
  isCopied.value = true
  setTimeout(() => isCopied.value = false, 2000)
}

// Convert amount to words in Arabic & English
const numberToWordsAr = (amount, currency = 'USD') => {
  const num = Math.floor(amount)
  const cents = Math.round((amount - num) * 100)
  
  const currNames = {
    USD: { singular: 'دولار أمريكي', plural: 'دولارات أمريكية', cent: 'سنت' },
    EGP: { singular: 'جنيه مصري', plural: 'جنيهات مصرية', cent: 'قرش' },
    EUR: { singular: 'يورو', plural: 'يوروهات', cent: 'سنت' },
  }
  
  const c = currNames[currency] || currNames.USD
  
  // Basic friendly Arabic words for standard amounts
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

  let finalPhrase = `فقط ${result} ${c.singular} لا غير`
  if (cents > 0) {
    finalPhrase += ` و ${cents} ${c.cent}`
  }
  return finalPhrase
}

const numberToWordsEn = (amount, currency = 'USD') => {
  const currNames = {
    USD: 'US Dollars',
    EGP: 'Egyptian Pounds',
    EUR: 'Euros',
  }
  return `Only ${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currNames[currency] || currency}`
}
</script>

<template>
  <Head :title="`فاتورة ${invoice.invoice_number} | MH For Export`" />

  <div class="min-h-screen bg-slate-100 py-6 px-3 sm:px-6 font-sans text-slate-900 selection:bg-blue-600 selection:text-white">
    
    <!-- Workbench Action Toolbar (Hidden during Print) -->
    <div class="no-print max-w-[210mm] mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
      <div class="flex items-center gap-2">
        <Link 
          :href="route('invoices.index')" 
          class="p-2 rounded-lg bg-slate-50 text-slate-600 hover:text-blue-950 hover:bg-slate-100 border border-slate-200 transition-colors"
          :title="isAr ? 'الرجوع للقائمة' : 'Back to Invoices'"
        >
          <component :is="isAr ? ArrowRight : ArrowLeft" class="w-4 h-4" />
        </Link>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-mono font-black text-sm text-blue-950">{{ invoice.invoice_number }}</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-blue-50 text-blue-800 border border-blue-200">
              {{ invoice.status }}
            </span>
          </div>
          <p class="text-xs text-slate-500">
            {{ isAr ? 'معاينة جاهزة للطباعة على ورق شركة MH For Export' : 'Printable invoice on MH For Export Stationery' }}
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
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
          <span>{{ showBackground ? (isAr ? 'الورق المروس: مفعل' : 'Letterhead: ON') : (isAr ? 'طباعة على ورق جاهز (خلفية بيضاء)' : 'Plain Paper') }}</span>
        </button>

        <!-- Copy Share Link -->
        <button
          type="button"
          @click="copyShareLink"
          class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 cursor-pointer transition-colors"
        >
          <component :is="isCopied ? CheckCircle2 : Share2" class="w-3.5 h-3.5" :class="isCopied ? 'text-emerald-600' : ''" />
          <span>{{ isCopied ? (isAr ? 'تم النسخ!' : 'Copied!') : (isAr ? 'مشاركة' : 'Share') }}</span>
        </button>

        <!-- New Invoice -->
        <Link
          :href="route('invoices.create')"
          class="hidden sm:flex px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 items-center gap-1.5 transition-colors"
        >
          <FilePlus2 class="w-3.5 h-3.5" />
          <span>{{ isAr ? 'فاتورة جديدة' : 'New Invoice' }}</span>
        </Link>

        <!-- Print Action -->
        <button
          type="button"
          @click="printInvoice"
          class="px-4 py-1.5 rounded-lg text-xs font-bold bg-blue-700 hover:bg-blue-800 text-white flex items-center gap-2 cursor-pointer shadow-sm active:scale-95 transition-all"
        >
          <Printer class="w-4 h-4" />
          <span>{{ isAr ? 'طباعة الفاتورة / PDF' : 'Print / Save PDF' }}</span>
        </button>
      </div>
    </div>

    <!-- The A4 Sheet Simulator Container -->
    <div class="invoice-container flex justify-center">
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
            <div class="flex items-start justify-between border-b border-blue-900/20 pb-3 mb-3">
              <div>
                <div class="flex items-center gap-2">
                  <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-800"></span>
                  <h1 class="text-xl font-black tracking-tight text-blue-950 font-serif">
                    {{ isArabicDoc ? 'فاتورة مبيعات ضريبية' : 'TAX & COMMERCIAL INVOICE' }}
                  </h1>
                </div>
                <p class="text-[11px] text-blue-800/80 font-bold mt-0.5">
                  {{ isArabicDoc ? 'شركة إم إند إتش للشحن والتصدير البحري' : 'MH For Export Logistics & Freight Management' }}
                </p>
              </div>

              <!-- Invoice Reference Badges -->
              <div class="text-left rtl:text-left ltr:text-right">
                <div class="inline-block px-2.5 py-1 bg-blue-950 text-white font-mono font-black text-sm rounded shadow-xs">
                  {{ invoice.invoice_number }}
                </div>
                <div class="text-[10px] text-slate-500 font-mono mt-1 font-semibold">
                  {{ isArabicDoc ? 'التاريخ:' : 'Date:' }} 
                  <span class="text-slate-900 font-bold">{{ invoice.issue_date }}</span>
                </div>
                <div class="text-[10px] text-slate-500 font-mono font-semibold">
                  {{ isArabicDoc ? 'الاستحقاق:' : 'Due:' }} 
                  <span class="text-slate-900 font-bold">{{ invoice.due_date }}</span>
                </div>
              </div>
            </div>

            <!-- Two-Column Information Box: Client (Billed To) & Shipment Logistics -->
            <div class="grid grid-cols-2 gap-3 mb-3">
              <!-- Billed To Box -->
              <div class="p-2.5 rounded-lg bg-blue-50/60 border border-blue-100 text-xs">
                <div class="flex items-center gap-1.5 text-blue-950 font-black text-[11px] uppercase tracking-wider mb-1.5 border-b border-blue-200/60 pb-1">
                  <Building2 class="w-3.5 h-3.5 text-blue-700" />
                  <span>{{ isArabicDoc ? 'بيانات العميل (المفوتر إليه)' : 'Billed To (Customer Details)' }}</span>
                </div>
                <div class="space-y-1 text-[11px]">
                  <div class="font-black text-slate-900 text-xs">
                    {{ invoice.party?.legal_name || 'عميل نقدي / Cash Customer' }}
                  </div>
                  <div v-if="invoice.party?.code" class="text-slate-600 font-mono text-[10px]">
                    <span class="text-slate-400">{{ isArabicDoc ? 'كود العميل:' : 'Code:' }}</span> 
                    <strong>{{ invoice.party.code }}</strong>
                  </div>
                  <div v-if="invoice.party?.tax_number" class="text-slate-600 font-mono text-[10px]">
                    <span class="text-slate-400">{{ isArabicDoc ? 'البطاقة الضريبية:' : 'Tax ID:' }}</span> 
                    <strong>{{ invoice.party.tax_number }}</strong>
                  </div>
                  <div class="text-slate-600 text-[10px]">
                    <span class="text-slate-400">{{ isArabicDoc ? 'العنوان:' : 'Address:' }}</span> 
                    {{ invoice.party?.address || (isArabicDoc ? 'الإسكندرية، مصر' : 'Alexandria, Egypt') }}
                  </div>
                </div>
              </div>

              <!-- Ocean Freight & Master Shipment Reference Box -->
              <div class="p-2.5 rounded-lg bg-slate-50/80 border border-slate-200 text-xs">
                <div class="flex items-center gap-1.5 text-blue-950 font-black text-[11px] uppercase tracking-wider mb-1.5 border-b border-slate-200 pb-1">
                  <Ship class="w-3.5 h-3.5 text-blue-700" />
                  <span>{{ isArabicDoc ? 'بيانات الشحنة والعملية البحرية' : 'Ocean Logistics Reference' }}</span>
                </div>
                <div class="space-y-1 text-[11px]">
                  <div class="flex justify-between font-mono">
                    <span class="text-slate-500 text-[10px]">{{ isArabicDoc ? 'ملف الشحنة:' : 'Master File:' }}</span>
                    <strong class="text-blue-950">{{ invoice.master_shipment?.file_number || (isArabicDoc ? 'خدمات عامة' : 'General Services') }}</strong>
                  </div>
                  <div v-if="invoice.master_shipment?.vessel_name" class="flex justify-between">
                    <span class="text-slate-500 text-[10px]">{{ isArabicDoc ? 'السفينة / الرحلة:' : 'Vessel/Voy:' }}</span>
                    <strong class="text-slate-900">{{ invoice.master_shipment.vessel_name }} ({{ invoice.master_shipment.voyage_number || 'Voy' }})</strong>
                  </div>
                  <!-- Master B/L removed from invoice view -->
                  <div class="flex justify-between text-[10px]">
                    <span class="text-slate-500">{{ isArabicDoc ? 'خط السير:' : 'Route:' }}</span>
                    <span class="font-bold text-slate-800 truncate">
                      {{ invoice.master_shipment?.pol?.name || (isArabicDoc ? 'ميناء الإسكندرية' : 'Alexandria Port') }} ➔ {{ invoice.master_shipment?.pod?.name || (isArabicDoc ? 'ميناء المقصد' : 'Destination') }}
                    </span>
                  </div>
                  <div v-if="invoice.master_shipment?.containers?.length" class="text-[10px] text-slate-600 truncate">
                    <span class="text-slate-500">{{ isArabicDoc ? 'الحاويات:' : 'Containers:' }}</span>
                    <span class="font-mono font-bold text-blue-900">
                      {{ invoice.master_shipment.containers.map(c => c.container_number).join(', ') }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Line Items Table -->
            <div class="border border-blue-950/20 rounded-lg overflow-hidden mb-3">
              <table class="w-full text-xs">
                <thead class="bg-blue-950 text-white text-[10px] font-bold uppercase tracking-wider">
                  <tr>
                    <th class="py-1.5 px-2 text-center w-8">#</th>
                    <th class="py-1.5 px-3" :class="isArabicDoc ? 'text-right' : 'text-left'">
                      {{ isArabicDoc ? 'بيان البند والخدمة' : 'Description & Charge Item' }}
                    </th>
                    <th class="py-1.5 px-2 text-center w-16">{{ isArabicDoc ? 'الرمز' : 'Code' }}</th>
                    <th class="py-1.5 px-2 text-center w-16">{{ isArabicDoc ? 'الكمية' : 'Qty' }}</th>
                    <th class="py-1.5 px-3 text-center w-24">{{ isArabicDoc ? 'سعر الوحدة' : 'Unit Price' }}</th>
                    <th class="py-1.5 px-3 text-center w-28">{{ isArabicDoc ? 'الإجمالي' : 'Total' }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                  <tr 
                    v-for="(item, idx) in invoice.items" 
                    :key="item.id || idx"
                    :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/50'"
                  >
                    <td class="py-2 px-2 text-center font-mono text-[11px] text-slate-500">{{ idx + 1 }}</td>
                    <td class="py-2 px-3">
                      <div class="font-bold text-slate-900 text-xs">
                        {{ item.description || item.charge_item?.name_ar || item.charge_item?.name_en }}
                      </div>
                      <div v-if="item.charge_item?.name_en && isArabicDoc" class="text-[9px] text-slate-500 font-mono">
                        {{ item.charge_item.name_en }}
                      </div>
                    </td>
                    <td class="py-2 px-2 text-center font-mono text-[10px] text-slate-600 font-bold">
                      {{ item.charge_item?.code || 'SRV' }}
                    </td>
                    <td class="py-2 px-2 text-center font-mono font-bold text-slate-900">
                      {{ Number(item.quantity) }}
                    </td>
                    <td class="py-2 px-3 text-center font-mono text-slate-700">
                      ${{ Number(item.unit_price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </td>
                    <td class="py-2 px-3 text-center font-mono font-black text-blue-950">
                      ${{ Number(item.line_total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </td>
                  </tr>

                  <!-- Fill blank rows if few items to keep proportion -->
                  <tr v-if="invoice.items?.length < 3" v-for="n in (3 - invoice.items.length)" :key="'blank-'+n" class="h-6">
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
            <div class="grid grid-cols-12 gap-3 mb-3 items-start">
              <!-- Left / Tafqeet & Payment Notice -->
              <div class="col-span-7 space-y-2">
                <div class="p-2.5 rounded-lg bg-blue-50/50 border border-blue-100 text-[11px]">
                  <div class="font-bold text-blue-950 mb-0.5">
                    {{ isArabicDoc ? 'المبلغ بالحروف (فقط لا غير):' : 'Amount in Words:' }}
                  </div>
                  <div class="font-bold text-slate-800 leading-relaxed font-serif">
                    {{ isArabicDoc ? numberToWordsAr(invoice.total_amount, invoice.currency_code) : numberToWordsEn(invoice.total_amount, invoice.currency_code) }}
                  </div>
                </div>

                <div class="text-[10px] text-slate-500 leading-tight space-y-0.5">
                  <p class="font-semibold text-slate-700">
                    {{ isArabicDoc ? '• السداد يتم عن طريق التحويل البنكي لحسابات الشركة المعتمدة الموضحة بالأسفل.' : '• Wire transfers must be made to company official accounts listed below.' }}
                  </p>
                  <p>
                    {{ isArabicDoc ? '• الخدمات التصديرية معفاة من ضريبة القيمة المضافة بموجب القانون رقم 67 لسنة 2016.' : '• Export services are VAT-exempted as per Egyptian tax law No. 67/2016.' }}
                  </p>
                </div>
              </div>

              <!-- Right / Totals Box -->
              <div class="col-span-5 bg-slate-50 rounded-lg border border-slate-200 p-2.5 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-600 text-[11px]">
                  <span>{{ isArabicDoc ? 'المجموع الفرعي:' : 'Subtotal:' }}</span>
                  <span class="font-mono font-bold text-slate-900">
                    ${{ Number(invoice.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                  </span>
                </div>
                <div class="flex justify-between text-slate-600 text-[11px]">
                  <span>{{ isArabicDoc ? 'ضريبة القيمة المضافة (0% تصدير):' : 'VAT (0% Export):' }}</span>
                  <span class="font-mono font-bold text-slate-900">$0.00</span>
                </div>
                <div class="border-t border-slate-300 pt-1.5 flex justify-between items-center text-blue-950">
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
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-4">
                  {{ isArabicDoc ? 'إعداد الحسابات' : 'Prepared By' }}
                </p>
                <div class="border-b border-slate-300 pb-0.5">
                  <span class="font-bold text-slate-800 text-[11px]">Financial Accountant</span>
                </div>
              </div>

              <!-- Official Blue Verification Circular Stamp -->
              <div class="flex flex-col items-center justify-center">
                <div class="w-20 h-20 rounded-full border-2 border-dashed border-blue-700/80 p-1 flex items-center justify-center text-center rotate-[-6deg] select-none hover:rotate-0 transition-transform">
                  <div class="w-full h-full rounded-full border border-blue-800 flex flex-col items-center justify-center bg-blue-50/40 text-blue-900">
                    <span class="text-[7px] font-mono font-black tracking-wider uppercase">MH FOR EXPORT</span>
                    <ShieldCheck class="w-3.5 h-3.5 text-blue-800 my-0.5" />
                    <span class="text-[6px] font-black tracking-widest uppercase">AUDITED & APPROVED</span>
                    <span class="text-[6px] font-mono text-blue-700">FINANCIAL CONTROL</span>
                  </div>
                </div>
              </div>

              <!-- Authorized Officer -->
              <div class="text-center w-36">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-4">
                  {{ isArabicDoc ? 'اعتماد الإدارة المالية' : 'Authorized Signatory' }}
                </p>
                <div class="border-b border-slate-300 pb-0.5">
                  <span class="font-bold text-slate-800 text-[11px]">Financial Controller</span>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Standard A4 Screen Simulation */
.invoice-sheet {
  width: 210mm;
  height: 297mm;
  max-height: 297mm;
  box-sizing: border-box;
  box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(15, 23, 42, 0.05);
  margin-bottom: 2rem;
  overflow: hidden;
}

/* When letterhead background is enabled */
.invoice-sheet.with-letterhead {
  background-image: url('/paper_025415.png');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: top center;
  /* Top 53mm gives exact clearance for the logo, angled polygon & cyan stripes */
  /* Bottom 43mm gives clearance for Alexandria branch address & bank accounts */
  padding-top: 53mm;
  padding-bottom: 43mm;
  padding-left: 18mm;
  padding-right: 18mm;
}

/* When printing on company pre-printed paper stock */
.invoice-sheet.plain-white {
  background: #ffffff;
  padding-top: 53mm;
  padding-bottom: 43mm;
  padding-left: 18mm;
  padding-right: 18mm;
}

/* High Fidelity 100% Exact A4 Print Stylesheet */
@media print {
  @page {
    size: A4 portrait;
    margin: 0;
  }

  html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    width: 210mm !important;
    height: 297mm !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .no-print {
    display: none !important;
  }

  .invoice-container {
    padding: 0 !important;
    margin: 0 !important;
    display: block !important;
  }

  .invoice-sheet {
    width: 210mm !important;
    height: 297mm !important;
    max-height: 297mm !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
    page-break-after: avoid !important;
    page-break-inside: avoid !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
