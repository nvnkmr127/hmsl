<div x-data="{ 
        notifications: [],
        add(notification) {
            const id = Date.now();
            this.notifications.push({
                id: id,
                type: notification.type || 'info',
                message: notification.message || '',
                show: false
            });
            this.$nextTick(() => {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) this.notifications[index].show = true;
            });
            setTimeout(() => {
                this.remove(id);
            }, 3000);
        },
        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index !== -1) {
                this.notifications[index].show = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 400);
            }
        }
     }"
     @notify.window="add($event.detail)"
     class="fixed bottom-6 right-6 z-[100] flex flex-col space-y-3 w-full max-w-sm">
    
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-90"
             class="p-4 rounded-2xl shadow-2xl border flex items-center space-x-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl transition-all"
             :class="{
                'border-indigo-500/20 text-indigo-700 dark:text-indigo-300': notification.type === 'info',
                'border-emerald-500/20 text-emerald-700 dark:text-emerald-300': notification.type === 'success',
                'border-amber-500/20 text-amber-700 dark:text-amber-300': notification.type === 'warning',
                'border-red-500/20 text-red-700 dark:text-red-300': notification.type === 'error'
             }">
            
            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gray-100/50 dark:bg-gray-700/50 shadow-inner">
                <svg x-show="notification.type === 'info'" class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <svg x-show="notification.type === 'success'" class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <svg x-show="notification.type === 'warning'" class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <svg x-show="notification.type === 'error'" class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            
            <div class="flex-1">
                <p class="text-sm font-bold leading-tight" x-text="notification.message"></p>
            </div>
            
            <button @click="remove(notification.id)" class="p-1 rounded-full hover:bg-gray-200/50 dark:hover:bg-gray-700/50 transition-colors">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" /></svg>
            </button>
        </div>
    </template>
</div>
