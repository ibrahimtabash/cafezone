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

        {{ $slot }}

        <footer class="border-t border-border/60 py-8 text-center text-sm text-muted-foreground">
            <div class="font-serif text-primary text-lg">Zone Coffee &amp; Eatery</div>
            <p class="mt-1">حيث تمتزج القهوة بالثقافة، والنكهة تُحكى كرواية تلامس القلب.</p>
        </footer>
    </div>

    <div x-data="{ show: false, message: '' }"
        x-on:toast.window="
        message = $event.detail.message;
        show = true;
        setTimeout(() => show = false, 2500);
    "
        x-show="show" x-transition
        class="fixed bottom-5 left-5 bg-primary text-white px-4 py-2 rounded-xl shadow-lg z-50">
        <span x-text="message"></span>
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
