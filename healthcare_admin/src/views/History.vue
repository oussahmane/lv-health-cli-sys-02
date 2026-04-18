<template>
  <AdminLayout title="Queue History">
    <div class="space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Records</h2>
          <p class="text-slate-500">Browse and export past queue entries.</p>
        </div>
        <div class="flex gap-4">
           <input type="date" v-model="selectedDate" @change="fetchHistory" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl font-medium focus:ring-teal-500 outline-none" />
           <button @click="exportCSV" class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center gap-2">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
             </svg>
             Export CSV
           </button>
        </div>
      </div>

      <!-- History Table -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">#</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Patient Name</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Timing</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Duration</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="q in history" :key="q.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-8 py-5">
                <span class="font-black text-slate-400">#{{ q.queue_number }}</span>
              </td>
              <td class="px-8 py-5">
                <div>
                   <h4 class="font-bold text-slate-800">{{ q.patient_name }}</h4>
                   <p class="text-xs text-slate-400">{{ q.patient_phone || 'No phone' }}</p>
                </div>
              </td>
              <td class="px-8 py-5">
                 <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border" :class="statusClass(q.status)">
                   {{ q.status }}
                 </span>
              </td>
              <td class="px-8 py-5">
                 <div class="text-xs space-y-1">
                    <p class="text-slate-500"><b class="text-slate-700">In:</b> {{ formatDate(q.created_at) }}</p>
                    <p v-if="q.called_at" class="text-slate-500"><b class="text-slate-700">Called:</b> {{ formatDate(q.called_at) }}</p>
                 </div>
              </td>
              <td class="px-8 py-5 text-right font-medium text-slate-600">
                 {{ calculateDuration(q) }}
              </td>
            </tr>
            <tr v-if="history.length === 0">
               <td colspan="5" class="px-8 py-20 text-center">
                  <div class="flex flex-col items-center gap-4">
                     <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                     </div>
                     <p class="text-slate-400 font-medium italic">No entries found for this date.</p>
                  </div>
               </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const history = ref([]);
const selectedDate = ref(now().toDateString());

function now() {
  return new Date();
}

async function fetchHistory() {
  try {
    const response = await api.get(`/clinic/history?date=${selectedDate.value}`);
    history.value = response.data;
  } catch (err) {
    console.error('Failed to fetch history:', err);
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function calculateDuration(q) {
  if (!q.called_at || !q.created_at) return '-';
  const start = new Date(q.created_at);
  const end = new Date(q.called_at);
  const diff = Math.round((end - start) / 60000);
  return `${diff} min`;
}

function exportCSV() {
  window.location.href = `http://localhost:8000/api/clinic/history/export?date=${selectedDate.value}&token=${localStorage.getItem('clinic_auth_token')}`;
}

function statusClass(status) {
  switch (status) {
    case 'done': return 'bg-green-50 text-green-700 border-green-100';
    case 'no_show': return 'bg-orange-50 text-orange-700 border-orange-100';
    case 'cancelled': return 'bg-red-50 text-red-700 border-red-100';
    default: return 'bg-slate-50 text-slate-500 border-slate-100';
  }
}

onMounted(() => {
  const d = new Date();
  selectedDate.value = d.toISOString().split('T')[0];
  fetchHistory();
});
</script>
