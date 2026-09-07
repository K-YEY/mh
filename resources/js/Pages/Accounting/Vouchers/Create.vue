<script setup>
import { computed, watch } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { ArrowLeft, ArrowRight } from 'lucide-vue-next'

const { isAr } = useI18n()
const page = usePage()
const baseCurrency = computed(() => page.props.base_currency || { code: 'EGP', symbol: 'E£' })

const props = defineProps({
  parties: Array,
  accounts: Array,
})

const form = useForm({
  voucher_type: 'RECEIPT',
  party_id: '',
  payment_method: 'BANK_TRANSFER',
  account_id: 1, // Bank Account
  amount: 5000.00,
  currency_code: baseCurrency.value.code || 'EGP',
  exchange_rate: 1.0,
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: 'TRF-990123',
})

watch(() => form.currency_code, (newCode) => {
  if (newCode === baseCurrency.value.code) {
    form.exchange_rate = 1.0
  } else if (newCode === 'USD') {
    form.exchange_rate = baseCurrency.value.code === 'EGP' ? 50.0 : 1.0
  } else if (newCode === 'EUR') {
    form.exchange_rate = baseCurrency.value.code === 'EGP' ? 54.5 : 1.085
  } else if (newCode === 'EGP') {
    form.exchange_rate = baseCurrency.value.code === 'USD' ? 0.02 : 1.0
  }
})

const submit = () => {
  form.post(route('vouchers.store'))
}
</script>

<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
      <Link :href="route('vouchers.index')" class="p-2 rounded-lg bg-zinc-100 text-zinc-600 hover:text-zinc-950 border border-zinc-200 transition-colors">
        <component :is="isAr ? ArrowRight : ArrowLeft" class="w-4 h-4" />
      </Link>
      <div>
        <h1 class="text-xl font-extrabold text-zinc-950 tracking-tight">
          {{ isAr ? 'إصدار سند قبض / صرف خزينة وبنك' : 'Record Treasury Voucher' }}
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'ترحيل قيود اليومية المحاسبية المزدوجة التلقائية لحسابات الخزينة والبنوك والعملاء والموردين' : 'Automated double-entry cash/bank ledger posting against parties' }}
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="shadcn-card p-6 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع السند' : 'Voucher Type' }}</label>
          <select v-model="form.voucher_type" class="shadcn-input">
            <option value="RECEIPT">{{ isAr ? 'سند قبض (استلام من عميل)' : 'Receipt Voucher (From Client)' }}</option>
            <option value="PAYMENT">{{ isAr ? 'سند صرف (سداد لمورد/خط ملاحي)' : 'Payment Voucher (To Vendor/Line)' }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الطرف المستفيد / العميل' : 'Party / Client' }}</label>
          <select v-model="form.party_id" class="shadcn-input" required>
            <option value="">{{ isAr ? '-- حدد الطرف ذو الصلة --' : '-- Select Party --' }}</option>
            <option v-for="p in parties" :key="p.id" :value="p.id">{{ p.legal_name }} ({{ p.code }})</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الحساب المالي (الخزينة / البنك)' : 'Treasury / Bank Account' }}</label>
          <select v-model="form.account_id" class="shadcn-input" required>
            <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ isAr ? a.name_ar : a.name_en }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'طريقة السداد' : 'Payment Method' }}</label>
          <select v-model="form.payment_method" class="shadcn-input">
            <option value="BANK_TRANSFER">{{ isAr ? 'تحويل بنكي / سويفت' : 'Bank Wire / SWIFT' }}</option>
            <option value="CASH">{{ isAr ? 'نقداً / خزينة' : 'Cash on Hand' }}</option>
            <option value="CHEQUE">{{ isAr ? 'شيك بنكي' : 'Bank Cheque' }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'المبلغ' : 'Amount' }}</label>
          <input v-model="form.amount" type="number" step="0.01" class="shadcn-input font-mono font-bold" required dir="ltr" />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'العملة' : 'Currency' }}</label>
          <select v-model="form.currency_code" class="shadcn-input">
            <option value="EGP">EGP (E£)</option>
            <option value="USD">USD ($)</option>
            <option value="EUR">EUR (€)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? ('سعر الصرف لعملة الأساس (' + baseCurrency.code + ')') : ('FX Rate to Base ' + baseCurrency.code) }}</label>
          <input v-model="form.exchange_rate" type="number" step="0.0001" class="shadcn-input font-mono" required dir="ltr" />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'تاريخ السداد / التحويل' : 'Payment Date' }}</label>
          <input v-model="form.payment_date" type="date" class="shadcn-input font-mono" required dir="ltr" />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم المرجع / التحويل السويفت' : 'Reference / Swift #' }}</label>
          <input v-model="form.reference_number" type="text" placeholder="e.g. SWIFT-88901" class="shadcn-input font-mono" dir="ltr" />
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
        <button type="submit" :disabled="form.processing" class="shadcn-btn-primary px-6 cursor-pointer">
          {{ isAr ? 'ترحيل واعتماد السند' : 'Process Treasury Voucher' }}
        </button>
      </div>
    </form>
  </div>
</template>
