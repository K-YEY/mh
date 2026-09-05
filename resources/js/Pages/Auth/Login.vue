<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { ArrowRight, ArrowLeft, Lock, Mail, CheckCircle2, Sparkles, Globe } from 'lucide-vue-next'

defineOptions({ layout: null })

const { locale, isAr, dir, toggleLocale } = useI18n()

const form = useForm({
  email: 'admin@agrierp.com',
  password: 'password',
  remember: true,
})

const isCopied = ref(false)

const fillDemo = () => {
  form.email = 'admin@agrierp.com'
  form.password = 'password'
  isCopied.value = true
  setTimeout(() => (isCopied.value = false), 1600)
}

const submit = () => {
  form.post(route('login'), { onFinish: () => form.reset('password') })
}
</script>

<template>
  <Head :title="isAr ? 'تسجيل الدخول' : 'Sign in'" />

  <div :dir="dir" class="min-h-screen bg-slate-50 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md mx-auto">

      <!-- Right: Card with form (login-03 minimal) -->
      <div class="bg-white rounded-2xl shadow-md p-8">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <img src="/logo.png" alt="MH" class="h-9" />
            <div class="text-sm font-bold">MH For Export</div>
          </div>
          <button @click="toggleLocale" class="text-xs text-slate-600 px-2 py-1 rounded-md hover:bg-slate-100">
            <Globe class="inline-block w-4 h-4 mr-1 align-text-bottom" />
            {{ isAr ? 'العربية' : 'EN' }}
          </button>
        </div>

        <h3 class="text-2xl font-extrabold mb-1">{{ isAr ? 'تسجيل الدخول' : 'Sign in to your account' }}</h3>
        <p class="text-xs text-slate-500 mb-6">{{ isAr ? 'أدخل بياناتك المؤسسية للوصول' : 'Use your corporate credentials to continue.' }}</p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label for="email" class="block text-xs font-medium text-slate-700 mb-1">{{ isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
            <div class="relative">
              <Mail class="absolute left-3 top-3 text-slate-400 w-4 h-4" />
              <input id="email" type="email" v-model="form.email" required placeholder="admin@agrierp.com" class="w-full pl-10 pr-3 py-2 border rounded-lg focus:ring-1 focus:ring-sky-500" />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-xs font-medium text-slate-700 mb-1">{{ isAr ? 'كلمة المرور' : 'Password' }}</label>
              <a href="#" class="text-xs text-sky-600 hover:underline">{{ isAr ? 'نسيت؟' : 'Forgot?' }}</a>
            </div>
            <div class="relative">
              <Lock class="absolute left-3 top-3 text-slate-400 w-4 h-4" />
              <input id="password" type="password" v-model="form.password" required placeholder="••••••••" class="w-full pl-10 pr-3 py-2 border rounded-lg focus:ring-1 focus:ring-sky-500" />
            </div>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border" />
              <span class="text-xs text-slate-600">{{ isAr ? 'تذكرني' : 'Remember me' }}</span>
            </label>
            <button type="button" @click="fillDemo" class="text-xs text-slate-600 hover:underline">{{ isAr ? 'تعبئة تجريبية' : 'Auto fill demo' }}</button>
          </div>

          <div>
            <button type="submit" :disabled="form.processing" class="w-full flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white py-2 rounded-lg font-semibold">
              <span>{{ isAr ? 'تسجيل الدخول' : 'Sign in' }}</span>
              <component :is="isAr ? ArrowLeft : ArrowRight" class="w-4 h-4" />
            </button>
          </div>
        </form>

        <div class="text-center text-xs text-slate-500 mt-5">{{ isAr ? 'بوابة الوصول للمؤسسات' : 'Enterprise access portal' }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rounded-2xl { border-radius: 1rem; }
</style>
