<template>
  <div>
    <!-- Loading State -->
    <div v-if="auth.loading" class="min-h-screen w-full flex items-center justify-center bg-white">
      <div class="flex flex-col items-center gap-4">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600"></div>
        <p class="text-slate-500 font-medium italic animate-pulse">Synchronizing permissions...</p>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="auth.user" class="min-h-screen bg-slate-50 flex font-sans">
      <!-- Sidebar -->
      <aside class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex">
        <div class="p-6 flex items-center gap-3">
          <div class="h-10 w-10 bg-teal-600 rounded-xl flex items-center justify-center shadow-md shadow-teal-100">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
             </svg>
          </div>
          <span class="font-bold text-slate-900 text-lg line-clamp-1">{{ auth.user?.clinic?.name || 'Clinic Portal' }}</span>
        </div>
        
        <nav class="flex-1 px-4 space-y-2 mt-4">
          <router-link v-if="auth.can('manage_queue') || auth.isDoctor" to="/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Dashboard' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            {{ auth.isDoctor ? 'My Patients' : 'Live Queue' }}
          </router-link>
          
          <router-link v-if="auth.can('view_history')" to="/history" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'History' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            History
          </router-link>

          <router-link v-if="auth.can('view_analytics')" to="/analytics" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Analytics' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Analytics
          </router-link>

          <div v-if="auth.can('manage_staff') || auth.can('manage_settings')" class="pt-4 mt-4 border-t border-slate-100 space-y-2">
             <router-link v-if="auth.can('manage_staff')" to="/staff" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Staff' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              Staff
            </router-link>
            
            <router-link v-if="auth.can('manage_settings')" to="/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Settings' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Settings
            </router-link>

            <router-link v-if="auth.can('manage_settings')" to="/counters" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all font-medium" :class="$route.name === 'Counters' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Rooms & Counters
            </router-link>
          </div>
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
        <header class="bg-white border-bottom border-slate-200 h-16 flex items-center px-8 sticky top-0 z-10 shadow-sm">
          <h1 class="text-xl font-bold text-slate-900">{{ title }}</h1>
          <div class="ml-auto flex items-center gap-4">
            <div class="text-right">
              <p class="text-sm font-bold text-slate-900 leading-tight">{{ auth.user?.name }}</p>
              <p class="text-xs text-slate-500 uppercase font-medium tracking-tighter">{{ auth.user?.role?.replace('_', ' ') }}</p>
            </div>
            <div class="h-10 w-10 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
              {{ auth.user?.name?.[0] }}
            </div>
          </div>
        </header>
        
        <div class="p-8">
          <slot></slot>
        </div>
      </main>
    </div>

    <!-- Error/Fallback State -->
    <div v-else class="min-h-screen w-full flex items-center justify-center bg-slate-50">
       <div class="text-center p-8 bg-white rounded-3xl shadow-xl border border-slate-100 max-w-sm">
          <div class="h-16 w-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
             </svg>
          </div>
          <h2 class="text-xl font-bold text-slate-900 mb-2">Access Denied</h2>
          <p class="text-slate-500 text-sm mb-6">We couldn't verify your permissions. Please try logging in again.</p>
          <button @click="handleLogout" class="w-full py-3 bg-teal-600 text-white rounded-xl font-bold hover:bg-teal-700 transition-all">Back to Login</button>
       </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../authStore';
import { useRouter } from 'vue-router';
import { onMounted } from 'vue';

defineProps({
  title: {
    type: String,
    default: 'Clinic Management'
  }
});

const auth = useAuthStore();
const router = useRouter();

async function handleLogout() {
  await auth.logout();
  router.push('/login');
}

onMounted(() => {
  if (auth.token && !auth.user) {
    auth.fetchUser();
  }
});
</script>
