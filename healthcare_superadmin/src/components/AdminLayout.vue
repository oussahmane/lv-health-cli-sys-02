<template>
  <div class="min-h-screen bg-slate-50 flex font-sans">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex">
      <div class="p-6 flex items-center gap-3">
        <div class="h-10 w-10 bg-teal-600 rounded-xl flex items-center justify-center shadow-md shadow-teal-100">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
           </svg>
        </div>
        <span class="font-bold text-slate-900 text-lg">HealthQ Platform</span>
      </div>
      
      <nav class="flex-1 px-4 space-y-2 mt-4">
        <router-link to="/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Dashboard' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </router-link>
        
        <router-link to="/clinics" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Clinics' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          Clinics
        </router-link>

        <router-link to="/plans" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Plans' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a.75.75 0 0 0-1.117 1.117a3.5 3.5 0 0 1 0 4.372a.75.75 0 1 0 1.117 1.117a5 5 0 0 0 0-6.606zM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
          </svg>
          Plans
        </router-link>

        <router-link to="/payments" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Payments' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          Payments
        </router-link>
      </nav>
      
      <div class="p-4 border-t border-slate-100">
        <button @click="handleLogout" class="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-slate-600 hover:bg-red-50 hover:text-red-600 transition-all font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
      <!-- Navbar -->
      <header class="bg-white border-bottom border-slate-200 h-16 flex items-center px-8 sticky top-0 z-10">
        <h1 class="text-xl font-bold text-slate-900">Platform Admin</h1>
        <div class="ml-auto flex items-center gap-4">
          <span class="text-sm font-medium text-slate-600">{{ auth.user?.name }}</span>
          <div class="h-8 w-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">
            {{ auth.user?.name?.[0] }}
          </div>
        </div>
      </header>
      
      <div class="p-8">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '../authStore';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

async function handleLogout() {
  await auth.logout();
  router.push('/login');
}
</script>
