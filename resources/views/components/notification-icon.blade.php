@php
    $currentResidentId = null;
    if (auth()->check() && auth()->user()->hasRole('resident')) {
        $resident = \App\Models\Resident::where('user_id', auth()->id())->first();
        if ($resident) {
            $currentResidentId = $resident->id;
        }
    }
@endphp

<div
    x-data="notificationIcon({{ $currentResidentId ?? 'null' }})"
    @click.outside="isOpen = false"
    @keydown.escape.window="isOpen = false"
    class="relative inline-block text-left font-sans"
>
    {{-- Notification Bell Icon --}}
    <div>
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-full border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500 relative min-w-fit"
            id="menu-button"
            aria-expanded="true"
            aria-haspopup="true"
            @click="toggleDropdown()"
        >
            {{-- Bell Icon (using inline SVG) --}}
            <svg class="h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a6 6 0 00-6 0M12 21a2 2 0 01-2-2h4a2 2 0 01-2 2z" />
            </svg>

            {{-- Unread Count Badge --}}
            <template x-if="unreadCount > 0">
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                    <span x-text="unreadCount"></span>
                </span>
            </template>
        </button>
    </div>

    {{-- Notifications Dropdown --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="menu-button"
        tabindex="-1"
    >
        <div class="py-1" role="none">
            {{-- Dropdown Header --}}
            <div class="px-4 py-2 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                <button
                    x-show="unreadCount > 0 && !loading"
                    @click.stop="markAllAsRead()"
                    class="text-sm text-indigo-600 hover:text-indigo-800 focus:outline-none"
                >
                    Mark all as read
                </button>
                <span x-show="loading" class="text-sm text-gray-500">Loading...</span>
            </div>

            {{-- Control Buttons (Moved to top) --}}
            <div class="p-4 border-b border-gray-200 grid grid-cols-2 gap-2 text-center">
                <button
                    x-show="!loading && notifications.some(n => !n.is_read) && currentViewMode !== 'unread'"
                    @click.stop="viewUnreadNotifications()"
                    class="w-full bg-yellow-500 text-white py-2 px-4 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 text-sm"
                >
                    View Unread
                </button>
                <button
                    x-show="!loading && currentViewMode !== 'all'"
                    @click.stop="viewAllNotifications()"
                    class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 text-sm"
                >
                    View All
                </button>
                <button
                    x-show="currentViewMode !== 'paginated'"
                    @click.stop="resetToInitialView()"
                    class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 text-sm col-span-2"
                >
                    View Recent (back to top 10)
                </button>
            </div>

            {{-- Unread Notifications Section --}}
            <template x-if="unreadNotifications.length > 0 && !loading">
                <div class="p-4">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Unread</h4>
                    <ul class="space-y-2">
                        <template x-for="notification in unreadNotifications" :key="notification.id + '-' + notification.source_type">
                            <li
                                @click.stop="markAsRead(notification.id, notification.source_type)"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 rounded-md bg-blue-50 border-l-4 border-blue-500"
                            >
                                <p class="text-sm font-medium text-gray-900" x-text="notification.message"></p>
                                <p class="text-xs text-gray-500 mt-1"><span x-text="new Date(notification.timestamp).toLocaleString()"></span></p>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            {{-- Read Notifications Section --}}
            <template x-if="readNotifications.length > 0 && !loading">
                <div :class="{ 'border-t border-gray-200': unreadNotifications.length > 0 }" class="p-4 mt-2">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Read</h4>
                    <ul class="space-y-2">
                        <template x-for="notification in readNotifications" :key="notification.id + '-' + notification.source_type">
                            <li
                                class="px-4 py-2 text-gray-600 rounded-md"
                            >
                                <p class="text-sm" x-text="notification.message"></p>
                                <p class="text-xs text-gray-400 mt-1"><span x-text="new Date(notification.timestamp).toLocaleString()"></span></p>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            {{-- No Notifications Message --}}
            <template x-if="notifications.length === 0 && !loading">
                <div class="p-4 text-center text-gray-500">
                    No notifications to display.
                </div>
            </template>

            {{-- Loading State --}}
            <template x-if="loading">
                <div class="p-4 text-center text-gray-500">
                    Fetching notifications...
                </div>
            </template>

            {{-- View More Button (Remains at bottom) --}}
            <div class="p-4 border-t border-gray-200 flex flex-col space-y-2">
                <button
                    x-show="hasMorePages && !loading && currentViewMode === 'paginated'"
                    @click.stop="loadMoreNotifications()"
                    class="w-full bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-sm"
                >
                    View More (<span x-text="remainingNotificationsCount"></span> remaining)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alpine.js component definition --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationIcon', (initialResidentId) => ({
            isOpen: false,
            notifications: [],
            loading: false,
            residentId: initialResidentId,
            currentPage: 1,
            perPage: 10,
            totalNotificationsCount: 0,
            hasMorePages: false,
            currentViewMode: 'paginated', // 'paginated', 'all', 'unread'

            init() {
                // You can pre-fetch notifications here if you want them loaded
                // immediately when the page loads, rather than on first click.
            },

            get unreadCount() {
                // This is a computed property, it filters the currently loaded notifications
                // For a more accurate count that might not be fully loaded,
                // you'd need a separate API endpoint that just returns unread count.
                return this.notifications.filter(n => !n.is_read).length;
            },

            get remainingNotificationsCount() {
                // Calculates how many more notifications are there beyond the current loaded amount
                return Math.max(0, this.totalNotificationsCount - this.notifications.length);
            },

            fetchNotifications() {
                if (this.residentId === null) {
                    console.warn('Resident ID not available. Cannot fetch notifications.');
                    this.loading = false;
                    return;
                }

                this.loading = true;
                const url = `/api/notifications/resident/${this.residentId}?page=${this.currentPage}&per_page=${this.perPage}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.data) {
                            if (this.currentPage === 1 && this.currentViewMode === 'paginated') {
                                this.notifications = data.data.data;
                            } else if (this.currentViewMode === 'paginated') {
                                // Append new unique notifications for subsequent pages
                                const newNotifications = data.data.data.filter(newNotif =>
                                    !this.notifications.some(existingNotif =>
                                        existingNotif.id === newNotif.id && existingNotif.source_type === newNotif.source_type
                                    )
                                );
                                this.notifications = this.notifications.concat(newNotifications);
                            } else {
                                // If switching from 'all' or 'unread' back to 'paginated', replace completely
                                this.notifications = data.data.data;
                            }

                            this.totalNotificationsCount = data.data.total;
                            this.hasMorePages = (data.data.current_page < data.data.last_page);
                            this.currentViewMode = 'paginated';
                        } else {
                            console.error('API Error:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching notifications:', error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            loadMoreNotifications() {
                this.currentPage++;
                this.fetchNotifications();
            },

            viewAllNotifications() {
                if (this.residentId === null) {
                    console.warn('Resident ID not available. Cannot fetch all notifications.');
                    return;
                }
                this.loading = true;
                fetch(`/api/notifications/resident/${this.residentId}/all`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.notifications = data.data; // Replace with all notifications
                            this.totalNotificationsCount = data.data.length; // Update total count
                            this.hasMorePages = false; // No more pages when viewing all
                            this.currentViewMode = 'all'; // Set view mode
                        } else {
                            console.error('API Error viewing all:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching all notifications:', error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            viewUnreadNotifications() {
                 if (this.residentId === null) {
                    console.warn('Resident ID not available. Cannot fetch unread notifications.');
                    return;
                }
                this.loading = true;
                fetch(`/api/notifications/resident/${this.residentId}/unread`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.notifications = data.data; // Replace with only unread notifications
                            this.totalNotificationsCount = data.data.length; // Update total count
                            this.hasMorePages = false; // No more pages when viewing all
                            this.currentViewMode = 'unread'; // Set view mode
                        } else {
                            console.error('API Error viewing unread:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching unread notifications:', error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            resetToInitialView() {
                this.currentPage = 1;
                this.perPage = 10;
                this.fetchNotifications(); // Reload initial paginated view
            },

            markAsRead(notificationId, sourceType) {
                if (this.residentId === null) {
                    console.warn('Resident ID not available. Cannot mark notification as read.');
                    return;
                }
                // Optimistically update UI
                this.notifications = this.notifications.map(n =>
                    (n.id === notificationId && n.source_type === sourceType) ? { ...n, is_read: true } : n
                );

                fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ source_type: sourceType })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        console.error('API Error marking as read:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            },

            markAllAsRead() {
                if (this.residentId === null) {
                    console.warn('Resident ID not available. Cannot mark all notifications as read.');
                    return;
                }
                const unreadNotifications = this.notifications.filter(n => !n.is_read);

                // Optimistically update UI
                this.notifications = this.notifications.map(n => ({ ...n, is_read: true }));

                unreadNotifications.forEach(notif => {
                    fetch(`/api/notifications/${notif.id}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ source_type: notif.source_type })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            console.error('API Error marking all as read for ID:', notif.id, data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error marking all notifications as read for ID:', notif.id, error);
                    });
                });
            },

            get unreadNotifications() {
                return this.notifications.filter(n => !n.is_read);
            },
            get readNotifications() {
                return this.notifications.filter(n => n.is_read);
            },

            toggleDropdown() {
                this.isOpen = !this.isOpen;
                // Fetch initial paginated view when opening if not already loaded or view mode changed
                if (this.isOpen && (this.notifications.length === 0 || this.currentViewMode !== 'paginated')) {
                    this.currentPage = 1; // Reset to first page
                    this.fetchNotifications();
                }
            },
        }));
    });
</script>