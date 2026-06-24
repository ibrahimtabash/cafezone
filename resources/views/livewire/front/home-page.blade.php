<main class="flex-1">
    <div class="overflow-hidden">
        <section class="relative isolate">
            <div class="absolute inset-0 -z-10"><img alt="" class="h-full w-full object-cover opacity-40"
                    src="{{ asset('assets/images/hero.webp') }}">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,transparent,oklch(0.18_0.03_40)_75%)]">
                </div>
            </div>
            <div class="mx-auto max-w-6xl px-4 py-20 sm:py-28 text-center text-cream">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-cream/30 bg-background/10 px-4 py-1.5 text-xs tracking-[0.3em] backdrop-blur">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-coffee h-3.5 w-3.5" aria-hidden="true">
                        <path d="M10 2v2"></path>
                        <path d="M14 2v2"></path>
                        <path
                            d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1">
                        </path>
                        <path d="M6 2v2"></path>
                    </svg>COFFEE &amp; EATERY
                </div>
                <h1 class="mt-6 font-serif text-4xl sm:text-6xl font-bold leading-[1.15]"><span
                        class="block text-gold tracking-wide">Zone</span><span class="block mt-2">حيث تمتزج
                        القهوة بالثقافة</span></h1>
                <p class="mx-auto mt-5 max-w-xl text-base sm:text-lg text-cream/80">نكهة تُطهى وتُحكى كرواية
                    تلامس القلب. تصفّح أقسامنا وأضف ما يحلو لك إلى السلة.</p>
                <div class="mt-8 flex justify-center gap-3">
                    <a href="#menu" class="rounded-full btn-hero px-6 py-3 font-semibold">استكشف القائمة</a>
                </div>
            </div>
        </section>
        <section id="menu" class="mx-auto max-w-6xl px-4 py-16">
            <div class="mb-10 text-center">
                <p class="text-xs tracking-[0.3em] text-gold">MENU</p>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl text-primary">أقسام القائمة</h2>
                <div class="mx-auto mt-3 h-1 w-24 tatreez-border"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($categories as $category)
                    <a href="{{ route('category.show', $category->slug) }}"
                        class="group relative overflow-hidden rounded-3xl bg-card shadow-[var(--shadow-soft)] hover:shadow-[var(--shadow-elegant)] transition-all duration-500 hover:-translate-y-1">

                        <div class="relative aspect-[5/4] overflow-hidden">

                            <img src="{{ Storage::url($category->image) }}"
                                alt="{{ $category->name }}"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">

                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent">
                            </div>

                            <span
                                class="absolute top-4 right-4 rounded-full px-3 py-1 text-xs font-semibold bg-primary text-white">
                                {{ $category->menu_items_count }} صنف
                            </span>
                        </div>

                        <div class="p-5">
                            <h3 class="font-serif text-2xl text-primary">
                                {{ $category->name }}
                            </h3>

                            <p class="mt-1 text-sm text-muted-foreground">
                                استكشف منتجات هذا القسم
                            </p>

                            <div class="mt-4 flex items-center justify-between text-sm font-semibold text-primary">
                                <span>اطلب الآن</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</main>
