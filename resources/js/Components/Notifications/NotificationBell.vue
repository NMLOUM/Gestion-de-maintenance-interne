<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const notifications = ref([]);
const unreadCount = ref(0);
const isOpen = ref(false);
const loading = ref(false);

const loadNotifications = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/notifications/unread');
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.count;
    } catch (error) {
        console.error('Erreur chargement notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`);
        notifications.value = notifications.value.filter(n => n.id !== notificationId);
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (error) {
        console.error('Erreur marquage notification:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-read');
        notifications.value = [];
        unreadCount.value = 0;
    } catch (error) {
        console.error('Erreur marquage toutes notifications:', error);
    }
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && notifications.value.length === 0) {
        loadNotifications();
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

onMounted(() => {
    loadNotifications();
    setInterval(loadNotifications, 30000);
});

const formatDate = (date) => {
    const now = new Date();
    const notifDate = new Date(date);
    const diffSeconds = Math.floor((now - notifDate) / 1000);

    if (diffSeconds < 60) return "À l'instant";
    if (diffSeconds < 3600) return `Il y a ${Math.floor(diffSeconds / 60)} min`;
    if (diffSeconds < 86400) return `Il y a ${Math.floor(diffSeconds / 3600)}h`;
    return notifDate.toLocaleDateString('fr-FR');
};

const getNotificationIcon = (type) => {
    const icons = {
        ticket_created: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ticket_assigned: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        status_changed: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        comment_added: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
    };
    return icons[type] || icons.ticket_created;
};

const getTypeColor = (type) => {
    const colors = {
        ticket_created: 'bg-blue-100 text-blue-800',
        ticket_assigned: 'bg-green-100 text-green-800',
        status_changed: 'bg-yellow-100 text-yellow-800',
        comment_added: 'bg-purple-100 text-purple-800'
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <div class="relative">
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors duration-200"
            :class="{ 'bg-gray-100': isOpen }"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>

            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                @click.away="closeDropdown"
                class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            >
                <div class="px-4 py-3 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                        <button
                            v-if="notifications.length > 0"
                            @click="markAllAsRead"
                            class="text-xs text-blue-600 hover:text-blue-800"
                        >
                            Tout marquer comme lu
                        </button>
                    </div>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <div v-if="loading" class="py-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    </div>

                    <div v-else-if="notifications.length === 0" class="py-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-sm text-gray-500">Aucune notification</p>
                    </div>

                    <div v-else>
                        <Link
                            v-for="notification in notifications"
                            :key="notification.id"
                            :href="`/tickets/${notification.ticket_id}`"
                            @click="markAsRead(notification.id)"
                            class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0"
                        >
                            <div class="flex items-start">
                                <div :class="getTypeColor(notification.type)"
                                     class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path :d="getNotificationIcon(notification.type)"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="notifications.length > 0" class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    <Link href="/notifications" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Voir toutes les notifications
                    </Link>
                </div>
            </div>
        </Transition>
    </div>
</template>