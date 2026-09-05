<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { ArrowLeft, ArrowRight } from 'lucide-vue-next'

const { isAr } = useI18n()

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
  currency_code: 'USD',
  exchange_rate: 1.0,
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: 'TRF-990123',
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
          {{ isAr ? 'ترحيل المقبوضات والمدفوعات إلى دفتر اليومية مع التسوية الآلية للعملاء والموردين' : 'Post Receipt / Payment to General Ledger with Sub-ledger Reconciliation' }}
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="shadcn-card p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع السند' : 'Voucher Type' }}</label>
          <UiSelect v-model="form.voucher_type" >
            <option value="RECEIPT">{{ isAr ? 'سند قبض (Receipt Voucher - عملاء)' : 'Receipt Voucher (Customer AR)' }}</option>
            <option value="PAYMENT">{{ isAr ? 'سند صرف (Payment Voucher - موردين)' : 'Payment Voucher (Vendor AP)' }}</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الطرف (العميل / المورد)' : 'Party' }}</label>
          <UiSelect v-model="form.party_id"  required>
            <option value="" disabled>{{ isAr ? 'اختر الطرف...' : 'Select Party...' }}</option>
            <option v-for="p in parties" :key="p.id" :value="p.id">{{ p.legal_name }} ({{ p.code }})</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'طريقة الدفع / التحويل' : 'Payment Method' }}</label>
          <UiSelect v-model="form.payment_method" >
            <option value="BANK_TRANSFER">{{ isAr ? 'تحويل بنكي (Bank Wire Transfer)' : 'Bank Wire Transfer' }}</option>
            <option value="CASH">{{ isAr ? 'نقداً بالخزينة (Cash in Hand)' : 'Cash in Hand' }}</option>
            <option value="CHEQUE">{{ isAr ? 'شيك بنكي (Bank Cheque)' : 'Bank Cheque' }}</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'حساب الخزينة / البنك' : 'Bank / Cash Account' }}</label>
          <UiSelect v-model="form.account_id"  required>
            <option v-for="a in accounts" :key="a.id" :value="a.id">{{ isAr ? a.name_ar : a.name_en }} ({{ a.code }})</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'المبلغ' : 'Amount' }}</label>
          <UiInput v-model="form.amount" type="number" step="0.01"  required dir="ltr" / />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'العملة' : 'Currency' }}</label>
          <UiSelect v-model="form.currency_code" >
            <option value="USD">USD ($)</option>
            <option value="EUR">EUR (€)</option>
            <option value="EGP">EGP (E£)</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'تاريخ السداد / التحويل' : 'Payment Date' }}</label>
          <UiInput v-model="form.payment_date" type="date"  required dir="ltr" / />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رقم المرجع / التحويل السويفت' : 'Reference / Swift #' }}</label>
          <UiInput v-model="form.reference_number" type="text" placeholder="e.g. SWIFT-88901"  dir="ltr" / />
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
        <UiButton type="submit" :disabled="form.processing"  variant="primary">
          {{ isAr ? 'ترحيل واعتماد السند' : 'Process Treasury Voucher' }}
        </UiButton>
      </div>
    </form>
  </div>
</template>
