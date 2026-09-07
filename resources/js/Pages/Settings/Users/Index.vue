<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Users, Plus, UserCheck } from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  users: Array,
})

const showCreateModal = ref(false)

const form = useForm({
  name: '',
  email: '',
  password: 'password',
})

const submit = () => {
  form.post(route('users.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Users class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'إدارة المستخدمين والأدوار والصلاحيات (RBAC)' : 'Users & Security Permissions' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'التحكم الصارم بالصلاحيات وحسابات الموظفين بالمنظومة' : 'Granular Role-Based Access Control (RBAC) & Employee Provisions' }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة مستخدم جديد' : 'Provision User' }}</span>
      </button>
    </div>

    <!-- Users Table Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <UserCheck class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `دليل حسابات المستخدمين (${users.length} مستخدم)` : `System Accounts Directory (${users.length} Users)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'المستخدم' : 'User' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'البريد الإلكتروني' : 'Corporate Email' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'دور الصلاحية' : 'System Access Role' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="px-4 py-3 font-mono">{{ isAr ? 'تاريخ الإنشاء' : 'Created Date' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="u in users" :key="u.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-full bg-zinc-950 text-white font-bold text-xs flex items-center justify-center font-mono">
                    {{ u.name.substring(0, 2).toUpperCase() }}
                  </div>
                  <div class="font-bold text-zinc-950">{{ u.name }}</div>
                </div>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-800">
                {{ u.email }}
              </td>
              <td class="px-4 py-3">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-100 text-zinc-950 border border-zinc-300 uppercase">
                  {{ isAr ? 'مدير عام للنظام' : 'super-admin' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
              </td>
              <td class="px-4 py-3 text-zinc-500 font-mono text-[11px]">
                {{ u.created_at ? formatDateTime(u.created_at) : (isAr ? 'مدير النظام' : 'System Admin') }}
              </td>
            </tr>

            <tr v-if="!users?.length">
              <td colspan="5" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا يوجد مستخدمون مسجلون حالياً' : 'No system users found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950">{{ isAr ? 'إضافة مستخدم جديد للنظام' : 'Provision System User' }}</h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم الموظف / المستخدم' : 'Employee Full Name' }}</label>
            <input v-model="form.name" type="text" placeholder="e.g. Ahmed Hassan" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'البريد الإلكتروني للشركة' : 'Corporate Email Address' }}</label>
            <input v-model="form.email" type="email" placeholder="ahmed@agrierp.com" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'كلمة المرور الافتراضية' : 'Default Password' }}</label>
            <input v-model="form.password" type="password" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ المستخدم' : 'Save User' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
