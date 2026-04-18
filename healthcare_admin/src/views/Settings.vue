<template>
  <AdminLayout title="Clinic Settings">
    <div class="max-w-4xl space-y-8">
      <!-- General Info -->
      <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
         <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">General Information</h3>
            <button @click="saveGeneral" :disabled="saving" class="px-5 py-2 bg-teal-600 text-white rounded-xl font-bold hover:bg-teal-700 transition-all disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
         </div>
         <div class="p-8 grid grid-cols-2 gap-6">
            <div class="col-span-2 flex items-center gap-6 pb-6 border-b border-slate-50">
               <div class="h-24 w-24 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 overflow-hidden relative group">
                  <img v-if="clinic.logo" :src="'http://localhost:8000/storage/' + clinic.logo" class="h-full w-full object-cover" />
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <label class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center cursor-pointer transition-all">
                     <span class="text-white text-[10px] font-bold uppercase tracking-wider">Change</span>
                     <input type="file" class="hidden" @change="handleLogo" accept="image/*" />
                  </label>
               </div>
               <div>
                  <h4 class="font-bold text-slate-900">Clinic Logo</h4>
                  <p class="text-sm text-slate-500">JPG, PNG or GIF. Max 2MB.</p>
               </div>
            </div>

            <div class="col-span-2">
               <label class="block text-sm font-semibold text-slate-700 mb-1 text-right-dir">Clinic Name</label>
               <input v-model="clinic.name" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none" />
            </div>

            <div class="col-span-2">
               <label class="block text-sm font-semibold text-slate-700 mb-1">Address</label>
               <input v-model="clinic.address" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none" />
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
               <input v-model="clinic.phone" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none" />
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-700 mb-1">Timezone</label>
               <select v-model="clinic.timezone" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none">
                 <option value="Africa/Algiers">Africa/Algiers</option>
                 <option value="UTC">UTC</option>
               </select>
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-700 mb-1">Avg Min per Patient</label>
               <input v-model="clinic.avg_minutes_per_patient" type="number" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none" />
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-700 mb-1">Default Max Patients / Day</label>
               <input v-model="clinic.default_max_patients" type="number" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none" />
            </div>
         </div>
      </section>
 
       <!-- TV Display Settings -->
       <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
             <h3 class="font-bold text-slate-800">TV Display Settings</h3>
          </div>
          <div class="p-8 space-y-4">
             <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Waiting Room Ticker Message</label>
                <textarea v-model="clinic.display_ticker" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none resize-none" placeholder="Welcome to {clinic_name} • High volume: Expected wait {wait_time} min • Stay healthy • Thank you for your patience"></textarea>
                <p class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  Use {clinic_name} and {wait_time} as dynamic placeholders.
                </p>
             </div>
          </div>
       </section>

      <!-- Working Hours -->
      <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
         <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Weekly Working Hours</h3>
         </div>
         <div class="p-8 space-y-4">
            <div v-for="day in weekdays" :key="day.id" class="flex items-center justify-between py-3 px-4 rounded-2xl hover:bg-slate-50 transition-all">
               <div class="flex items-center gap-4 w-32">
                  <div class="h-8 w-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">{{ day.name[0] }}</div>
                  <span class="font-bold text-slate-700">{{ day.name }}</span>
               </div>
               
               <div class="flex items-center gap-8">
                  <template v-if="getWorkHour(day.id).is_open">
                    <div class="flex items-center gap-2">
                       <input type="checkbox" :id="'24h-'+day.id" :checked="is24h(day.id)" @change="toggle24h(day.id)" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                       <label :for="'24h-'+day.id" class="text-xs font-bold text-slate-500 uppercase tracking-widest cursor-pointer">Open 24h</label>
                    </div>
                    
                    <div v-if="!is24h(day.id)" class="flex items-center gap-3">
                       <input type="time" v-model="getWorkHour(day.id).open_time" class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-teal-500" />
                       <span class="text-slate-400">to</span>
                       <input type="time" v-model="getWorkHour(day.id).close_time" class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-teal-500" />
                    </div>
                    <span v-else class="text-slate-400 font-bold text-xs uppercase tracking-widest bg-slate-100 px-3 py-1 rounded-lg">Continuous Operation</span>
                  </template>
                  <span v-else class="text-slate-400 font-medium italic text-sm">Closed</span>
               </div>

               <div class="flex items-center">
                  <button @click="toggleDay(day.id)" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2" :class="getWorkHour(day.id).is_open ? 'bg-teal-600' : 'bg-slate-200'">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="getWorkHour(day.id).is_open ? 'translate-x-5' : 'translate-x-0'"></span>
                  </button>
               </div>
            </div>
         </div>
      </section>

      <!-- Share & Public Links -->
      <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
         <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-teal-50/30">
            <h3 class="font-bold text-slate-800">Share Clinic & Display</h3>
            <span class="px-3 py-1 bg-teal-100 text-teal-700 rounded-lg text-xs font-black uppercase tracking-widest">Public Access</span>
         </div>
         <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
               <!-- Public Link -->
               <div class="space-y-4">
                  <div>
                    <h4 class="font-bold text-slate-900">Patient Registration Link</h4>
                    <p class="text-xs text-slate-500 mt-1">Copy this link to your website or social media.</p>
                  </div>
                  <div class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-2xl">
                     <input :value="publicLink" readonly class="flex-1 bg-transparent px-3 py-2 text-sm text-slate-600 outline-none font-medium" />
                     <button @click="copyLink" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-teal-600 hover:text-white hover:border-teal-600 transition-all shadow-sm">
                       {{ copied ? 'Copied!' : 'Copy Link' }}
                     </button>
                  </div>
               </div>

               <!-- TV Display -->
               <div class="space-y-4">
                  <div>
                    <h4 class="font-bold text-slate-900">Waiting Room TV Mode</h4>
                    <p class="text-xs text-slate-500 mt-1">Open this on your clinic's TV or monitor.</p>
                  </div>
                  <button @click="openDisplay" class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                     </svg>
                     Launch TV Display
                  </button>
               </div>
            </div>

            <!-- Hint -->
            <div class="p-4 bg-blue-50 border border-blue-100 rounded-2xl flex items-start gap-3">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
               </svg>
               <p class="text-xs text-blue-700 leading-relaxed">
                 <b>PRO TIP:</b> You can use any free QR code generator online to turn your Patient Registration Link into a QR code. Print it and place it at your clinic's entrance for contactless registration!
               </p>
            </div>
         </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const clinic = ref({
  name: '',
  address: '',
  phone: '',
  slug: '',
  timezone: 'Africa/Algiers',
  avg_minutes_per_patient: 10,
  default_max_patients: 50,
  logo: null,
  display_ticker: '',
  working_hours: []
});

const weekdays = [
  { id: 1, name: 'Monday' },
  { id: 2, name: 'Tuesday' },
  { id: 3, name: 'Wednesday' },
  { id: 4, name: 'Thursday' },
  { id: 5, name: 'Friday' },
  { id: 6, name: 'Saturday' },
  { id: 0, name: 'Sunday' },
];

const saving = ref(false);
const logoFile = ref(null);
const copied = ref(false);

const publicLink = computed(() => {
  if (!clinic.value.slug) return 'Loading...';
  const baseUrl = import.meta.env.VITE_FRONTEND_URL || 'http://localhost:5175';
  return `${baseUrl}/q/${clinic.value.slug}`;
});

function openDisplay() {
  if (!clinic.value.slug) return alert('Clinic slug not loaded yet. Please wait.');
  const baseUrl = import.meta.env.VITE_FRONTEND_URL || 'http://localhost:5175';
  window.open(`${baseUrl}/q/${clinic.value.slug}/display`, '_blank');
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(publicLink.value);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
  } catch (err) {
    console.error('Failed to copy:', err);
  }
}

async function fetchSettings() {
  try {
    const response = await api.get('/clinic/settings');
    clinic.value = response.data.clinic;
  } catch (err) {
    console.error('Failed to fetch settings:', err);
  }
}

function getWorkHour(weekday) {
  let wh = clinic.value.working_hours.find(h => h.weekday === weekday);
  if (!wh) {
    wh = { weekday, open_time: '08:00', close_time: '17:00', is_open: true };
    clinic.value.working_hours.push(wh);
  }
  return wh;
}

function toggleDay(weekday) {
  const wh = getWorkHour(weekday);
  wh.is_open = !wh.is_open;
}

function is24h(weekday) {
  const wh = getWorkHour(weekday);
  return wh.is_open && (wh.open_time === null || wh.close_time === null || (wh.open_time === '00:00:00' && wh.close_time === '23:59:00'));
}

function toggle24h(weekday) {
  const wh = getWorkHour(weekday);
  if (is24h(weekday)) {
    wh.open_time = '08:00:00';
    wh.close_time = '17:00:00';
  } else {
    wh.open_time = null;
    wh.close_time = null;
  }
}

function handleLogo(e) {
  const file = e.target.files[0];
  if (file) {
    logoFile.value = file;
    // Preview locally
    const reader = new FileReader();
    reader.onload = (re) => {
      // Temporary preview
      // clinic.value.logo = re.target.result;
    };
    reader.readAsDataURL(file);
  }
}

async function saveGeneral() {
  saving.value = true;
  try {
    const formData = new FormData();
    Object.keys(clinic.value).forEach(key => {
      if (key === 'working_hours') {
        formData.append('working_hours', JSON.stringify(clinic.value.working_hours));
      } else if (key === 'logo') {
        if (logoFile.value) formData.append('logo', logoFile.value);
      } else {
        formData.append(key, clinic.value[key]);
      }
    });

    // Laravel requires a special trick for PUT with FormData: use POST + _method=PUT
    // formData.append('_method', 'PUT');
    // But since our route is Route::post('/settings', ...), we just use POST.
    
    await api.post('/clinic/settings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    alert('Settings saved successfully!');
    fetchSettings();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save settings');
  } finally {
    saving.value = false;
  }
}

onMounted(fetchSettings);
</script>
