<main class="flex-1">
    <div class="overflow-hidden">
        <section class="relative isolate">
            <div class="absolute inset-0 -z-10">
                <x-hero-picture :desktop="$hero[0]" :mobile="$hero[1]" aria-hidden="true" />
                <div class="absolute inset-0 bg-gradient-to-b from-[#0b1f3f]/70 via-[#0b1f3f]/55 to-[#0b1f3f]/80"></div>
            </div>
            <div class="mx-auto max-w-6xl px-4 py-20 sm:py-28 text-center text-cream">
                <div class="inline-flex items-center gap-2 rounded-full border border-cream/30 bg-background/10 px-4 py-1.5 text-xs tracking-[0.3em] backdrop-blur">
                    ❄ WINTER COLLECTION
                </div>
                <h1 class="mt-6 font-serif text-4xl sm:text-6xl font-bold leading-[1.15]">
                    <span class="block text-sky-200 tracking-wide">Winter</span>
                    <span class="block mt-2">المنتجات الشتوية</span>
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-base sm:text-lg text-cream/85">مشروبات ساخنة وحلويات ووجبات تدفّيك في أيام البرد. اختر القسم لتصفّح منتجاته.</p>
                <div class="mt-8 flex justify-center gap-3">
                    <a href="#winter-menu" class="rounded-full btn-hero px-6 py-3 font-semibold">استكشف الأقسام</a>
                </div>
            </div>
        </section>
        <section id="winter-menu" class="mx-auto max-w-6xl px-4 py-16">
            <div class="mb-10 text-center">
                <p class="text-xs tracking-[0.3em] text-gold">WINTER MENU</p>
                <h2 class="mt-2 font-serif text-3xl sm:text-4xl text-primary">أقسام الشتاء</h2>
                <div class="mx-auto mt-3 h-1 w-24 tatreez-border"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($categories as $category)
                    <a href="{{ route('category.show', $category->slug) }}"
                        class="group relative overflow-hidden rounded-3xl bg-card shadow-[var(--shadow-soft)] hover:shadow-[var(--shadow-elegant)] transition-all duration-500 hover:-translate-y-1">
                        <div class="relative aspect-[5/4] overflow-hidden">
                            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            <span class="absolute top-4 right-4 rounded-full px-3 py-1 text-xs font-semibold bg-primary text-white">
                                {{ $category->menu_items_count }} صنف
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-serif text-2xl text-primary">{{ $category->name }}</h3>
                            <p class="mt-1 text-sm text-muted-foreground line-clamp-1">{{ $category->description }}</p>
                            <div class="mt-4 flex items-center justify-between text-sm font-semibold text-primary">
                                <span>اطلب الآن</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full py-16 text-center text-muted-foreground">لا توجد أقسام شتوية حالياً.</p>
                @endforelse
            </div>
        </section>
    </div>
</main>
