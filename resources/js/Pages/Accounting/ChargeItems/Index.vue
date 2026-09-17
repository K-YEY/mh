<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Layers, 
  Plus, 
  Search, 
  Pencil, 
  Trash2, 
  ArrowUpRight, 
  ArrowDownLeft, 
  CheckCircle2, 
  Ship, 
  Truck, 
  ShieldCheck, 
  FileText, 
  Building2, 
  X,
  HelpCircle,
  FolderTree
} from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  charge_items: Array,
  revenue_accounts: Array,
  expense_accounts: Array,
  all_accounts: Array,
})

const searchQuery = ref('')
const activeCategory = ref('ALL')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingItem = ref(null)

const categories = [
  { value: 'ALL', labelAr: 'كافة الخطوات', labelEn: 'All Steps' },
  { value: 'OCEAN', labelAr: 'نولون وتداول الموانئ', labelEn: 'Ocean & Port' },
  { value: 'CUSTOMS', labelAr: 'تخليص وفحص زراعي', labelEn: 'Customs & Phyto' },
  { value: 'TRUCKING', labelAr: 'نقل بري وأرضيات', labelEn: 'Trucking & Storage' },
]

const createForm = useForm({
  code: '',
  name_ar: '',
  name_en: '',
  revenue_account_id: '',
  expense_account_id: '',
})

const editForm = useForm({
  code: '',
  name_ar: '',
  name_en: '',
  revenue_account_id: '',
  expense_account_id: '',
})

const getItemCategory = (code) => {
  if (code.includes('OFRT') || code.includes('THC') || code.includes('SEAL')) return 'OCEAN'
  if (code.includes('CUSTOMS') || code.includes('PHYTO') || code.includes('DOC')) return 'CUSTOMS'
  if (code.includes('TRUCK') || code.includes('DEMURRAGE')) return 'TRUCKING'
  return 'OTHER'
}

const filteredItems = computed(() => {
  return (props.charge_items || []).filter(item => {
    if (activeCategory.value !== 'ALL') {
      if (getItemCategory(item.code) !== activeCategory.value) return false
    }

    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      const matchCode = item.code?.toLowerCase().includes(q)
      const matchAr = item.name_ar?.toLowerCase().includes(q)
      const matchEn = item.name_en?.toLowerCase().includes(q)
      const matchRev = item.revenue_account?.code?.includes(q)
      const matchExp = item.expense_account?.code?.includes(q)
      if (!matchCode && !matchAr && !matchEn && !matchRev && !matchExp) return false
    }

    return true
  })
})

const getItemIcon = (code) => {
  if (code.includes('OFRT') || code.includes('THC') || code.includes('SEAL')) return Ship
  if (code.includes('TRUCK')) return Truck
  if (code.includes('CUSTOMS') || code.includes('PHYTO')) return ShieldCheck
  return FileText
}

const openEditModal = (item) => {
  editingItem.value = item
  editForm.code = item.code
  editForm.name_ar = item.name_ar
  editForm.name_en = item.name_en
  editForm.revenue_account_id = item.revenue_account_id
  editForm.expense_account_id = item.expense_account_id
  showEditModal.value = true
}

const submitCreate = () => {
  createForm.post(route('charge-items.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      createForm.reset()
    }
  })
}

const submitEdit = () => {
  editForm.put(route('charge-items.update', editingItem.value.id), {
    onSuccess: () => {
      showEditModal.value = false
      editingItem.value = null
    }
  })
}

const deleteItem = (item) => {
  if (confirm(isAr.value ? `حذف الخطوة (${item.name_ar})؟` : `Delete (${item.name_en})?`)) {
    router.delete(route('charge-items.destroy', item.id))
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6 pb-20">
    <!-- Apple Serene Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
      <div>
        <h1 class="text-2xl font-bold text-zinc-950 tracking-tight flex items-center gap-2.5">
          <Layers class="w-6 h-6 text-zinc-900" />
          <span>{{ isAr ? 'خطوات وعناصر التكاليف التشغيلية' : 'Operational Freight Steps' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'تحديد حساب الإيراد (للعميل) وحساب التكلفة (للمورد) لكل خطوة من خطوات الشحن والتخليص' : 'Map designated Revenue and Cost GL accounts for each freight and clearance operation' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link 
          :href="route('parties.index')" 
          class="px-3.5 py-2 rounded-xl text-xs font-semibold text-zinc-700 bg-zinc-100 hover:bg-zinc-200 transition"
        >
          {{ isAr ? 'حسابات الشركات' : 'Company Accounts' }}
        </Link>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 shadow-sm transition cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>{{ isAr ? 'إضافة خطوة جديدة' : 'Add Step' }}</span>
        </button>
      </div>
    </div>

    <!-- Apple-Style Simple Guide Callout -->
    <div class="bg-zinc-100/70 rounded-2xl p-3.5 px-4 flex items-center gap-3 text-xs text-zinc-600 border border-zinc-200/60">
      <HelpCircle class="w-4 h-4 text-zinc-700 shrink-0" />
      <span class="leading-relaxed">
        {{ isAr ? 'التوجيه المالي التلقائي: عند فوترة العميل تُقيد القيمة في [حساب الإيراد]، وعند تسجيل مطالبة خط الشحن أو المخلص تُقيد التكلفة في [حساب المصروف]، والفرق هو ربح الشحنة.' : 'Customer billing credits the Revenue Account, while vendor/carrier bills debit the Cost Account.' }}
      </span>
    </div>

    <!-- Segmented Categories & Search Bar -->
    <div class="bg-white/80 backdrop-blur-md p-2 rounded-2xl border border-zinc-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <!-- Calm Segmented Tabs -->
      <div class="flex items-center gap-1 overflow-x-auto p-1 scrollbar-none">
        <button
          v-for="cat in categories"
          :key="cat.value"
          @click="activeCategory = cat.value"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer',
            activeCategory === cat.value
              ? 'bg-zinc-950 text-white shadow-xs'
              : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100'
          ]"
        >
          {{ isAr ? cat.labelAr : cat.labelEn }}
        </button>
      </div>

      <!-- Clean Search -->
      <div class="relative min-w-[240px] px-1">
        <Search class="w-3.5 h-3.5 text-zinc-400 absolute start-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="isAr ? 'بحث بالاسم أو الكود (41100)...' : 'Search by step or code...'"
          class="w-full bg-zinc-100/80 border-none rounded-xl text-xs ps-8 pe-8 py-2 text-zinc-900 placeholder:text-zinc-400 focus:bg-white focus:ring-2 focus:ring-zinc-950/10 transition"
        />
        <button 
          v-if="searchQuery" 
          @click="searchQuery = ''" 
          class="absolute end-3.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Clean Apple Cards Grid -->
    <div v-if="filteredItems.length === 0" class="bg-white rounded-3xl p-16 text-center border border-zinc-200/80">
      <Layers class="w-10 h-10 text-zinc-300 mx-auto mb-2" />
      <div class="text-sm font-bold text-zinc-800">
        {{ isAr ? 'لا توجد خطوات تشغيلية مطابقة' : 'No operational steps found' }}
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="item in filteredItems"
        :key="item.id"
        class="bg-white rounded-2xl p-5 border border-zinc-200/80 hover:border-zinc-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-4 group"
      >
        <div class="space-y-3">
          <!-- Step Header: Icon & Code -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-700 group-hover:bg-zinc-950 group-hover:text-white transition-colors">
                <component :is="getItemIcon(item.code)" class="w-4 h-4" />
              </div>
              <span class="font-mono text-xs font-bold text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded-lg">
                {{ item.code }}
              </span>
            </div>

            <button
              @click="openEditModal(item)"
              class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-950 hover:bg-zinc-100 transition cursor-pointer"
              :title="isAr ? 'تعديل الحسابات' : 'Edit'"
            >
              <Pencil class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Step Names -->
          <div>
            <h3 class="text-base font-bold text-zinc-950 leading-snug tracking-tight">
              {{ isAr ? item.name_ar : item.name_en }}
            </h3>
            <p class="text-xs text-zinc-400 mt-0.5 font-medium">
              {{ isAr ? item.name_en : item.name_ar }}
            </p>
          </div>

          <!-- Mapped Accounts Pills (Clean & Intuitive) -->
          <div class="space-y-2 pt-1 text-xs">
            <!-- Revenue -->
            <div class="bg-emerald-50/60 rounded-xl p-2.5 border border-emerald-200/60 flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 overflow-hidden">
                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded uppercase shrink-0">
                  {{ isAr ? 'إيراد' : 'Revenue' }}
                </span>
                <span class="font-mono text-xs font-bold text-emerald-950 shrink-0">
                  {{ item.revenue_account?.code }}
                </span>
                <span class="text-xs text-emerald-900 truncate font-medium">
                  {{ isAr ? item.revenue_account?.name_ar : item.revenue_account?.name_en }}
                </span>
              </div>
            </div>

            <!-- Expense -->
            <div class="bg-blue-50/60 rounded-xl p-2.5 border border-blue-200/60 flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 overflow-hidden">
                <span class="text-[10px] font-bold text-blue-800 bg-blue-100 px-1.5 py-0.5 rounded uppercase shrink-0">
                  {{ isAr ? 'تكلفة' : 'Cost' }}
                </span>
                <span class="font-mono text-xs font-bold text-blue-950 shrink-0">
                  {{ item.expense_account?.code }}
                </span>
                <span class="text-xs text-blue-900 truncate font-medium">
                  {{ isAr ? item.expense_account?.name_ar : item.expense_account?.name_en }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-[11px] text-zinc-400">
          <span>ID: #{{ item.id }}</span>
          <button
            @click="deleteItem(item)"
            class="text-zinc-400 hover:text-red-600 transition cursor-pointer"
            :title="isAr ? 'حذف' : 'Delete'"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Apple-Style Simple Modal: Add Step -->
    <div 
      v-if="showCreateModal" 
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-zinc-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
          <h3 class="text-base font-bold text-zinc-950">
            {{ isAr ? 'إضافة خطوة تشغيلية جديدة' : 'Add New Operational Step' }}
          </h3>
          <button 
            @click="showCreateModal = false" 
            class="w-7 h-7 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'كود الخطوة' : 'Code' }} *</label>
              <input 
                v-model="createForm.code" 
                type="text" 
                placeholder="INSP_PHYTO" 
                class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono uppercase focus:bg-white focus:ring-2 focus:ring-zinc-950/10"
                required 
              />
            </div>
            <div class="sm:col-span-2">
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالعربية' : 'Arabic Name' }} *</label>
              <input 
                v-model="createForm.name_ar" 
                type="text" 
                placeholder="مثال: رسوم فحص معمل الحجر الزراعي" 
                class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-zinc-950/10"
                required 
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالإنجليزية' : 'English Name' }} *</label>
            <input 
              v-model="createForm.name_en" 
              type="text" 
              placeholder="e.g. Phyto Lab Analysis Fees" 
              class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-zinc-950/10"
              required 
            />
          </div>

          <div class="space-y-3 pt-2">
            <div>
              <label class="block font-bold text-emerald-900 mb-1 flex items-center gap-1">
                <span>{{ isAr ? 'حساب الإيراد (عند فوترة العميل)' : 'Revenue Account' }} *</span>
              </label>
              <select v-model="createForm.revenue_account_id" class="w-full bg-emerald-50/60 rounded-xl px-3 py-2 text-xs border border-emerald-200/80 cursor-pointer" required>
                <option value="" disabled>{{ isAr ? '-- اختر حساب الإيراد --' : '-- Select Revenue Account --' }}</option>
                <option v-for="acc in revenue_accounts" :key="acc.id" :value="acc.id">
                  [{{ acc.code }}] {{ isAr ? acc.name_ar : acc.name_en }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-blue-900 mb-1 flex items-center gap-1">
                <span>{{ isAr ? 'حساب التكلفة (عند فاتورة المورد)' : 'Cost Account' }} *</span>
              </label>
              <select v-model="createForm.expense_account_id" class="w-full bg-blue-50/60 rounded-xl px-3 py-2 text-xs border border-blue-200/80 cursor-pointer" required>
                <option value="" disabled>{{ isAr ? '-- اختر حساب التكلفة --' : '-- Select Expense Account --' }}</option>
                <option v-for="acc in expense_accounts" :key="acc.id" :value="acc.id">
                  [{{ acc.code }}] {{ isAr ? acc.name_ar : acc.name_en }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100">
            <button 
              type="button" 
              @click="showCreateModal = false" 
              class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="createForm.processing"
              class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 cursor-pointer disabled:opacity-50"
            >
              {{ isAr ? 'حفظ الخطوة' : 'Save Step' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div 
      v-if="showEditModal" 
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-zinc-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
          <h3 class="text-base font-bold text-zinc-950">
            {{ editForm.name_ar }} ({{ editForm.code }})
          </h3>
          <button 
            @click="showEditModal = false" 
            class="w-7 h-7 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600 cursor-pointer"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-4 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الكود' : 'Code' }} *</label>
              <input v-model="editForm.code" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs font-mono uppercase" required />
            </div>
            <div class="sm:col-span-2">
              <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالعربية' : 'Arabic Name' }} *</label>
              <input v-model="editForm.name_ar" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs" required />
            </div>
          </div>

          <div>
            <label class="block font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالإنجليزية' : 'English Name' }} *</label>
            <input v-model="editForm.name_en" type="text" class="w-full bg-zinc-100/80 rounded-xl px-3 py-2 text-xs" required />
          </div>

          <div class="space-y-3 pt-2">
            <div>
              <label class="block font-bold text-emerald-900 mb-1">{{ isAr ? 'حساب الإيراد (للعملاء)' : 'Revenue Account' }} *</label>
              <select v-model="editForm.revenue_account_id" class="w-full bg-emerald-50/60 rounded-xl px-3 py-2 text-xs border border-emerald-200/80 cursor-pointer" required>
                <option value="" disabled>{{ isAr ? '-- اختر حساب الإيراد --' : '-- Select Revenue Account --' }}</option>
                <option v-for="acc in revenue_accounts" :key="acc.id" :value="acc.id">
                  [{{ acc.code }}] {{ isAr ? acc.name_ar : acc.name_en }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-blue-900 mb-1">{{ isAr ? 'حساب التكلفة (للموردين)' : 'Cost Account' }} *</label>
              <select v-model="editForm.expense_account_id" class="w-full bg-blue-50/60 rounded-xl px-3 py-2 text-xs border border-blue-200/80 cursor-pointer" required>
                <option value="" disabled>{{ isAr ? '-- اختر حساب التكلفة --' : '-- Select Expense Account --' }}</option>
                <option v-for="acc in expense_accounts" :key="acc.id" :value="acc.id">
                  [{{ acc.code }}] {{ isAr ? acc.name_ar : acc.name_en }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100">
            <button 
              type="button" 
              @click="showEditModal = false" 
              class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 cursor-pointer"
            >
              {{ isAr ? 'إلغاء' : 'Cancel' }}
            </button>
            <button 
              type="submit" 
              :disabled="editForm.processing"
              class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 cursor-pointer disabled:opacity-50"
            >
              {{ isAr ? 'حفظ التعديلات' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
