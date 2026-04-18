import { defineStore } from 'pinia';
import api from './api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('clinic_auth_token') || null,
        loading: false,
        error: null,
    }),
    
    getters: {
        isAuthenticated: (state) => !!state.token,
        isClinicAdmin: (state) => state.user?.role === 'clinic_admin',
        isSecretary: (state) => state.user?.role === 'secretary',
        isDoctor: (state) => state.user?.role === 'doctor',
        clinic: (state) => state.user?.clinic || null,
        permissions: (state) => state.user?.permissions || [],
        can: (state) => (permission) => {
            if (state.user?.permissions?.includes('*')) return true;
            return state.user?.permissions?.includes(permission);
        }
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/login', { email, password });
                
                const role = response.data.user.role;
                const allowedRoles = ['clinic_admin', 'secretary', 'doctor'];
                
                if (!allowedRoles.includes(role)) {
                    throw new Error('Access denied. Only clinic staff can access this portal.');
                }

                this.token = response.data.access_token;
                this.user = response.data.user;
                localStorage.setItem('clinic_auth_token', this.token);
                return true;
            } catch (err) {
                this.error = err.response?.data?.message || err.message;
                return false;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await api.post('/logout');
            } catch (err) {
                console.error('Logout failed:', err);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('clinic_auth_token');
            }
        },

        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await api.get('/me');
                this.user = response.data;
            } catch (err) {
                this.logout();
            }
        }
    }
});
