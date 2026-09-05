<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { ArrowLeft, ArrowRight, Plus, Trash2 } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  parties: Array,
  shipments: Array,
  charge_items: Array,
})

const form = useForm({
  invoice_type: 'CUSTOMER_INVOICE',
  party_id: '',
  master_shipment_id: '',
  currency_code: 'USD',
  exchange_rate: 1.0,
  issue_date: new Date().toISOString().split('T')[0],
  due_date: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0],
  items: [
    { charge_item_id: 1, description: 'Ocean Freight 40HR Cold Chain Tariff', quantity: 1, unit_price: 2400.00 },
    { charge_item_id: 2, description: 'Terminal Handling Charge (THC) POL', quantity: 1, unit_price: 250.00 }
  ]
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
          <UiSelect v-model="form.invoice_type" >
            <option value="CUSTOMER_INVOICE">{{ isAr ? 'فاتورة مبيعات عميل (CUSTOMER INVOICE)' : 'Customer Sales Invoice' }}</option>
            <option value="VENDOR_BILL">{{ isAr ? 'مطالبة تكلفة مورد (VENDOR BILL)' : 'Vendor Cost Bill' }}</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الطرف (العميل / المورد)' : 'Party (Customer / Vendor)' }}</label>
          <UiSelect v-model="form.party_id"  required>
            <option value="" disabled>{{ isAr ? 'اختر الطرف...' : 'Select Party...' }}</option>
            <option v-for="p in parties" :key="p.id" :value="p.id">{{ p.legal_name }} ({{ p.code }})</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الربط بشحنة ماستر' : 'Link to Master Shipment' }}</label>
          <UiSelect v-model="form.master_shipment_id" >
            <option value="">{{ isAr ? 'بدون ربط (مصروفات عامة)' : 'None (General Overhead)' }}</option>
            <option v-for="s in shipments" :key="s.id" :value="s.id">{{ s.file_number }} - {{ s.vessel_name }}</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'عملة الفاتورة' : 'Invoice Currency' }}</label>
          <UiSelect v-model="form.currency_code" >
            <option value="USD">USD ($)</option>
            <option value="EUR">EUR (€)</option>
            <option value="EGP">EGP (E£)</option>
          </UiSelect>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'سعر الصرف للدولار الأساسي' : 'FX Rate to Base USD' }}</label>
          <UiInput v-model="form.exchange_rate" type="number" step="0.0001"  required dir="ltr" / />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'تاريخ الإصدار' : 'Issue Date' }}</label>
          <UiInput v-model="form.issue_date" type="date"  required dir="ltr" / />
        </div>
      </div>

      <!-- Line Items List -->
      <div class="space-y-3 pt-4 border-t border-zinc-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold text-zinc-950 uppercase tracking-wider">{{ isAr ? 'بنود الفاتورة والخدمات' : 'Invoice Charge Line Items' }}</h3>
          <UiButton type="button" @click="addItem"  variant="secondary">
            <Plus class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'إضافة بند جديد' : 'Add Line Item' }}</span>
          </UiButton>
        </div>

        <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-2 items-center bg-zinc-50 p-3 rounded-lg border border-zinc-200">
          <div class="col-span-4">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'نوع البند' : 'Charge Type' }}</label>
            <UiSelect v-model="item.charge_item_id"  required>
              <option value="" disabled>{{ isAr ? 'اختر البند...' : 'Select Charge Item...' }}</option>
              <option v-for="ci in charge_items" :key="ci.id" :value="ci.id">{{ isAr ? ci.name_ar : ci.name_en }} ({{ ci.code }})</option>
            </UiSelect>
          </div>

          <div class="col-span-4">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'الوصف والتفاصيل' : 'Description' }}</label>
            <UiInput v-model="item.description" type="text" placeholder="التفاصيل..."  / />
          </div>

          <div class="col-span-1">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'الكمية' : 'Qty' }}</label>
            <UiInput v-model="item.quantity" type="number" step="0.1"  required dir="ltr" / />
          </div>

          <div class="col-span-2">
            <label class="text-[10px] text-zinc-500 font-bold uppercase">{{ isAr ? 'السعر' : 'Price' }}</label>
            <UiInput v-model="item.unit_price" type="number" step="0.01"  required dir="ltr" / />
          </div>

          <div class="col-span-1 text-right pt-4">
            <button type="button" @click="removeItem(idx)" class="p-2 text-zinc-400 hover:text-zinc-950 transition-colors cursor-pointer">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
        <UiButton type="submit" :disabled="form.processing"  variant="primary">
          {{ isAr ? 'ترحيل الفاتورة وإنشاء القيد' : 'Post Invoice & Create Journal Entry' }}
        </UiButton>
      </div>
    </form>
  </div>
</template>
