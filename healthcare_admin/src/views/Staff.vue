<template>
  <AdminLayout title="Staff Management">
    <div class="space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Secretaries</h2>
          <p class="text-slate-500">Manage your clinic staff accounts.</p>
        </div>
        <button @click="openAddModal" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl font-bold shadow-lg shadow-teal-100 hover:bg-teal-700 transition-all flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add Staff Member
        </button>
      </div>

      <!-- Staff Table -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Name</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Role</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Email</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="member in staff" :key="member.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-8 py-5">
                <div class="flex items-center gap-3">
                   <div class="h-8 w-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs uppercase">{{ member.name[0] }}</div>
                   <span class="font-bold text-slate-900">{{ member.name }}</span>
                </div>
              </td>
              <td class="px-8 py-5">
                 <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border" :class="member.role === 'doctor' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-50 text-slate-600 border-slate-200'">
                   {{ member.role }}
                 </span>
              </td>
              <td class="px-8 py-5 text-slate-600 font-medium">{{ member.email }}</td>
              <td class="px-8 py-5">
                 <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border" :class="member.is_active ? 'bg-green-50 text-green-700 border-green-100' : 'bg-slate-50 text-slate-400 border-slate-100'">
                   {{ member.is_active ? 'Active' : 'Inactive' }}
                 </span>
              </td>
              <td class="px-8 py-5 text-right">
                 <button @click="toggleStatus(member)" class="text-xs font-bold text-slate-500 hover:text-teal-600 transition-colors">
                   {{ member.is_active ? 'Deactivate' : 'Reactivate' }}
                 </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Modal -->
      <div v-if="showModal" @click.self="showModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-white/10 transition-opacity overflow-y-auto">
         <div @click.stop class="relative bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl border border-slate-100">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Create Secretary Account</h3>
            <form @submit.prevent="saveStaff" class="space-y-4">
               <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                  <input v-model="form.name" type="text" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none transition-all" />
               </div>
               <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                  <input v-model="form.email" type="email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none transition-all" />
               </div>
               <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">Account Role</label>
                  <select v-model="form.role" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none transition-all">
                    <option value="secretary">Secretary / Assistant</option>
                    <option value="doctor">Medical Doctor</option>
                  </select>
               </div>
               <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                  <input v-model="form.password" type="password" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-teal-500 outline-none transition-all" />
               </div>
               <div class="flex gap-3 pt-6">
                  <button type="button" @click="showModal = false" class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold">Cancel</button>
                  <button type="submit" :disabled="loading" class="flex-1 px-4 py-2.5 bg-teal-600 text-white rounded-xl font-bold shadow-lg shadow-teal-100">Create</button>
               </div>
            </form>
         </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const staff = ref([]);
const showModal = ref(false);
const loading = ref(false);

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'secretary',
});

const openAddModal = () => {
  form.value = { name: '', email: '', password: '', role: 'secretary' };
  showModal.value = true;
};

async function fetchStaff() {
  try {
    const response = await api.get('/clinic/staff');
    staff.value = response.data;
  } catch (err) {
    console.error('Failed to fetch staff:', err);
  }
}

async function saveStaff() {
  loading.value = true;
  try {
    await api.post('/clinic/staff', form.value);
    showModal.value = false;
    fetchStaff();
    // Reset form
    form.value = { name: '', email: '', password: '' };
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save staff');
  } finally {
    loading.value = false;
  }
}

async function toggleStatus(member) {
  try {
    const response = await api.patch(`/clinic/staff/${member.id}/toggle`);
    member.is_active = response.data.is_active;
  } catch (err) {
    console.error('Failed to toggle status:', err);
  }
}

onMounted(fetchStaff);
</script>
