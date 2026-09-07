<script setup>
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Plus, TrendingUp, RefreshCw, Globe, Radio, Zap, Trash2, Pencil, CheckCircle2 } from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  currencies: Array,
  rates: Object,
  base_currency: Object,
  live_api: Object,
})

const syncingFx = ref(false)
const showEditModal = ref(false)

const form = useForm({
  currency_code: 'USD',
  rate_to_base: 50.0000,
  effective_date: new Date().toISOString().split('T')[0],
})

const editForm = useForm({
  id: null,
  currency_code: '',
  rate_to_base: 1.0,
  effective_date: '',
})

watch(() => form.currency_code, (newCode) => {
  const match = props.live_api?.rates?.find(r => r.code === newCode)
  if (match) {
    form.rate_to_base = match.rate_to_base
  }
})

const submit = () => {
  form.post(route('exchange-rates.store'), {
    onSuccess: () => form.reset(),
  })
}

const openEditModal = (r) => {
  editForm.id = r.id
  editForm.currency_code = r.currency_code
  editForm.rate_to_base = Number(r.rate_to_base)
  editForm.effective_date = r.effective_date ? r.effective_date.split('T')[0] : new Date().toISOString().split('T')[0]
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(route('exchange-rates.update', editForm.id), {
    onSuccess: () => {
      showEditModal.value = false
    }
  })
}

const syncFxRates = () => {
  syncingFx.value = true
  router.post(route('exchange-rates.sync'), {}, {
    onFinish: () => syncingFx.value = false
  })
}

const applyLiveRate = (item) => {
  form.currency_code = item.code
  form.rate_to_base = item.rate_to_base
}

const fillSelectedFromLiveApi = () => {
  const match = props.live_api?.rates?.find(r => r.code === form.currency_code)
  if (match) {
    form.rate_to_base = match.rate_to_base
  }
}

const deleteRate = (rate) => {
  if (confirm(isAr.value ? `هل أنت متأكد من حذف سجل سعر صرف ${rate.currency_code}؟` : `Are you sure you want to delete ${rate.currency_code} rate record?`)) {
    router.delete(route('exchange-rates.destroy', rate.id))
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <TrendingUp class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'أسعار صرف العملات الأجنبية (FX Matrix)' : 'Foreign Exchange Rates' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-0.5">
          {{ isAr ? `مصفوفة أسعار الصرف المباشرة من الـ API مقابل عملة الأساس (${base_currency?.code || 'EGP'}) للعمليات والفواتير` : `Direct API Foreign Exchange Matrix relative to Base (${base_currency?.code || 'EGP'})` }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="syncFxRates"
          :disabled="syncingFx"
          class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
        >
          <RefreshCw class="w-3.5 h-3.5 text-white" :class="{ 'animate-spin': syncingFx }" />
          <span>{{ syncingFx ? (isAr ? 'جاري السحب من الـ API...' : 'Syncing Live API...') : (isAr ? 'تحديث وتثبيت أسعار الـ API المباشرة' : 'Sync Direct from Live API') }}</span>
        </button>
      </div>
    </div>

    <!-- Live Direct API Feed Cards -->
    <div v-if="live_api?.rates?.length" class="shadcn-card p-4 space-y-3 bg-gradient-to-r from-blue-50/50 via-white to-zinc-50 border border-blue-200">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-blue-100 pb-2.5">
        <div class="flex items-center gap-2">
          <Radio class="w-4 h-4 text-emerald-600 animate-pulse" />
          <span class="text-xs font-extrabold text-blue-950 uppercase tracking-wide">
            {{ isAr ? 'بث أسعار العملات اللحظي من الـ API مباشرة (Direct Live API Feed)' : 'Direct Real-Time Live API Feed' }}
          </span>
          <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
            {{ isAr ? 'سحب مباشر من الـ API' : 'DIRECT API FEED' }}
          </span>
        </div>
        <div class="flex items-center gap-3 text-[11px] font-mono text-zinc-500">
          <span>{{ live_api.provider }}</span>
          <span>•</span>
          <span>{{ formatDateTime(live_api.updated_at) }}</span>
        </div>
      </div>

      <!-- Quick Tickers Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5">
        <div 
          v-for="rate in live_api.rates" 
          :key="rate.code"
          @click="applyLiveRate(rate)"
          class="p-2.5 rounded-lg bg-white border border-zinc-200 hover:border-blue-500 hover:shadow-xs transition-all cursor-pointer group relative"
          :title="isAr ? 'انقر لتعبئة هذا السعر المباشر بالنموذج' : 'Click to copy live rate into form'"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono font-bold text-zinc-950">{{ rate.code }}</span>
            <span class="text-[10px] font-mono text-zinc-400">{{ rate.symbol }}</span>
          </div>
          <div class="mt-1 text-sm font-extrabold text-blue-950 font-mono group-hover:text-blue-700">
            {{ Number(rate.rate_to_base).toFixed(4) }}
            <span class="text-[10px] font-sans font-normal text-zinc-500">{{ base_currency?.symbol || 'E£' }}</span>
          </div>
          <div class="text-[9px] text-zinc-500 font-sans mt-0.5 truncate flex items-center justify-between">
            <span>{{ isAr ? rate.name : rate.name_en }}</span>
            <Zap class="w-3 h-3 text-amber-500 opacity-0 group-hover:opacity-100 transition-opacity" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Active Rates Table Card -->
      <div class="lg:col-span-2 shadcn-card overflow-hidden">
        <div class="p-3.5 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
          <div class="flex items-center gap-2 text-xs font-bold text-zinc-900">
            <Globe class="w-4 h-4 text-blue-700" />
            <span>{{ isAr ? `جدول أسعار العملات مقابل الأساس (${base_currency?.name || 'الجنيه المصري'} - ${base_currency?.code || 'EGP'})` : `Currency Matrix relative to Base (${base_currency?.code || 'EGP'})` }}</span>
          </div>
          <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-900">
            1 {{ base_currency?.code || 'EGP' }} = 1.000000
          </span>
        </div>

        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="p-4">{{ isAr ? 'كود العملة' : 'Currency Code' }}</th>
              <th class="p-4">{{ isAr ? `سعر الصرف مقابل الأساس (${base_currency?.code || 'EGP'})` : `Rate to Base (${base_currency?.code || 'EGP'})` }}</th>
              <th class="p-4">{{ isAr ? 'تاريخ السريان' : 'Effective Date' }}</th>
              <th class="p-4">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="p-4 text-center">{{ isAr ? 'إجراءات' : 'Actions' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-mono">
            <tr v-for="r in rates.data" :key="r.id" class="hover:bg-zinc-50 transition-colors">
              <td class="p-4 text-zinc-950 font-bold text-sm">
                {{ r.currency_code }}
              </td>
              <td class="p-4 text-zinc-950 font-bold">
                {{ Number(r.rate_to_base).toFixed(6) }} {{ base_currency?.symbol || 'E£' }}
              </td>
              <td class="p-4 text-zinc-700 font-mono text-[11px]">
                {{ formatDateTime(r.created_at || r.effective_date) }}
              </td>
              <td class="p-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
              </td>
              <td class="p-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    type="button"
                    @click="openEditModal(r)"
                    class="px-2 py-1 rounded-lg text-xs font-bold text-blue-700 bg-blue-50/80 hover:bg-blue-100/90 border border-blue-200/80 flex items-center gap-1 transition shadow-xs cursor-pointer"
                    :title="isAr ? 'تعديل سعر الصرف' : 'Edit FX Rate'"
                  >
                    <Pencil class="w-3.5 h-3.5 text-blue-600" />
                    <span>{{ isAr ? 'تعديل' : 'Edit' }}</span>
                  </button>

                  <button
                    type="button"
                    @click="deleteRate(r)"
                    class="p-1 rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer"
                    :title="isAr ? 'حذف هذا السجل' : 'Delete this record'"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!rates.data?.length">
              <td colspan="5" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد أسعار صرف مسجلة حالياً' : 'No exchange rates recorded yet.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Update Exchange Rate Form Card -->
      <div class="shadcn-card p-5 space-y-4 h-fit">
        <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
          <Plus class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'تحديث سعر صرف مخصص' : 'Update Custom FX Spot Rate' }}</span>
        </h3>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'العملة المستهدفة' : 'Target Currency' }}</label>
            <select v-model="form.currency_code" class="shadcn-input">
              <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.code }} - {{ c.name }} ({{ c.symbol }})</option>
            </select>
            <button
              type="button"
              @click="fillSelectedFromLiveApi"
              class="text-[10px] text-blue-800 hover:text-blue-950 font-bold mt-1.5 inline-flex items-center gap-1 cursor-pointer bg-blue-50 px-2 py-0.5 rounded border border-blue-200"
              :title="isAr ? 'تعبئة السعر مباشرة من الـ API اللحظي' : 'Load direct rate from API feed'"
            >
              <Zap class="w-3 h-3 text-amber-500" />
              <span>{{ isAr ? 'استيراد سعر الـ API اللحظي لهذه العملة' : 'Import Live API Spot Rate' }}</span>
            </button>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? `سعر الصرف مقابل (${base_currency?.code || 'EGP'})` : `Exchange Rate to Base (${base_currency?.code || 'EGP'})` }}</label>
            <input v-model="form.rate_to_base" type="number" step="0.000001" class="shadcn-input font-mono font-bold" required dir="ltr" />
            <p class="text-[10px] text-zinc-500 mt-1">
              {{ isAr ? `مثال: 1 USD = 50.000000 ${base_currency?.code || 'EGP'} | 1 EUR = 54.500000 ${base_currency?.code || 'EGP'}` : `e.g. 1 USD = 50.000000 ${base_currency?.code || 'EGP'}` }}
            </p>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'تاريخ السريان' : 'Effective Date' }}</label>
            <input v-model="form.effective_date" type="date" class="shadcn-input font-mono" required dir="ltr" />
          </div>

          <button type="submit" class="shadcn-btn-secondary w-full gap-1 mt-2 cursor-pointer font-bold">
            <RefreshCw class="w-3.5 h-3.5 text-zinc-700" />
            <span>{{ isAr ? 'حفظ وتثبيت سعر الصرف' : 'Post FX Rate Update' }}</span>
          </button>
        </form>
      </div>
    </div>

    <!-- Edit Exchange Rate Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl rounded-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
            <Pencil class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? `تعديل سعر صرف عملة: ${editForm.currency_code}` : `Edit FX Rate: ${editForm.currency_code}` }}</span>
          </h3>
          <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'كود العملة' : 'Currency Code' }}</label>
            <input :value="editForm.currency_code" disabled class="shadcn-input font-mono bg-zinc-100 text-zinc-500 cursor-not-allowed uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? `سعر الصرف مقابل (${base_currency?.code || 'EGP'})` : `Exchange Rate to Base (${base_currency?.code || 'EGP'})` }}</label>
            <input v-model="editForm.rate_to_base" type="number" step="0.000001" class="shadcn-input font-mono font-bold" required dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'تاريخ السريان' : 'Effective Date' }}</label>
            <input v-model="editForm.effective_date" type="date" class="shadcn-input font-mono" required dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showEditModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="editForm.processing" class="shadcn-btn-primary cursor-pointer flex items-center gap-1">
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ isAr ? 'حفظ التعديلات' : 'Save Changes' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
