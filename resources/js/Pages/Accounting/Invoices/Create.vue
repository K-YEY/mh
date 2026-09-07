<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { ArrowLeft, ArrowRight, Plus, Trash2 } from 'lucide-vue-next'

const { isAr } = useI18n()
const page = usePage()
const baseCurrency = computed(() => page.props.base_currency || { code: 'EGP', symbol: 'E£' })

const props = defineProps({
  parties: Array,
  shipments: Array,
  charge_items: Array,
})

const form = useForm({
  invoice_type: 'CUSTOMER_INVOICE',
  party_id: '',
  master_shipment_id: '',
  currency_code: baseCurrency.value.code || 'EGP',
  exchange_rate: 1.0,
  issue_date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0],
  items: [
    { charge_item_id: 1, description: 'Ocean Freight 40HR Cold Chain Tariff', quantity: 1, unit_price: 2400.00 },
    { charge_item_id: 2, description: 'Terminal Handling Charge (THC) POL', quantity: 1, unit_price: 250.00 }
  ]
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

const addItem = () => {
  form.items.push({ charge_item_id: '', description: '', quantity: 1, unit_price: 0.00 })
}

const removeItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

const submit = () => {
  form.post(route('invoices.store'))
}
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
      <Link :href="route('invoices.index')" class="p-2 rounded-lg bg-zinc-100 text-zinc-600 hover:text-zinc-950 border border-zinc-200 transition-colors">
        <component :is="isAr ? ArrowRight : ArrowLeft" class="w-4 h-4" />
      </Link>
      <div>
        <h1 class="text-xl font-extrabold text-zinc-950 tracking-tight">
          {{ isAr ? 'إصدار فاتورة عمليات أو مطالبة مورد' : 'Create Operations Invoice / Vendor Bill' }}
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'إنشاء قيد يومية آلي مزدوج (مدين العملاء/الموردين ودائن الإيرادات/المصروفات)' : 'Generates Double-Entry Journal Entry (Debits AR/AP and Credits Revenue/Expense)' }}
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="shadcn-card p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع المستند / الفاتورة' : 'Invoice Document Type' }}</label>
          <select v-model="form.invoice_type" class="shadcn-input">
            <option value="CUSTOMER_INVOICE">{{ isAr ? 'فاتورة مبيعات عميل (CUSTOMER INVOICE)' : 'Customer Sales Invoice' }}</option>
            <option value="VENDOR_BILL">{{ isAr ? 'مطالبة تكلفة مورد (VENDOR BILL)' : 'Vendor Cost Bill' }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الطرف (العميل / المورد)' : 'Party (Customer / Vendor)' }}</label>
          <select v-model="form.party_id" class="shadcn-input" required>
            <option value="" disabled>{{ isAr ? 'اختر الطرف...' : 'Select Party...' }}</option>
            <option v-for="p in parties" :key="p.id" :value="p.id">{{ p.legal_name }} ({{ p.code }})</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الربط بشحنة ماستر' : 'Link to Master Shipment' }}</label>
          <select v-model="form.master_shipment_id" class="shadcn-input">
            <option value="">{{ isAr ? 'بدون ربط (مصروفات عامة)' : 'None (General Overhead)' }}</option>
            <option v-for="s in shipments" :key="s.id" :value="s.id">{{ s.file_number }} - {{ s.vessel_name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'عملة الفاتورة' : 'Invoice Currency' }}</label>
          <select v-model="form.currency_code" class="shadcn-input">
            <option value="USD">USD ($)</option>
            <option value="EUR">EUR (€)</option>
            <option value="EGP">EGP (E£)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? ('سعر الصرف لعملة الأساس (' + baseCurrency.code + ')') : ('FX Rate to Base ' + baseCurrency.code) }}</label>
          <input v-model="form.exchange_rate" type="number" step="0.0001" class="shadcn-input font-mono" required dir="ltr" />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'تاريخ الإصدار' : 'Issue Date' }}</label>
          <input v-model="form.issue_date" type="date" class="shadcn-input font-mono" required dir="ltr" />
        </div>
      </div>

      <!-- Line Items List -->
      <div class="space-y-3 pt-4 border-t border-zinc-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold text-zinc-950 uppercase tracking-wider">{{ isAr ? 'بنود الفاتورة والخدمات' : 'Invoice Charge Line Items' }}</h3>
          <button type="button" @click="addItem" class="shadcn-btn-secondary text-xs h-8 px-3 gap-1 cursor-pointer">
            <Plus class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'إضافة بند جديد' : 'Add Line Item' }}</span>
          </button>
        </div>

        <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-2 items-center bg-zinc-50 p-3 rounded-lg border border-zinc-200">
          <div class="col-span-4">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'نوع البند' : 'Charge Type' }}</label>
            <select v-model="item.charge_item_id" class="shadcn-input" required>
              <option value="" disabled>{{ isAr ? 'اختر البند...' : 'Select Charge Item...' }}</option>
              <option v-for="ci in charge_items" :key="ci.id" :value="ci.id">{{ isAr ? ci.name_ar : ci.name_en }} ({{ ci.code }})</option>
            </select>
          </div>

          <div class="col-span-4">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'الوصف والتفاصيل' : 'Description' }}</label>
            <input v-model="item.description" type="text" placeholder="التفاصيل..." class="shadcn-input" />
          </div>

          <div class="col-span-1">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'الكمية' : 'Qty' }}</label>
            <input v-model="item.quantity" type="number" step="0.1" class="shadcn-input font-mono" required dir="ltr" />
          </div>

          <div class="col-span-2">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'السعر' : 'Price' }}</label>
            <input v-model="item.unit_price" type="number" step="0.01" class="shadcn-input font-mono" required dir="ltr" />
          </div>

          <div class="col-span-1 text-right pt-4">
            <button type="button" @click="removeItem(idx)" class="p-2 text-zinc-400 hover:text-zinc-950 transition-colors cursor-pointer">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
        <button type="submit" :disabled="form.processing" class="shadcn-btn-primary px-6 cursor-pointer">
          {{ isAr ? 'ترحيل الفاتورة وإنشاء القيد' : 'Post Invoice & Create Journal Entry' }}
        </button>
      </div>
    </form>
  </div>
</template>
