import './echo';
import './order-alerts';

window.flyToCart = (event, imageUrl) => {
    const cart = document.querySelector('[data-cart-target]');
    const card = event.currentTarget.closest('[data-menu-item-card]');
    const source = card?.querySelector('[data-menu-item-image]');

    if (!cart || !source || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const sourceRect = source.getBoundingClientRect();
    const cartRect = cart.getBoundingClientRect();
    const size = Math.min(76, Math.max(58, sourceRect.width * 0.25));
    const startX = sourceRect.left + (sourceRect.width - size) / 2;
    const startY = sourceRect.top + (sourceRect.height - size) / 2;
    const endX = cartRect.left + cartRect.width / 2 - size / 2;
    const endY = cartRect.top + cartRect.height / 2 - size / 2;
    const deltaX = endX - startX;
    const deltaY = endY - startY;

    const flyingImage = document.createElement('img');
    flyingImage.src = imageUrl;
    flyingImage.alt = '';
    flyingImage.setAttribute('aria-hidden', 'true');
    Object.assign(flyingImage.style, {
        position: 'fixed',
        zIndex: '9999',
        pointerEvents: 'none',
        left: `${startX}px`,
        top: `${startY}px`,
        width: `${size}px`,
        height: `${size}px`,
        objectFit: 'cover',
        borderRadius: '18px',
        border: '3px solid rgba(255, 255, 255, .95)',
        boxShadow: '0 14px 35px rgba(68, 30, 22, .35)',
        willChange: 'transform, opacity',
    });

    document.body.appendChild(flyingImage);

    const animation = flyingImage.animate([
        { transform: 'translate3d(0, 0, 0) scale(1) rotate(0deg)', opacity: 1 },
        {
            transform: `translate3d(${deltaX * 0.48}px, ${Math.min(deltaY * 0.35, -70)}px, 0) scale(.82) rotate(-8deg)`,
            opacity: 1,
            offset: 0.48,
        },
        { transform: `translate3d(${deltaX}px, ${deltaY}px, 0) scale(.18) rotate(12deg)`, opacity: .25 },
    ], {
        duration: 760,
        easing: 'cubic-bezier(.22,.8,.28,1)',
        fill: 'forwards',
    });

    animation.finished.finally(() => {
        flyingImage.remove();
        const currentCart = document.querySelector('[data-cart-target]');

        if (currentCart) {
            currentCart.classList.remove('cart-receive-pulse');
            void currentCart.offsetWidth;
            currentCart.classList.add('cart-receive-pulse');
            setTimeout(() => currentCart.classList.remove('cart-receive-pulse'), 600);
        }
    });
};
