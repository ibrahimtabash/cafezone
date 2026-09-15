@if (auth()->user()?->canManageOrders() || auth()->user()?->canOnlyViewOrders())
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        try {
            if (localStorage.getItem('cafe_order_alerts_enabled') === '1') {
                document.documentElement.dataset.orderAlertsEnabled = 'true';
            }
        } catch (error) {}
    </script>
    <div data-admin-order-alerts></div>
    <style>.order-unread > td { background-color: rgba(245,158,11,.16) !important; } .order-unread > td:first-child { border-inline-start: 4px solid #f59e0b; }</style>
    <button type="button" class="admin-alert-toggle" data-enable-order-alerts aria-pressed="false">
        <span class="admin-alert-toggle__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg></span>
        <span data-alert-label>تفعيل صوت الطلبات</span>
        <small data-alert-hint>اضغط مرة واحدة</small>
        <span class="admin-alert-toggle__dot"></span>
    </button>
@endif
