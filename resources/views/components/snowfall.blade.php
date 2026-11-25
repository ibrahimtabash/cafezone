{{-- ثلج متساقط عشوائي (مواضع وأحجام وسرعات تتغيّر عشوائياً في كل دورة) + سقيع على حواف الشاشة --}}
<div class="frost" aria-hidden="true"></div>
<div class="snowfall" aria-hidden="true" x-data="{
        init() {
            const rnd = (a, b) => a + Math.random() * (b - a);
            const layers = [
                { cls: 'is-far',  size: [7, 12],  dur: [24, 36], op: [0.4, 0.6] },
                { cls: 'is-mid',  size: [12, 19], dur: [17, 26], op: [0.65, 0.85] },
                { cls: 'is-near', size: [19, 30], dur: [13, 19], op: [0.85, 1] },
            ];
            const shuffle = (el, layer, first) => {
                el.style.setProperty('--x', rnd(-2, 102).toFixed(1) + 'vw');
                el.style.setProperty('--s', rnd(...layer.size).toFixed(1) + 'px');
                el.style.setProperty('--d', rnd(...layer.dur).toFixed(1) + 's');
                el.style.setProperty('--drift', rnd(-22, 22).toFixed(0) + 'px');
                el.style.setProperty('--o', rnd(...layer.op).toFixed(2));
                el.style.setProperty('--sway', rnd(4, 8).toFixed(1) + 's');
                el.style.setProperty('--spin', rnd(25, 50).toFixed(1) + 's');
                if (first) el.style.setProperty('--delay', rnd(0, 12).toFixed(1) + 's');
            };
            const count = window.innerWidth < 640 ? 9 : 16;
            for (let i = 0; i < count; i++) {
                const layer = layers[Math.floor(Math.random() * 3)];
                const el = document.createElement('span');
                el.className = layer.cls;
                shuffle(el, layer, true);
                el.addEventListener('animationiteration', (e) => {
                    if (e.animationName === 'snow-fall') shuffle(el, layer, false);
                });
                this.$el.appendChild(el);
            }
        }
    }"></div>
