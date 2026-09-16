let audio;
let toastTimer = 0;
const alertPreferenceKey = 'cafe_order_alerts_enabled';

function alertsEnabled() {
    try { return localStorage.getItem(alertPreferenceKey) === '1'; }
    catch { return false; }
}

function rememberAlerts() {
    try {
        localStorage.setItem(alertPreferenceKey, '1');
        document.documentElement.dataset.orderAlertsEnabled = 'true';
    }
    catch { /* Notifications still work for the current page. */ }
}

function updateAlertButtons(enabled) {
    document.querySelectorAll('[data-enable-order-alerts]').forEach(button => {
        const wrapper = button.closest('[data-alert-activation-wrap]');
        if (wrapper) wrapper.hidden = enabled;
        else button.hidden = enabled;
        button.classList.toggle('is-enabled', enabled);
        const label = button.querySelector('[data-alert-label]');
        const hint = button.querySelector('[data-alert-hint]');
        if (label) label.textContent = enabled ? 'صوت الطلبات مفعّل' : 'تفعيل صوت الطلبات';
        if (hint) hint.textContent = enabled ? 'سيصلك تنبيه فور وصول الطلب' : 'اضغط مرة واحدة';
        button.setAttribute('aria-pressed', String(enabled));
        button.disabled = false;
    });
}

window.enableOrderAlerts = async () => {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) throw new Error('audio-not-supported');
    audio ??= new AudioContext();
    await audio.resume();

    if ('Notification' in window && Notification.permission === 'default') {
        await Notification.requestPermission();
    }

    const browserNotifications = !('Notification' in window) || Notification.permission !== 'denied';
    rememberAlerts();
    updateAlertButtons(true);
    if (document.querySelector('[data-admin-order-alerts]')) playAdminAlert();
    else playCustomerAlert();
    toast(browserNotifications ? 'ستسمع صوتاً عند وصول طلب جديد' : 'الصوت مفعّل، لكن إشعارات النظام محظورة من إعدادات المتصفح', null, {
        title: 'جاهزين لأي طلب جديد',
        type: 'success',
        duration: 4500,
    });
};

async function restoreAlertAudio() {
    if (!alertsEnabled()) return;
    updateAlertButtons(true);
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    audio ??= new AudioContext();
    try { await audio.resume(); } catch { /* The next user interaction retries it. */ }
}

async function handleAlertActivation(button) {
    if (button.disabled) return;
    button.disabled = true;
    const label = button.querySelector('[data-alert-label]');
    if (label) label.textContent = 'جاري التفعيل…';
    try {
        await window.enableOrderAlerts();
    } catch (error) {
        button.disabled = false;
        updateAlertButtons(false);
        toast('تعذّر تشغيل الصوت. اسمح بالصوت والإشعارات من إعدادات المتصفح ثم حاول مرة أخرى.', null, {
            title: 'لم يتم تفعيل الإشعارات', type: 'error', duration: 7000,
        });
    }
}

function playNotes(notes, volumeLevel, wave = 'sine') {
    if (audio?.state !== 'running') return;

    const compressor = audio.createDynamicsCompressor();
    compressor.threshold.value = -18;
    compressor.knee.value = 12;
    compressor.ratio.value = 6;
    compressor.attack.value = .003;
    compressor.release.value = .2;
    compressor.connect(audio.destination);

    notes.forEach(({ delay, frequency, duration }) => {
        const tone = audio.createOscillator();
        const volume = audio.createGain();
        const start = audio.currentTime + delay;
        tone.type = wave;
        tone.frequency.setValueAtTime(frequency, start);
        tone.connect(volume);
        volume.connect(compressor);
        volume.gain.setValueAtTime(.001, start);
        volume.gain.exponentialRampToValueAtTime(volumeLevel, start + .025);
        volume.gain.setValueAtTime(volumeLevel, start + Math.max(.03, duration - .09));
        volume.gain.exponentialRampToValueAtTime(.001, start + duration);
        tone.start(start);
        tone.stop(start + duration + .02);
    });
}

function playAdminAlert() {
    // One short, distinct bell strike for newly received admin orders.
    playNotes([
        { delay: 0, frequency: 1175, duration: .38 },
    ], .3, 'sine');
}

function playCustomerAlert() {
    playNotes([
        { delay: 0, frequency: 780, duration: .28 },
        { delay: .18, frequency: 1040, duration: .3 },
    ], .12);
}

function toast(message, url = null, options = {}) {
    const { title = url ? 'طلب جديد' : 'تحديث على طلبك', type = 'order', duration = 10000 } = options;
    let container = document.querySelector('[data-order-toast-container]');

    if (!container) {
        container = document.createElement('div');
        container.dataset.orderToastContainer = '';
        container.className = 'order-toast-container';
        container.setAttribute('aria-live', 'polite');
        document.body.append(container);
    }

    container.replaceChildren();
    clearTimeout(toastTimer);

    const box = document.createElement('div');
    box.className = `order-alert-toast order-alert-toast--${type}`;
    box.setAttribute('role', 'status');

    const icon = document.createElement('span');
    icon.className = 'order-alert-toast__icon';
    icon.innerHTML = type === 'success'
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';

    const content = document.createElement('span');
    content.className = 'order-alert-toast__content';
    const heading = document.createElement('strong');
    heading.className = 'order-alert-toast__title';
    heading.textContent = title;
    const description = document.createElement('span');
    description.className = 'order-alert-toast__message';
    description.textContent = message;
    content.append(heading, description);

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'order-alert-toast__close';
    close.setAttribute('aria-label', 'إغلاق الإشعار');
    close.textContent = '×';

    const progress = document.createElement('span');
    progress.className = 'order-alert-toast__progress';
    progress.style.animationDuration = `${duration}ms`;

    box.append(icon, content);
    if (url) {
        const action = document.createElement('a');
        action.href = url;
        action.className = 'order-alert-toast__action';
        action.textContent = 'عرض الطلب';
        box.append(action);
    }
    box.append(close, progress);
    container.append(box);

    requestAnimationFrame(() => box.classList.add('is-visible'));
    const dismiss = () => {
        box.classList.remove('is-visible');
        setTimeout(() => box.remove(), 260);
    };
    close.addEventListener('click', dismiss);
    toastTimer = setTimeout(dismiss, duration);
}

function alertOrder(message, url) {
    toast(message, url, { title: url ? 'وصل طلب جديد' : 'تحديث على طلبك' });
    if (url) playAdminAlert();
    else playCustomerAlert();
    if ('Notification' in window && Notification.permission === 'granted') {
        const notification = new Notification('كافيه زون', { body: message, icon: '/assets/images/logo2026.png', tag: 'cafe-order', renotify: true });
        if (url) notification.onclick = () => { window.focus(); window.location.href = url; };
    }
}

let subscribed;
function subscribe() {
    updateAlertButtons(alertsEnabled());
    if (!window.Echo) return;
    const token = document.querySelector('[data-order-token]')?.dataset.orderToken;
    const admin = document.querySelector('[data-admin-order-alerts]');
    const key = admin ? 'orders' : token ? `order.${token}` : null;
    if (subscribed === key) return;
    if (subscribed) window.Echo.leave(subscribed);
    subscribed = key;
    if (!key) return;
    const channel = admin ? window.Echo.private(key) : window.Echo.channel(key);
    channel.listen('OrderChanged', event => {
        if (admin) {
            window.Livewire?.dispatch('orders-changed');
            if (event.isNew) alertOrder('الطلب بانتظار مراجعة إثبات الدفع', `/admin/orders/${event.id}`);
        } else {
            // Refresh the tracking component from the same event that produced the alert.
            // This avoids waiting for the 30-second fallback poll.
            window.Livewire?.dispatch('order-status-changed');
            if (event.message) alertOrder(event.message);
        }
    });
    window.Echo.connector.pusher.connection.bind('connected', () => {
        if (admin) window.Livewire?.dispatch('orders-changed');
    });
}
document.addEventListener('click', event => {
    const button = event.target.closest('[data-enable-order-alerts]');
    if (button) handleAlertActivation(button);
});
['pointerdown', 'keydown', 'touchstart'].forEach(eventName => {
    document.addEventListener(eventName, restoreAlertAudio, { passive: true });
});
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') restoreAlertAudio();
});
document.addEventListener('livewire:navigated', subscribe);
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { subscribe(); restoreAlertAudio(); });
} else {
    subscribe(); restoreAlertAudio();
}
