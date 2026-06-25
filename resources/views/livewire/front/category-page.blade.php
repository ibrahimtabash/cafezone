<main class="flex-1">
    <div>
        <section class="relative h-72 overflow-hidden"><img alt="المشروبات الساخنة"
                class="absolute inset-0 h-full w-full object-cover"
                src="{{ Storage::url($category->image) }}">
            <div class="absolute inset-0 bg-gradient-to-t from-background via-background/70 to-transparent">
            </div>
            <div class="absolute inset-0 flex flex-col items-center justify-end pb-8 text-center"><a href="/"
                    class="mb-3 inline-flex items-center gap-1 rounded-full bg-background/80 px-3 py-1 text-xs text-foreground backdrop-blur"><svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-arrow-right h-3.5 w-3.5" aria-hidden="true">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg> كل الأقسام</a>
                <h1 class="font-serif text-4xl sm:text-5xl text-primary drop-shadow">{{ $category->name }}</h1>
                <p class="mt-2 max-w-md px-4 text-sm text-foreground/80">قهوة محمّصة بحب على الطريقة العربية
                    والإيطالية</p>
            </div>
        </section>
        <section class="mx-auto max-w-4xl px-4 py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($items as $item)

                <div
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card transition hover:border-primary/40 hover:shadow-[var(--shadow-soft)]">
                    <div class="relative h-44 overflow-hidden">
                        <img alt="{{ $item->name }}" loading="lazy"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            src="{{ Storage::url($item->image) }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-card/80 via-transparent to-transparent">
                        </div>
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
</main>
