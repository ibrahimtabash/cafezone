import {
    WebGLRenderer, Scene, PerspectiveCamera, Group, Mesh, SphereGeometry, CylinderGeometry, ConeGeometry,
    TorusGeometry, BoxGeometry, CircleGeometry, MeshStandardMaterial, MeshBasicMaterial, HemisphereLight,
    DirectionalLight, CanvasTexture, SRGBColorSpace, CapsuleGeometry, MathUtils,
} from 'three';

const std = (color, rough = 0.7, extra = {}) => new MeshStandardMaterial({ color, roughness: rough, metalness: 0, ...extra });

function shadowTexture() {
    const c = document.createElement('canvas');
    c.width = c.height = 128;
    const g = c.getContext('2d');
    const grd = g.createRadialGradient(64, 64, 4, 64, 64, 62);
    grd.addColorStop(0, 'rgba(42,15,20,.5)');
    grd.addColorStop(1, 'rgba(42,15,20,0)');
    g.fillStyle = grd;
    g.fillRect(0, 0, 128, 128);
    const t = new CanvasTexture(c);
    t.colorSpace = SRGBColorSpace;
    return t;
}

function build() {
    const root = new Group();
    const body = new Group();
    root.add(body);

    const snow = std('#f6f9ff', 0.62, { emissive: '#9db4d8', emissiveIntensity: 0.06 });
    const ball = (r, y) => {
        const m = new Mesh(new SphereGeometry(r, 48, 32), snow);
        m.position.y = y;
        return m;
    };
    body.add(ball(1, 1), ball(0.76, 2.18));

    const head = new Group();
    head.position.y = 3.2;
    head.add(new Mesh(new SphereGeometry(0.58, 48, 32), snow));
    body.add(head);

    // العيون
    const eyeMat = std('#140a0c', 0.25);
    const eyes = [-1, 1].map((s) => {
        const e = new Mesh(new SphereGeometry(0.075, 20, 16), eyeMat);
        e.scale.set(1, 1.25, 0.6);
        e.position.set(0.2 * s, 0.1, 0.52);
        const shine = new Mesh(new SphereGeometry(0.022, 8, 8), new MeshBasicMaterial({ color: '#ffffff' }));
        shine.position.set(-0.02, 0.04, 0.06);
        e.add(shine);
        head.add(e);
        return e;
    });

    // الخدود
    [-1, 1].forEach((s) => {
        const c = new Mesh(new SphereGeometry(0.09, 16, 12), std('#ff9aa8', 0.9, { emissive: '#ff6a80', emissiveIntensity: 0.12 }));
        c.scale.set(1.2, 0.8, 0.4);
        c.position.set(0.34 * s, -0.08, 0.45);
        head.add(c);
    });

    // الأنف
    const nose = new Mesh(new ConeGeometry(0.09, 0.5, 20), std('#ff8a1f', 0.5));
    nose.rotation.x = Math.PI / 2;
    nose.position.set(0, -0.02, 0.7);
    head.add(nose);

    // الفم (أحجار صغيرة بشكل ابتسامة)
    const mouthMat = std('#140a0c', 0.3);
    for (let i = -2; i <= 2; i++) {
        const m = new Mesh(new SphereGeometry(0.036, 10, 8), mouthMat);
        m.position.set(i * 0.1, -0.24 + i * i * 0.022, 0.5 - Math.abs(i) * 0.03);
        head.add(m);
    }

    // القبعة
    const hat = new Group();
    hat.position.y = 0.46;
    const hatMat = std('#241417', 0.45);
    const brim = new Mesh(new CylinderGeometry(0.62, 0.62, 0.06, 40), hatMat);
    const top = new Mesh(new CylinderGeometry(0.38, 0.4, 0.62, 40), hatMat);
    top.position.y = 0.34;
    const band = new Mesh(new CylinderGeometry(0.41, 0.41, 0.14, 40), std('#c8312f', 0.5));
    band.position.y = 0.1;
    hat.add(brim, top, band);
    head.add(hat);

    // الشال
    const scarfMat = std('#c8312f', 0.8);
    const scarf = new Mesh(new TorusGeometry(0.5, 0.15, 20, 48), scarfMat);
    scarf.rotation.x = Math.PI / 2 + 0.12;
    scarf.position.y = 2.66;
    const tail = new Mesh(new BoxGeometry(0.26, 0.8, 0.1), scarfMat);
    tail.position.set(0.3, 2.2, 0.62);
    tail.rotation.z = 0.12;
    const stripe = new Mesh(new BoxGeometry(0.27, 0.09, 0.11), std('#f6e6d4', 0.8));
    stripe.position.set(0.3, 2.08, 0.62);
    stripe.rotation.z = 0.12;
    body.add(scarf, tail, stripe);

    // الأزرار على الكرة السفلية
    const btnMat = std('#4a1520', 0.35);
    [0.62, 1.05, 1.48].forEach((y) => {
        const b = new Mesh(new SphereGeometry(0.1, 16, 12), btnMat);
        b.position.set(0, y, Math.sqrt(Math.max(1 - Math.pow(y - 1, 2), 0)) * 0.99);
        body.add(b);
    });

    // الأيدي
    const armMat = std('#5a3320', 0.8);
    const arms = [-1, 1].map((s) => {
        const a = new Group();
        a.position.set(0.72 * s, 2.3, 0);
        const stick = new Mesh(new CapsuleGeometry(0.045, 1.0, 6, 10), armMat);
        stick.rotation.z = s * -1.05;
        stick.position.set(0.45 * s, 0.25, 0);
        const f1 = new Mesh(new CapsuleGeometry(0.03, 0.28, 4, 8), armMat);
        f1.position.set(0.95 * s, 0.62, 0);
        f1.rotation.z = s * -0.4;
        const f2 = f1.clone();
        f2.position.set(0.92 * s, 0.4, 0);
        f2.rotation.z = s * -1.7;
        a.add(stick, f1, f2);
        body.add(a);
        return a;
    });

    // الظل على الأرض
    const shadow = new Mesh(new CircleGeometry(1.7, 40), new MeshBasicMaterial({ map: shadowTexture(), transparent: true, depthWrite: false }));
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = 0.01;
    root.add(shadow);

    return { root, body, head, hat, eyes, arms, shadow };
}

export function mountSnowman(host) {
    const canvas = document.createElement('canvas');
    canvas.className = 'snowman__canvas';
    let renderer;

    try {
        renderer = new WebGLRenderer({ canvas, alpha: true, antialias: true });
    } catch (e) {
        return null;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    host.appendChild(canvas);

    const scene = new Scene();
    const camera = new PerspectiveCamera(28, 1, 0.1, 50);
    camera.position.set(0, 2.4, 12.5);
    camera.lookAt(0, 2.1, 0);

    scene.add(new HemisphereLight('#ffffff', '#c9a9a0', 1.25));
    const key = new DirectionalLight('#fff3e2', 2.2);
    key.position.set(-3, 6, 6);
    scene.add(key);
    const rim = new DirectionalLight('#9ec5ff', 0.9);
    rim.position.set(4, 3, -4);
    scene.add(rim);

    const sn = build();
    scene.add(sn.root);

    const resize = () => {
        const w = host.clientWidth || 100;
        const h = host.clientHeight || 140;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    resize();
    window.addEventListener('resize', resize);

    const look = { x: 0, y: 0, tx: 0, ty: 0 };
    window.addEventListener('pointermove', (e) => {
        const r = host.getBoundingClientRect();
        look.tx = MathUtils.clamp((e.clientX - (r.left + r.width / 2)) / (window.innerWidth * 0.5), -1, 1);
        look.ty = MathUtils.clamp((e.clientY - (r.top + r.height * 0.3)) / (window.innerHeight * 0.5), -1, 1);
    }, { passive: true });

    let jump = -1;
    let hatPop = -1;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const start = performance.now();
    let last = start;
    let raf;

    const frame = (now) => {
        raf = requestAnimationFrame(frame);
        if (document.hidden) return;
        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;
        const t = (now - start) / 1000;

        look.x += (look.tx - look.x) * Math.min(dt * 6, 1);
        look.y += (look.ty - look.y) * Math.min(dt * 6, 1);

        // تنفّس وتمايل خفيف
        const breathe = reduced ? 0 : Math.sin(t * 2) * 0.012;
        sn.body.scale.set(1 + breathe, 1 - breathe, 1 + breathe);
        sn.body.rotation.z = reduced ? 0 : Math.sin(t * 1.3) * 0.02;
        sn.root.rotation.y = look.x * 0.55;
        sn.head.rotation.y = look.x * 0.5;
        sn.head.rotation.x = look.y * 0.3;

        // رمش
        const blink = Math.sin(t * 1.1) > 0.985 ? 0.1 : 1;
        sn.eyes.forEach((e) => { e.scale.y = 1.25 * blink; });

        // تلويح خفيف باليد
        if (jump < 0) {
            sn.arms[1].rotation.z = reduced ? 0 : Math.sin(t * 2.4) * 0.07;
            sn.arms[0].rotation.z = 0;
        }

        // قفزة عند الضغط
        if (jump >= 0) {
            const k = (now - jump) / 1000;
            if (k > 1.1) {
                jump = -1;
                sn.root.position.y = 0;
                sn.root.scale.set(1, 1, 1);
            } else {
                const y = Math.max(0, Math.sin(Math.min(k / 0.55, 1) * Math.PI)) * 0.9;
                const squash = k < 0.12 ? 1 - k * 1.2 : k < 0.55 ? 1.06 : 1 - Math.sin(Math.min((k - 0.55) / 0.25, 1) * Math.PI) * 0.12;
                sn.root.position.y = y;
                sn.root.scale.set(1 / Math.sqrt(squash), squash, 1 / Math.sqrt(squash));
                sn.root.rotation.y += Math.sin(Math.min(k / 0.8, 1) * Math.PI * 2) * 0.35 * (1 - k);
                sn.arms[0].rotation.z = Math.sin(k * 18) * 0.5 * (1 - k);
                sn.arms[1].rotation.z = -Math.sin(k * 18) * 0.5 * (1 - k);
            }
        }

        if (hatPop >= 0) {
            const k = (now - hatPop) / 1000;
            if (k > 0.9) {
                hatPop = -1;
                sn.hat.position.set(0, 0.46, 0);
                sn.hat.rotation.set(0, 0, 0);
            } else {
                sn.hat.position.y = 0.46 + Math.max(0, Math.sin(Math.min(k / 0.9, 1) * Math.PI)) * 0.9;
                sn.hat.rotation.z = Math.sin(k * 14) * 0.35 * (1 - k);
                sn.hat.rotation.x = Math.sin(k * 9) * 0.2 * (1 - k);
            }
        }

        sn.shadow.scale.setScalar(1 - Math.min(sn.root.position.y, 1) * 0.35);

        renderer.render(scene, camera);
    };
    raf = requestAnimationFrame(frame);

    return {
        poke() {
            jump = performance.now();
            hatPop = jump;
        },
        destroy() {
            cancelAnimationFrame(raf);
            renderer.dispose();
        },
    };
}
