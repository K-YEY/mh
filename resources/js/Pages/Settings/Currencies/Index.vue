<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { useCurrency } from '@/Composables/useCurrency'
import { DollarSign, Plus, Coins, RefreshCw, CheckCircle2, Pencil, Trash2, Eye } from 'lucide-vue-next'

const { isAr } = useI18n()
const { currentCurrency, switchCurrency, getFlag } = useCurrency()

const props = defineProps({
  currencies: Array,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const settingBase = ref(null)
const syncingFx = ref(false)

const form = useForm({
  code: '',
  name: '',
  symbol: '',
  decimal_places: 2,
})

const editForm = useForm({
  code: '',
  name: '',
  symbol: '',
  decimal_places: 2,
  is_active: true,
})

const submit = () => {
  form.post(route('currencies.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}

const openEditModal = (c) => {
  editForm.code = c.code
  editForm.name = c.name
  editForm.symbol = c.symbol
  editForm.decimal_places = c.decimal_places ?? 2
  editForm.is_active = c.is_active !== false
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(route('currencies.update', editForm.code), {
    onSuccess: () => {
      showEditModal.value = false
    }
  })
}

const deleteCurrency = (c) => {
  if (confirm(isAr.value ? `هل أنت متأكد من حذف العملة (${c.code} - ${c.name})؟` : `Delete currency (${c.code})?`)) {
    router.delete(route('currencies.destroy', c.code))
  }
}

const setBaseCurrency = (code) => {
  if (confirm(isAr.value ? `هل أنت متأكد من تعيين ${code} كعملة أساسية للنظام؟ سيتم إعادة حساب أسعار الصرف تلقائياً.` : `Set ${code} as base system currency? Rates will be recalculated.`)) {
    settingBase.value = code
    router.post(route('currencies.set-base', { currency: code }), {}, {
      onFinish: () => settingBase.value = null
    })
  }
}

const syncFxRates = () => {
  syncingFx.value = true
  router.post(route('exchange-rates.sync'), {}, {
    onFinish: () => syncingFx.value = false
  })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Coins class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'عملات النظام والعملة الأساسية' : 'System Multi-Currencies' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'إعدادات العملة الأساسية للنظام وتعدد العملات بدفاتر الحسابات' : 'Base Currency Configuration & Multi-Currency Ledger Precision' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="syncFxRates"
          :disabled="syncingFx"
          class="shadcn-btn-outline gap-1.5 shadow-xs cursor-pointer text-xs"
          :title="isAr ? 'تحديث أسعار العملات الحية آلياً عبر Google / Live FX' : 'Sync Live FX Rates'"
        >
          <RefreshCw class="w-3.5 h-3.5 text-blue-700" :class="{ 'animate-spin': syncingFx }" />
          <span>{{ syncingFx ? (isAr ? 'جاري التحديث...' : 'Syncing...') : (isAr ? 'تحديث أسعار الصرف آلياً (Live FX)' : 'Sync Live Rates') }}</span>
        </button>

        <button
          @click="showCreateModal = true"
          class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
        >
          <Plus class="w-4 h-4 font-bold" />
          <span>{{ isAr ? 'إضافة عملة جديدة' : 'Add Currency' }}</span>
        </button>
      </div>
    </div>

    <!-- Currencies Table Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <DollarSign class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `عملات النظام المفعّلة (${currencies.length} عملة)` : `Active System Currencies (${currencies.length} Currencies)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'كود العملة' : 'Currency Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم العملة' : 'Currency Name' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الرمز' : 'Symbol' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الكسور العشرية' : 'Decimals' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'حالة العملة الأساسية' : 'Base Currency Status' }}</th>
              <th class="px-4 py-3 text-center">{{ isAr ? 'التحكم والإجراءات' : 'Actions' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="c in currencies" :key="c.code" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950 text-sm">
                <div class="flex items-center gap-2">
                  <span class="text-lg leading-none">{{ getFlag(c.code) }}</span>
                  <span>{{ c.code }}</span>
                </div>
              </td>
              <td class="px-4 py-3 font-bold text-zinc-950">
                {{ c.name }}
              </td>
              <td class="px-4 py-3 font-mono text-zinc-950 font-bold text-sm">
                {{ c.symbol }}
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600">
                {{ c.decimal_places }} {{ isAr ? 'خانة عشرية' : 'Places' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-1.5">
                  <span v-if="c.is_base_currency" class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                    <span>{{ isAr ? 'العملة الأساسية' : 'BASE' }}</span>
                  </span>
                  <span v-if="c.code === currentCurrency.code" class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ isAr ? 'معروضة حالياً' : 'ACTIVE DISPLAY' }}</span>
                  </span>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Switch Display Currency Button -->
                  <button
                    v-if="c.code !== currentCurrency.code"
                    type="button"
                    @click="switchCurrency(c.code)"
                    class="px-2 py-1 rounded-lg text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 flex items-center gap-1 transition shadow-2xs cursor-pointer"
                    :title="isAr ? 'عرض بيانات النظام بهذه العملة' : 'Switch System Display'"
                  >
                    <Eye class="w-3.5 h-3.5 text-emerald-600" />
                    <span>{{ isAr ? 'عرض بها' : 'Display' }}</span>
                  </button>

                  <!-- Edit Button -->
                  <button
                    type="button"
                    @click="openEditModal(c)"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-blue-700 bg-blue-50/80 hover:bg-blue-100/90 border border-blue-200/80 flex items-center gap-1 transition shadow-xs cursor-pointer"
                    :title="isAr ? 'تعديل بيانات العملة' : 'Edit Currency'"
                  >
                    <Pencil class="w-3.5 h-3.5 text-blue-600" />
                    <span>{{ isAr ? 'تعديل' : 'Edit' }}</span>
                  </button>

                  <!-- Set as base -->
                  <button
                    v-if="!c.is_base_currency"
                    @click="setBaseCurrency(c.code)"
                    :disabled="settingBase === c.code"
                    class="shadcn-btn-secondary py-1 px-2 text-[11px] font-bold gap-1 cursor-pointer hover:border-blue-400 hover:text-blue-900 inline-flex items-center"
                    :title="isAr ? 'تعيين كعملة أساسية للنظام' : 'Set as Base Currency'"
                  >
                    <RefreshCw v-if="settingBase === c.code" class="w-3 h-3 animate-spin" />
                    <Coins v-else class="w-3 h-3 text-zinc-600" />
                    <span>{{ isAr ? 'تعيين كأساس' : 'Set Base' }}</span>
                  </button>
                  <span v-else class="text-[11px] font-bold text-emerald-700 font-mono flex items-center gap-1 px-1.5">
                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
                    <span>{{ isAr ? 'الأساسية' : 'Base' }}</span>
                  </span>

                  <!-- Delete Button -->
                  <button
                    v-if="!c.is_base_currency"
                    type="button"
                    @click="deleteCurrency(c)"
                    class="p-1 rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                    :title="isAr ? 'حذف العملة' : 'Delete Currency'"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!currencies?.length">
              <td colspan="6" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد عملات مسجلة للنظام حالياً' : 'No currencies found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl rounded-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
            <Plus class="w-4 h-4 text-zinc-950" />
            <span>{{ isAr ? 'إضافة عملة نظام جديدة' : 'Add System Currency' }}</span>
          </h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">كود العملة (ISO 3 Chars)</label>
            <input v-model="form.code" type="text" placeholder="e.g. SAR, AED, GBP" maxlength="3" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم العملة' : 'Currency Name' }}</label>
            <input v-model="form.name" type="text" placeholder="e.g. Saudi Riyal (ريال سعودي)" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رمز العملة' : 'Currency Symbol' }}</label>
            <input v-model="form.symbol" type="text" placeholder="e.g. ر.س, $, €" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'عدد الخانات العشرية' : 'Decimal Places' }}</label>
            <input v-model="form.decimal_places" type="number" min="0" max="4" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ العملة' : 'Save Currency' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Currency Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl rounded-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
            <Pencil class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? `تعديل بيانات العملة: ${editForm.code}` : `Edit Currency: ${editForm.code}` }}</span>
          </h3>
          <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'كود العملة' : 'Currency Code' }}</label>
            <input :value="editForm.code" disabled type="text" class="shadcn-input font-mono bg-zinc-100 text-zinc-500 cursor-not-allowed uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم العملة' : 'Currency Name' }}</label>
            <input v-model="editForm.name" type="text" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رمز العملة' : 'Currency Symbol' }}</label>
            <input v-model="editForm.symbol" type="text" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'عدد الخانات العشرية' : 'Decimal Places' }}</label>
            <input v-model="editForm.decimal_places" type="number" min="0" max="4" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showEditModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="editForm.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ التعديلات' : 'Save Changes' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
