import { defineStore } from 'pinia';
import api from './api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('auth_token') || null,
        loading: false,
        error: null,
    }),
    
    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'super_admin',
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/login', { email, password });
                
                if (response.data.user.role !== 'super_admin') {
                    throw new Error('Access denied. Super admins only.');
                }

                this.token = response.data.access_token;
                this.user = response.data.user;
                localStorage.setItem('auth_token', this.token);
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
                localStorage.removeItem('auth_token');
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
