<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- <link rel="stylesheet" href="/assets/styles-Dn-bKRFa.css" data-precedence="default"> --}}
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&amp;family=Playfair+Display:wght@600;700;800&amp;display=swap"
        data-precedence="default">
    <meta name="author" content="Zone Coffee">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Zone Coffee &amp; Eatery — منيو وطلبات">
    <meta name="twitter:description"
        content="منيو كافي زون — قهوة مختصة، بيتزا، برجر، حلويات وعصائر. اطلب أونلاين عبر واتساب.">
    {{-- <meta name="twitter:image"
        content="https://pub-bb2e103a32db4e198524a2e9ed8f35b4.r2.dev/2a95fb28-ca5f-4b4d-8c9a-bf4c0e274770/id-preview-94f3ef8d--0ed84ee0-22b1-42ee-a884-d729efc17592.lovable.app-1782114812929.png"> --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">

    <style>
        @font-face {
            font-family: 'CameraPlainVariable';
            src: url('https://cdn.gpteng.co/mcp-widgets/v1/fonts/CameraPlainVariable.woff2') format('woff2');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta property="og:image" content="">
    <title>Zone Coffee &amp; Eatery — منيو الكافي</title>
    <meta name="description"
        content="تصفّح قائمة كافي زون: قهوة مختصة، بيتزا، برجر، عصائر وحلويات. اطلب أونلاين عبر واتساب.">
    <meta property="og:title" content="Zone Coffee &amp; Eatery">
    <meta property="og:description" content="تصفّح قائمة كافي زون واطلب أونلاين.">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/logo.jpeg') }}" >
</head>

<body>


    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-40 border-b border-border/60 bg-background/85 backdrop-blur-md">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <a href="/" class="flex items-center gap-2 group active" data-status="active" aria-current="page">
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-full text-primary-foreground transition-transform group-hover:rotate-12">

                        <img src="{{ asset('assets/images/logo2026.png') }}" alt="logo">
                    </span>
                    <div class="leading-tight">
                        <div class="font-serif text-lg text-primary">Zone</div>
                        <div class="text-[10px] tracking-[0.3em] text-muted-foreground">COFFEE &amp; EATERY</div>
                    </div>
                </a>
                <nav class="flex items-center gap-1 sm:gap-3 text-sm">
                    <a href="/" class="rounded-full px-3 py-2 hover:bg-secondary transition active"
                        data-status="active" aria-current="page">القائمة</a>

                    <livewire:front.cart-counter />

                </nav>
            </div>
        </header>

        @php($orderContext = session('order_context', ['type' => 'delivery']))
        <div class="border-b border-primary/10 bg-primary/5 px-4 py-2 text-center text-xs font-semibold text-primary">
            @if(($orderContext['type'] ?? 'delivery') === 'dine_in')
                طلبك مسجّل على {{ $orderContext['table_name'] ?? 'الطاولة' }}
            @elseif(($orderContext['type'] ?? 'delivery') === 'takeaway')
                طلب خارجي للاستلام من الكافي
            @else
                طلب توصيل خارجي
            @endif
        </div>

        {{ $slot }}

        <footer class="border-t border-border/60 py-8 text-center text-sm text-muted-foreground">
            <div class="font-serif text-primary text-lg">Zone Coffee &amp; Eatery</div>
            <p class="mt-1">حيث تمتزج القهوة بالثقافة، والنكهة تُحكى كرواية تلامس القلب.</p>
        </footer>
    </div>

    <div x-data="{ show: false, message: '', timer: null }"
        x-on:toast.window="
            clearTimeout(timer);
            show = false;
            $nextTick(() => {
                message = $event.detail.message;
                show = true;
                timer = setTimeout(() => show = false, 3200);
            });
        "
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-5 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
        class="fixed bottom-5 left-1/2 z-50 w-[calc(100%_-_2rem)] max-w-sm -translate-x-1/2 overflow-hidden rounded-2xl border border-gold/50 bg-card shadow-[0_18px_50px_rgba(73,32,24,.28)] sm:left-5 sm:translate-x-0">
        <div class="flex items-center gap-3 p-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                    <path d="M3 6h18" /><path d="M16 10a4 4 0 0 1-8 0" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold text-primary">تمت الإضافة للسلة</div>
                <div class="mt-0.5 truncate text-xs text-muted-foreground" x-text="message"></div>
            </div>
            <button type="button" x-on:click="show = false; clearTimeout(timer)"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-muted-foreground transition hover:bg-secondary hover:text-primary"
                aria-label="إغلاق الإشعار">✕</button>
        </div>
        <div class="h-1 w-full bg-secondary">
            <div class="cart-toast-progress h-full bg-gradient-to-l from-primary via-gold to-primary"></div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-whatsapp', (event) => {
                window.open(event.url, '_blank');
            });
        });
    </script>
</body>

</html>
