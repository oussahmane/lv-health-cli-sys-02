<template>
  <AdminLayout>
    <div class="space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Clinics Management</h2>
          <p class="text-slate-500">Add and manage medical facilities on the platform.</p>
        </div>
        <button @click="showAddModal = true" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl font-bold shadow-lg shadow-teal-100 hover:bg-teal-700 transition-all flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add New Clinic
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
         <div class="relative w-full md:w-96">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Search clinics by name or address..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400" />
         </div>
         
         <div class="flex items-center gap-4 w-full md:w-auto">
            <select v-model="statusFilter" class="flex-1 md:w-48 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all font-semibold text-slate-700">
               <option value="all">All Status</option>
               <option value="active">Active Only</option>
               <option value="inactive">Inactive Only</option>
            </select>
            
            <div class="px-4 py-3 bg-slate-100 rounded-2xl text-xs font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">
               {{ filteredClinics.length }} Results
            </div>
         </div>
      </div>

      <!-- Clinics Table -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Clinic Info</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Subscription Plan</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Access Status</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="clinic in filteredClinics" :key="clinic.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <img v-if="clinic.logo" :src="'http://localhost:8000/storage/' + clinic.logo" class="h-full w-full object-cover rounded-xl" />
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-900">{{ clinic.name }}</h4>
                    <p class="text-[11px] text-slate-500">{{ clinic.address }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <div class="flex flex-col">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase tracking-wider w-fit">
                    {{ clinic.plan?.name || 'No Plan' }}
                  </span>
                  <span class="text-[10px] text-slate-400 mt-1 font-medium">{{ clinic.users_count }} staff members</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <div class="flex flex-col gap-1">
                  <button @click="toggleStatus(clinic)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors cursor-pointer w-fit" :class="clinic.is_active ? 'bg-green-50 text-green-700 border-green-100 hover:bg-green-100' : 'bg-red-50 text-red-700 border-red-100 hover:bg-red-100'">
                    {{ clinic.is_active ? 'Active' : 'Deactivated' }}
                  </button>
                  <span :class="paymentStatusClass(clinic.payment_status)" class="text-[9px] font-black uppercase tracking-widest w-fit p-1 rounded-md">
                    {{ clinic.payment_status }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-5 text-right">
                <button @click="openEditModal(clinic)" class="p-2 text-slate-400 hover:text-teal-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
              </td>
            </tr>
            <tr v-if="filteredClinics.length === 0">
               <td colspan="4" class="px-6 py-20 text-center">
                  <div class="max-w-xs mx-auto space-y-3">
                     <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                     </div>
                     <h3 class="font-bold text-slate-900">No clinics found</h3>
                     <p class="text-xs text-slate-500">Try adjusting your search query or status filter.</p>
                  </div>
               </td>
            </tr>
          </tbody>
        </table>
      </div>
 
       <!-- Add/Edit Modal -->
      <div v-if="showAddModal" @click.self="closeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-white/10 transition-opacity overflow-y-auto">
        <div @click.stop class="bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-xl sm:w-full border border-slate-100 mt-20 mb-20">
          <div class="bg-white px-8 pt-8 pb-8">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-xl font-bold text-slate-900 mb-6" id="modal-title">{{ isEditing ? 'Edit Clinic Details' : 'Create New Clinic' }}</h3>
                
                <form @submit.prevent="saveClinic" class="grid grid-cols-2 gap-4">
                  <div class="col-span-2">
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Clinic Name</label>
                     <input v-model="form.name" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all" />
                  </div>
                  <div class="col-span-2">
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Address</label>
                     <input v-model="form.address" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all" />
                  </div>
                  <div>
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Phone</label>
                     <input v-model="form.phone" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all" />
                  </div>
                  <div>
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Timezone</label>
                     <select v-model="form.timezone" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all">
                        <option value="Africa/Algiers">Africa/Algiers</option>
                        <option value="UTC">UTC</option>
                     </select>
                  </div>
                  <div class="col-span-2">
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Clinic Tier / Plan</label>
                     <select v-model="form.plan_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all">
                        <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                     </select>
                  </div>

                  <div class="col-span-2 mt-4">
                     <h4 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 mb-4">
                       {{ isEditing ? 'Primary Admin Account' : 'Initial Admin Account' }}
                     </h4>
                  </div>

                  <div class="col-span-2">
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Admin Full name</label>
                     <input v-model="form.admin_name" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all" />
                  </div>
                  <div>
                     <label class="block text-sm font-semibold text-slate-700 mb-1">Admin Email</label>
                     <input v-model="form.admin_email" type="email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all" />
                  </div>
                  <div>
                     <label class="block text-sm font-semibold text-slate-700 mb-1">
                       Admin Password
                       <span v-if="isEditing" class="text-[10px] text-slate-400 font-normal ml-1">(Leave blank to keep current)</span>
                     </label>
                     <input v-model="form.admin_password" type="password" :required="!isEditing" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 focus:border-teal-500 outline-none transition-all" />
                  </div>

                  <div class="col-span-2 flex gap-3 mt-8">
                    <button type="button" @click="closeModal" class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">Cancel</button>
                    <button type="submit" :disabled="loading" class="flex-1 px-4 py-2.5 bg-teal-600 text-white rounded-xl font-bold shadow-lg shadow-teal-100 hover:bg-teal-700 transition-all">
                      {{ isEditing ? 'Update Clinic' : 'Create Clinic' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';
 
const clinics = ref([]);
const plans = ref([]);
const searchQuery = ref('');
const statusFilter = ref('all');
const showAddModal = ref(false);
const loading = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
 
const filteredClinics = computed(() => {
  return clinics.value.filter(clinic => {
    const matchesSearch = clinic.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                         clinic.address.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesStatus = statusFilter.value === 'all' || 
                         (statusFilter.value === 'active' && clinic.is_active) || 
                         (statusFilter.value === 'inactive' && !clinic.is_active);
    
    return matchesSearch && matchesStatus;
  });
});

const paymentStatusClass = (status) => {
  switch (status) {
    case 'paid': return 'bg-green-100 text-green-800';
    case 'pending': return 'bg-orange-100 text-orange-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-slate-100 text-slate-500';
  }
};

const form = ref({
  name: '',
  address: '',
  phone: '',
  timezone: 'Africa/Algiers',
  plan_id: null,
  admin_name: '',
  admin_email: '',
  admin_password: '',
  is_active: true,
});

async function fetchClinics() {
  try {
    const response = await api.get('/super/clinics');
    clinics.value = response.data;
  } catch (err) {
    console.error('Failed to fetch clinics:', err);
  }
}

async function fetchPlans() {
  try {
    const response = await api.get('/super/plans');
    plans.value = response.data;
    if (plans.value.length > 0 && !form.value.plan_id) {
        form.value.plan_id = plans.value[0].id;
    }
  } catch (err) {
    console.error('Failed to fetch plans:', err);
  }
}

function openEditModal(clinic) {
  isEditing.value = true;
  editingId.value = clinic.id;
  const admin = clinic.admins?.[0] || {};
  form.value = {
    name: clinic.name,
    address: clinic.address,
    phone: clinic.phone,
    timezone: clinic.timezone,
    plan_id: clinic.plan_id,
    is_active: clinic.is_active,
    admin_name: admin.name || '',
    admin_email: admin.email || '',
    admin_password: '', // Always empty for editing unless changed
  };
  showAddModal.value = true;
}

function closeModal() {
  showAddModal.value = false;
  isEditing.value = false;
  editingId.value = null;
  resetForm();
}

function resetForm() {
   form.value = {
    name: '',
    address: '',
    phone: '',
    timezone: 'Africa/Algiers',
    plan_id: plans.value[0]?.id || null,
    admin_name: '',
    admin_email: '',
    admin_password: '',
    is_active: true,
  };
}

async function saveClinic() {
  loading.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/super/clinics/${editingId.value}`, form.value);
    } else {
      await api.post('/super/clinics', form.value);
    }
    closeModal();
    fetchClinics();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save clinic');
  } finally {
    loading.value = false;
  }
}

async function toggleStatus(clinic) {
  try {
    const response = await api.patch(`/super/clinics/${clinic.id}/toggle`);
    clinic.is_active = response.data.is_active;
  } catch (err) {
    console.error('Failed to toggle status:', err);
  }
}

onMounted(() => {
    fetchClinics();
    fetchPlans();
});
</script>
