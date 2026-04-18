<template>
  <AdminLayout>
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Platform Overview</h2>
        <p class="text-slate-500">Real-time statistics across all clinics.</p>
      </div>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Clinics -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-teal-100 text-teal-700 rounded-2xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Clinics</p>
              <p class="text-2xl font-bold text-slate-900">{{ stats.total_clinics }}</p>
            </div>
          </div>
        </div>

        <!-- Active Today -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-100 text-blue-700 rounded-2xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Active Clinics Today</p>
              <p class="text-2xl font-bold text-slate-900">{{ stats.active_clinics_today }}</p>
            </div>
          </div>
        </div>

        <!-- Total Patients -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 text-indigo-700 rounded-2xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Patients Today</p>
              <p class="text-2xl font-bold text-slate-900">{{ stats.total_queue_entries_today }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const stats = ref({
  total_clinics: 0,
  active_clinics_today: 0,
  total_queue_entries_today: 0,
});

async function fetchStats() {
  try {
    const response = await api.get('/super/stats');
    stats.value = response.data;
  } catch (err) {
    console.error('Failed to fetch stats:', err);
  }
}

onMounted(fetchStats);
</script>
