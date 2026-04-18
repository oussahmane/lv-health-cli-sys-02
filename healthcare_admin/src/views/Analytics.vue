<template>
  <AdminLayout title="Clinic Analytics">
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Performance Insights</h2>
        <p class="text-slate-500">Last 30 days of activity.</p>
      </div>

      <!-- Today's Breakdown -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
         <div v-for="(val, key) in stats.today" :key="key" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ key.replace('_', ' ') }} Today</p>
            <p class="text-3xl font-black text-slate-900">{{ val }}</p>
         </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
         <!-- Daily Trend -->
         <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <h3 class="font-bold text-slate-800">Daily Patient Trend</h3>
            <div class="h-48 flex items-end gap-2 px-2">
               <div v-for="day in stats.trend" :key="day.date" class="flex-1 bg-teal-100 rounded-t-lg transition-all hover:bg-teal-500 group relative" :style="{ height: (day.count / maxTrend * 100) + '%' }">
                  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                     {{ day.count }}
                  </div>
               </div>
            </div>
            <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">
               <span>{{ stats.trend[0]?.date }}</span>
               <span>{{ stats.trend[stats.trend.length-1]?.date }}</span>
            </div>
         </div>

         <!-- Peak Hours -->
         <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
               <h3 class="font-bold text-slate-800">Peak Hours (24h)</h3>
               <div class="text-right">
                  <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Avg Wait Time</p>
                  <p class="text-xl font-black text-teal-600">{{ stats.avg_wait_time }} <span class="text-xs">min</span></p>
               </div>
            </div>
            <div class="h-48 flex items-end gap-1 px-2">
               <div v-for="h in 24" :key="h" class="flex-1 bg-blue-100 rounded-t-md transition-all hover:bg-blue-500 group relative" :style="{ height: (getHourCount(h-1) / maxPeak * 100) + '%' }">
                  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                     {{ h-1 }}:00 - {{ getHourCount(h-1) }} patients
                  </div>
               </div>
            </div>
            <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">
               <span>00:00</span>
               <span>12:00</span>
               <span>23:59</span>
            </div>
         </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const stats = ref({
  today: { total: 0, done: 0, no_show: 0, cancelled: 0 },
  trend: [],
  peak_hours: [],
  avg_wait_time: 0,
});

const maxTrend = computed(() => {
  const counts = stats.value.trend.map(d => d.count);
  return counts.length ? Math.max(...counts) : 10;
});

const maxPeak = computed(() => {
  const counts = stats.value.peak_hours.map(h => h.count);
  return counts.length ? Math.max(...counts) : 10;
});

function getHourCount(hour) {
  const hData = stats.value.peak_hours.find(h => h.hour === hour);
  return hData ? hData.count : 0;
}

async function fetchStats() {
  try {
    const response = await api.get('/clinic/analytics');
    stats.value = response.data;
  } catch (err) {
    console.error('Failed to fetch analytics:', err);
  }
}

onMounted(fetchStats);
</script>
