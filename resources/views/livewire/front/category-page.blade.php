<main class="flex-1" x-data="{ imageOpen: false, imageUrl: '', imageName: '' }"
    x-on:keydown.escape.window="imageOpen = false">
    <div>
        <section class="relative h-72 overflow-hidden">
            <img alt="{{ $category->name }}"
                class="absolute inset-0 h-full w-full object-cover"
                src="{{ Storage::url($category->image) }}">
            <div class="absolute inset-0 bg-gradient-to-t from-background via-background/70 to-transparent">
            </div>
            <div class="absolute inset-0 flex flex-col items-center justify-end pb-8 text-center">
                <a href="/"
                    class="mb-3 inline-flex items-center gap-1 rounded-full bg-background/80 px-3 py-1 text-xs text-foreground backdrop-blur">
                    <svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-arrow-right h-3.5 w-3.5" aria-hidden="true">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg> كل الأقسام</a>
                <h1 class="font-serif text-4xl sm:text-5xl text-primary drop-shadow">{{ $category->name }}</h1>
                <p class="mt-2 max-w-md px-4 text-sm text-foreground/80">{{ $category->description }}</p>
            </div>
        </section>
        <section class="mx-auto max-w-4xl px-4 py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($items as $item)

                <div
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card transition hover:border-primary/40 hover:shadow-[var(--shadow-soft)]">
                    <div class="relative h-44 overflow-hidden">
                        <img alt="{{ $item->name }}" loading="lazy"
                            class="h-full w-full cursor-zoom-in object-cover transition duration-500 group-hover:scale-105"
                            src="{{ Storage::url($item->image) }}"
                            x-on:click="imageUrl = @js(Storage::url($item->image)); imageName = @js($item->name); imageOpen = true">
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-card/80 via-transparent to-transparent">
                        </div>
                        <button type="button"
                            x-on:click="imageUrl = @js(Storage::url($item->image)); imageName = @js($item->name); imageOpen = true"
                            class="absolute top-3 right-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/30 bg-black/55 text-white shadow-lg backdrop-blur transition hover:scale-105 hover:bg-primary focus:outline-none focus:ring-2 focus:ring-white"
                            aria-label="تكبير صورة {{ $item->name }}" title="تكبير الصورة">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                                <path d="M11 8v6"></path>
                                <path d="M8 11h6"></path>
                            </svg>
                        </button>
                        <div class="absolute bottom-2 left-2 rounded-full bg-background/85 px-3 py-1 backdrop-blur">
                            <span class="font-serif text-base text-primary">{{ $item->price }}</span><span
                                class="ms-1 text-[10px] text-muted-foreground">₪</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <h3 class="font-serif text-lg text-foreground">{{ $item->name }}</h3>
                            </div>
                        </div>
                        <button
                            wire:click="addToCart({{ $item->id }})"
                            class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition bg-primary text-primary-foreground hover:opacity-90">
                            <svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-plus h-4 w-4" aria-hidden="true">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg> أضف للسلة</button>
                    </div>
                </div>
                @endforeach

            </div>
        </section>
    </div>

    <div x-cloak x-show="imageOpen" x-transition.opacity
        x-on:click.self="imageOpen = false"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 p-4 sm:p-8"
        role="dialog" aria-modal="true" x-bind:aria-label="'صورة ' + imageName">
        <button type="button" x-on:click="imageOpen = false"
            class="absolute top-4 right-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-2xl text-white backdrop-blur transition hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white"
            aria-label="إغلاق الصورة">✕</button>

        <div x-show="imageOpen" x-transition.scale.origin.center class="flex max-h-full max-w-5xl flex-col items-center gap-3">
            <img x-bind:src="imageUrl" x-bind:alt="imageName"
                class="max-h-[82vh] max-w-full rounded-2xl object-contain shadow-2xl">
            <div x-text="imageName" class="rounded-full bg-black/50 px-5 py-2 text-sm font-semibold text-white"></div>
        </div>
    </div>
</main>
