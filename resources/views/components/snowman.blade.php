{{-- رجل الثلج ثلاثي الأبعاد (three.js) واقف على كومة ثلج تفاعلية --}}
<div class="snowman" x-data="{
        msgs: ['برد برد! ثبّت نفسك بكوب شوكولاتة ساخنة ☕', 'هل جرّبت السحلب الساخن اليوم؟', 'الشتاء أحلى مع قهوة زون ❄', 'لا تخرج بدون طاقية! 🧣', 'أنا ما بذوب هون.. عندهم تدفئة 😄', 'اطلب مشروبك الساخن قبل ما يبرد الجو!'],
        msg: '', open: false, timer: null, ctl: null, ok: true, squish: false, sTimer: null,
        async init() {
            this.ctl = await window.initSnowman(this.$refs.host);
            if (!this.ctl) this.ok = false;
        },
        puff(count = 12) {
            const r = this.$refs.mound.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height * 0.35;
            for (let i = 0; i < count; i++) {
                const p = document.createElement('span');
                p.className = 'snowman__puff';
                const size = 4 + Math.random() * 7;
                p.style.width = p.style.height = size + 'px';
                p.style.left = (cx + (Math.random() - .5) * r.width * .7) + 'px';
                p.style.top = cy + 'px';
                p.style.setProperty('--dx', ((Math.random() - .5) * 150) + 'px');
                p.style.setProperty('--dy', (-30 - Math.random() * 70) + 'px');
                document.body.appendChild(p);
                setTimeout(() => p.remove(), 1100);
            }
            this.squish = false;
            this.$nextTick(() => { this.squish = true; });
            clearTimeout(this.sTimer);
            this.sTimer = setTimeout(() => { this.squish = false; }, 500);
        },
        poke(e) {
            this.ctl?.poke();
            this.puff(10);
            this.msg = this.msgs[Math.floor(Math.random() * this.msgs.length)];
            this.open = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.open = false; }, 3500);
            const r = e.currentTarget.getBoundingClientRect();
            for (let i = 0; i < 14; i++) {
                const f = document.createElement('span');
                f.className = 'snowman__flake';
                f.textContent = i % 3 ? '❄' : '❅';
                f.style.left = (r.left + r.width / 2) + 'px';
                f.style.top = (r.top + 30) + 'px';
                f.style.setProperty('--dx', ((Math.random() - .5) * 220) + 'px');
                f.style.setProperty('--dy', (-40 - Math.random() * 160) + 'px');
                document.body.appendChild(f);
                setTimeout(() => f.remove(), 1500);
            }
        }
    }" x-show="ok">
    <div x-show="open" x-cloak x-transition.opacity class="snowman__bubble" role="status" x-text="msg"></div>
    <button type="button" class="snowman__btn" x-on:click="poke($event)" aria-label="رجل الثلج، اضغط عليه">
        <span class="snowman__host" x-ref="host"></span>
    </button>
    {{-- كومة الثلج: اضغط عليها لتتطاير الثلجات وتهتز --}}
    <button type="button" class="snowman__mound" :class="{ 'is-squish': squish }" x-ref="mound" x-on:click="puff(16)" aria-label="كومة ثلج، العب بها">
        <svg viewBox="0 0 200 54" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="mound-g" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#ffffff"/><stop offset=".7" stop-color="#e6f1ff"/><stop offset="1" stop-color="#c3d9f2"/>
                </linearGradient>
            </defs>
            <path d="M0 54V36C14 30 22 22 40 22C54 22 60 12 82 10C100 8 112 8 126 14C140 20 150 18 164 24C180 30 192 32 200 38V54Z" fill="url(#mound-g)"/>
            <path d="M18 36C34 30 44 26 62 28M96 18C110 15 124 18 136 24M150 32C166 30 178 36 190 40" stroke="#ffffff" stroke-width="3" stroke-linecap="round" fill="none" opacity=".9"/>
            <circle cx="52" cy="40" r="1.8" fill="#bcd6f5"/><circle cx="104" cy="34" r="1.8" fill="#bcd6f5"/><circle cx="150" cy="42" r="1.8" fill="#bcd6f5"/>
        </svg>
    </button>
</div>
