<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { BookOpen, Plus, FolderTree, Pencil, Trash2, CheckCircle2 } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  accounts: Array,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)

const form = useForm({
  code: '',
  name_ar: '',
  name_en: '',
  type: 'ASSET',
  parent_id: '',
  is_active: true,
})

const editForm = useForm({
  id: null,
  code: '',
  name_ar: '',
  name_en: '',
  type: 'ASSET',
  parent_id: '',
  is_active: true,
})

const submit = () => {
  form.post(route('accounts.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}

const openEditModal = (acc) => {
  editForm.id = acc.id
  editForm.code = acc.code
  editForm.name_ar = acc.name_ar
  editForm.name_en = acc.name_en
  editForm.type = acc.type
  editForm.parent_id = acc.parent_id || ''
  editForm.is_active = acc.is_active !== false
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(route('accounts.update', editForm.id), {
    onSuccess: () => {
      showEditModal.value = false
    }
  })
}

const deleteAccount = (acc) => {
  if (confirm(isAr.value ? `هل أنت متأكد من حذف الحساب (${acc.code} - ${acc.name_ar})؟` : `Delete account (${acc.code})?`)) {
    router.delete(route('accounts.destroy', acc.id))
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <BookOpen class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'دليل شجرة الحسابات المالية (COA)' : 'Chart of Accounts (COA Tree)' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'شجرة الحسابات المالية الرئيسية وتصنيفات دفاتر اليومية' : 'Master Financial Account Tree & Ledger Categorization' }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer text-xs"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة حساب جديد' : 'New Account' }}</span>
      </button>
    </div>

    <!-- Accounts Table Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <FolderTree class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `دليل شجرة الحسابات (${accounts.length} حساب)` : `Chart of Accounts Tree (${accounts.length} Accounts)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'رمز الحساب' : 'Account Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم الحساب (عربي / انجليزي)' : 'Account Name (EN / AR)' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'النوع' : 'Type' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحساب الأب' : 'Parent Account' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-4 py-3 text-center">{{ isAr ? 'التحكم والإجراءات' : 'Actions' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="acc in accounts" :key="acc.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950">
                {{ acc.code }}
              </td>
              <td class="px-4 py-3">
                <div class="font-bold text-zinc-950">{{ isAr ? acc.name_ar : acc.name_en }}</div>
                <div class="text-[11px] text-zinc-500 font-sans">{{ isAr ? acc.name_en : acc.name_ar }}</div>
              </td>
              <td class="px-4 py-3">
                <span 
                  class="px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase border bg-zinc-100 text-zinc-950 border-zinc-300"
                >
                  {{ acc.type }}
                </span>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600">
                {{ acc.parent ? `${acc.parent.code} - ${isAr ? acc.parent.name_ar : acc.parent.name_en}` : (isAr ? 'حساب رئيسي' : 'Top Level Account') }}
              </td>
              <td class="px-4 py-3">
                <span v-if="acc.is_active" class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
                <span v-else class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-100 text-zinc-500 border border-zinc-300">
                  {{ isAr ? 'غير نشط' : 'INACTIVE' }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    type="button"
                    @click="openEditModal(acc)"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-blue-700 bg-blue-50/80 hover:bg-blue-100/90 border border-blue-200/80 flex items-center gap-1 transition shadow-xs cursor-pointer"
                    :title="isAr ? 'تعديل بيانات الحساب' : 'Edit Account'"
                  >
                    <Pencil class="w-3.5 h-3.5 text-blue-600" />
                    <span>{{ isAr ? 'تعديل' : 'Edit' }}</span>
                  </button>

                  <button
                    type="button"
                    @click="deleteAccount(acc)"
                    class="p-1 rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                    :title="isAr ? 'حذف الحساب' : 'Delete Account'"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!accounts?.length">
              <td colspan="6" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد حسابات مسجلة في الشجرة' : 'No financial accounts found in chart.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Account Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl rounded-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950">{{ isAr ? 'إضافة حساب مالى جديد' : 'Create Financial Account' }}</h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رمز الحساب' : 'Account Code' }}</label>
            <input v-model="form.code" type="text" placeholder="e.g. 11050" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالإنجليزية' : 'Name (English)' }}</label>
            <input v-model="form.name_en" type="text" placeholder="e.g. Reefer Freight Revenue" required class="shadcn-input" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالعربية' : 'Name (Arabic)' }}</label>
            <input v-model="form.name_ar" type="text" placeholder="مثال: إيرادات النوالين البحرية" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع الحساب' : 'Account Type' }}</label>
            <select v-model="form.type" class="shadcn-input">
              <option value="ASSET">{{ isAr ? 'أصول (ASSET)' : 'ASSET' }}</option>
              <option value="LIABILITY">{{ isAr ? 'التزامات (LIABILITY)' : 'LIABILITY' }}</option>
              <option value="EQUITY">{{ isAr ? 'حقوق ملكية (EQUITY)' : 'EQUITY' }}</option>
              <option value="REVENUE">{{ isAr ? 'إيرادات (REVENUE)' : 'REVENUE' }}</option>
              <option value="EXPENSE">{{ isAr ? 'مصروفات (EXPENSE)' : 'EXPENSE' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الحساب الأب (اختياري)' : 'Parent Account (Optional)' }}</label>
            <select v-model="form.parent_id" class="shadcn-input">
              <option value="">{{ isAr ? 'بدون (حساب رئيسي)' : 'None (Top Level)' }}</option>
              <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ isAr ? a.name_ar : a.name_en }}</option>
            </select>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ الحساب' : 'Save Account' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Account Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl rounded-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
            <Pencil class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? `تعديل بيانات الحساب: ${editForm.code}` : `Edit Account: ${editForm.code}` }}</span>
          </h3>
          <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رمز الحساب' : 'Account Code' }}</label>
            <input v-model="editForm.code" type="text" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالإنجليزية' : 'Name (English)' }}</label>
            <input v-model="editForm.name_en" type="text" required class="shadcn-input" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالعربية' : 'Name (Arabic)' }}</label>
            <input v-model="editForm.name_ar" type="text" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع الحساب' : 'Account Type' }}</label>
            <select v-model="editForm.type" class="shadcn-input">
              <option value="ASSET">{{ isAr ? 'أصول (ASSET)' : 'ASSET' }}</option>
              <option value="LIABILITY">{{ isAr ? 'التزامات (LIABILITY)' : 'LIABILITY' }}</option>
              <option value="EQUITY">{{ isAr ? 'حقوق ملكية (EQUITY)' : 'EQUITY' }}</option>
              <option value="REVENUE">{{ isAr ? 'إيرادات (REVENUE)' : 'REVENUE' }}</option>
              <option value="EXPENSE">{{ isAr ? 'مصروفات (EXPENSE)' : 'EXPENSE' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الحساب الأب (اختياري)' : 'Parent Account (Optional)' }}</label>
            <select v-model="editForm.parent_id" class="shadcn-input">
              <option value="">{{ isAr ? 'بدون (حساب رئيسي)' : 'None (Top Level)' }}</option>
              <option v-for="a in accounts.filter(x => x.id !== editForm.id)" :key="a.id" :value="a.id">{{ a.code }} - {{ isAr ? a.name_ar : a.name_en }}</option>
            </select>
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
