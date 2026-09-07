<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Wallet, Printer, ArrowLeft, ArrowRight, CheckCircle2, Building2, Calendar, FileText, Scale } from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  voucher: Object,
})

const printVoucher = () => {
  window.print()
}

const isReceipt = props.voucher.voucher_type === 'RECEIPT'

const formatMoney = (val, currency) => {
  const num = parseFloat(val) || 0
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (currency || props.voucher.currency_code)
}
</script>

<template>
  <div class="space-y-6 max-w-4xl mx-auto print:p-0 print:m-0 print:max-w-none">
    <!-- Screen Actions Bar (Hidden on print) -->
    <div class="flex items-center justify-between print:hidden">
      <Link
        :href="route('vouchers.index')"
        class="text-xs font-bold text-zinc-600 hover:text-blue-700 flex items-center gap-1.5 transition-colors"
      >
        <component :is="isAr ? ArrowRight : ArrowLeft" class="w-4 h-4" />
        <span>{{ isAr ? 'العودة إلى قائمة السندات' : 'Back to Vouchers' }}</span>
      </Link>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="printVoucher"
          class="px-4 py-2 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded shadow-xs flex items-center gap-2 transition-colors cursor-pointer"
        >
          <Printer class="w-4 h-4" />
          <span>{{ isAr ? 'طباعة السند الرسمي' : 'Print Official Voucher' }}</span>
        </button>
      </div>
    </div>

    <!-- Official Voucher Document Card -->
    <div class="shadcn-card bg-white border border-zinc-300 p-8 sm:p-10 shadow-sm print:border-0 print:shadow-none print:p-6 space-y-6">
      <!-- Document Header -->
      <div class="flex items-start justify-between border-b-2 border-blue-900 pb-5">
        <div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded bg-blue-900 text-white flex items-center justify-center font-black text-xl tracking-wider">
              MH
            </div>
            <div>
              <h1 class="text-lg font-black tracking-tight text-blue-950 uppercase">M&H Logistics & Agricultural Export</h1>
              <p class="text-[11px] text-zinc-500 font-medium">Ocean Freight Logistics & Global Trade ERP</p>
            </div>
          </div>
          <div class="text-[10px] text-zinc-500 font-mono mt-2 space-y-0.5">
            <div>Banque Misr Accounts: EGP 100064715498 | USD 100064715528</div>
            <div>Alexandria / Port Said Port Operations</div>
          </div>
        </div>

        <div class="text-right space-y-1">
          <div 
            class="inline-block px-3 py-1 rounded text-xs font-extrabold uppercase tracking-wide border font-mono"
            :class="isReceipt ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : 'bg-amber-50 text-amber-900 border-amber-300'"
          >
            {{ isReceipt ? (isAr ? 'سند قبض نقدية / بنك' : 'RECEIPT VOUCHER') : (isAr ? 'سند صرف نقدية / بنك' : 'PAYMENT VOUCHER') }}
          </div>
          <div class="font-mono font-bold text-zinc-950 text-base">
            {{ voucher.voucher_number }}
          </div>
          <div class="text-xs text-zinc-500 font-mono">
            {{ isAr ? 'التاريخ: ' : 'Date: ' }}{{ formatDateTime(voucher.created_at || voucher.payment_date) }}
          </div>
        </div>
      </div>

      <!-- Voucher Metadata Fields -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-zinc-50 rounded border border-zinc-200 text-xs">
        <div>
          <span class="text-zinc-500 text-[10px] block uppercase font-bold">{{ isAr ? 'طريقة السداد' : 'Payment Method' }}</span>
          <span class="font-bold text-zinc-900 font-mono text-xs">{{ voucher.payment_method }}</span>
        </div>
        <div>
          <span class="text-zinc-500 text-[10px] block uppercase font-bold">{{ isAr ? 'المرجع / الشيك' : 'Reference / Cheque #' }}</span>
          <span class="font-bold text-zinc-900 font-mono text-xs">{{ voucher.reference_number || '-' }}</span>
        </div>
        <div>
          <span class="text-zinc-500 text-[10px] block uppercase font-bold">{{ isAr ? 'حساب الخزينة / البنك' : 'Treasury Account' }}</span>
          <span class="font-bold text-blue-900 text-xs">{{ isAr ? voucher.account?.name_ar : voucher.account?.name_en }} ({{ voucher.account?.code }})</span>
        </div>
        <div>
          <span class="text-zinc-500 text-[10px] block uppercase font-bold">{{ isAr ? 'سعر الصرف' : 'FX Rate' }}</span>
          <span class="font-bold text-zinc-900 font-mono text-xs">1 {{ voucher.currency_code }} = {{ Number(voucher.exchange_rate).toFixed(4) }} USD</span>
        </div>
      </div>

      <!-- Body / Party Statement -->
      <div class="space-y-4 text-xs">
        <div class="p-4 rounded border border-zinc-200 bg-white space-y-3">
          <!-- Beneficiary / Payer Line -->
          <div class="flex items-center justify-between border-b border-zinc-100 pb-2.5">
            <span class="font-bold text-zinc-600">
              {{ isReceipt ? (isAr ? 'وصلنا من السيد / السادة:' : 'Received From:') : (isAr ? 'يُصرف للسيد / السادة:' : 'Pay To / Beneficiary:') }}
            </span>
            <span class="font-extrabold text-sm text-zinc-950">
              {{ voucher.party?.legal_name }}
              <span class="text-xs font-mono font-normal text-zinc-500">({{ voucher.party?.party_type }})</span>
            </span>
          </div>

          <!-- Amount Line -->
          <div class="flex items-center justify-between border-b border-zinc-100 pb-2.5">
            <span class="font-bold text-zinc-600">
              {{ isAr ? 'المبلغ المستلم / المدفوع:' : 'Amount Paid / Received:' }}
            </span>
            <div class="text-right">
              <span class="text-xl font-extrabold font-mono text-blue-900">
                {{ formatMoney(voucher.amount, voucher.currency_code) }}
              </span>
            </div>
          </div>

          <!-- Explanation / Note -->
          <div class="flex items-start justify-between">
            <span class="font-bold text-zinc-600">
              {{ isAr ? 'وذلك عن / البيان:' : 'Being / For:' }}
            </span>
            <span class="font-medium text-zinc-800 text-right max-w-lg">
              {{ isReceipt 
                ? (isAr ? `تحصيل نقدية / بنكي تحت حساب شحنات وفواتير العميل ${voucher.party?.legal_name}` : `Receipt on account for shipments/invoices`) 
                : (isAr ? `صرف نقدية / بنكي لمستحقات المورد / مقاول النقل ${voucher.party?.legal_name}` : `Payment on account for freight/customs provider`) 
              }}
            </span>
          </div>
        </div>
      </div>

      <!-- Auto-Generated Double-Entry Audit Link -->
      <div v-if="voucher.journal_entry" class="space-y-2">
        <div class="flex items-center justify-between text-xs">
          <div class="font-bold text-zinc-900 flex items-center gap-1.5">
            <Scale class="w-4 h-4 text-blue-700" />
            <span>{{ isAr ? 'القيد المحاسبي المزدوج المُرحل آلياً' : 'Automated Double-Entry Journal Voucher' }}</span>
            <span class="font-mono text-blue-800 font-bold">({{ voucher.journal_entry.entry_number }})</span>
          </div>
          <span class="text-[11px] font-mono text-emerald-700 font-bold flex items-center gap-1">
            <CheckCircle2 class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'مُرحل في دفتر اليومية' : 'Posted to GL' }}</span>
          </span>
        </div>

        <div class="border border-zinc-200 rounded overflow-hidden">
          <table class="w-full text-xs font-mono" :class="isAr ? 'text-right' : 'text-left'">
            <thead class="bg-zinc-100 text-zinc-600 text-[10px] uppercase border-b border-zinc-200">
              <tr>
                <th class="p-2.5">{{ isAr ? 'كود الحساب' : 'Account Code' }}</th>
                <th class="p-2.5 font-sans">{{ isAr ? 'اسم الحساب' : 'Account Name' }}</th>
                <th class="p-2.5 text-right">{{ isAr ? 'مدين ($)' : 'Debit ($)' }}</th>
                <th class="p-2.5 text-right">{{ isAr ? 'دائن ($)' : 'Credit ($)' }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
              <tr v-for="line in voucher.journal_entry.lines" :key="line.id" class="hover:bg-zinc-50">
                <td class="p-2.5 font-bold text-zinc-900">{{ line.account?.code }}</td>
                <td class="p-2.5 font-sans text-zinc-800">{{ isAr ? line.account?.name_ar : line.account?.name_en }}</td>
                <td class="p-2.5 text-right font-bold">{{ line.debit > 0 ? '$' + Number(line.debit).toFixed(2) : '-' }}</td>
                <td class="p-2.5 text-right font-bold">{{ line.credit > 0 ? '$' + Number(line.credit).toFixed(2) : '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Formal Signature Blocks -->
      <div class="pt-8 border-t border-zinc-200">
        <div class="grid grid-cols-4 gap-4 text-center text-xs">
          <div class="space-y-8">
            <div class="font-bold text-zinc-700">{{ isAr ? 'إعداد المحاسب' : 'Accountant' }}</div>
            <div class="border-b border-dashed border-zinc-400 mx-4"></div>
          </div>
          <div class="space-y-8">
            <div class="font-bold text-zinc-700">{{ isAr ? 'المراجعة والتدقيق' : 'Internal Auditor' }}</div>
            <div class="border-b border-dashed border-zinc-400 mx-4"></div>
          </div>
          <div class="space-y-8">
            <div class="font-bold text-zinc-700">{{ isAr ? 'المدير المالي' : 'Chief Financial Officer' }}</div>
            <div class="border-b border-dashed border-zinc-400 mx-4"></div>
          </div>
          <div class="space-y-8">
            <div class="font-bold text-zinc-700">{{ isReceipt ? (isAr ? 'المستلم' : 'Cashier') : (isAr ? 'توقيع المستلم' : 'Receiver Sign') }}</div>
            <div class="border-b border-dashed border-zinc-400 mx-4"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
