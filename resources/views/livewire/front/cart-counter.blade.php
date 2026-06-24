<a href="{{ route('cart.index') }}"
    class="relative inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-primary-foreground btn-hero">

    <svg xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2">
        <path d="M16 10a4 4 0 0 1-8 0"></path>
        <path d="M3.103 6.034h17.794"></path>
        <path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"></path>
    </svg>

    <span>السلة</span>

    @if($count > 0)
        <span
            class="absolute -top-1 -left-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gold px-1.5 text-[11px] font-semibold text-foreground">
            {{ $count }}
        </span>
    @endif

</a>
