@props(['desktop', 'mobile' => null, 'alt' => ''])
{{-- صورة هيرو متجاوبة: صورة مخصصة للجوال وأخرى للديسكتوب --}}
<picture>
    @if($mobile && $mobile !== $desktop)
        <source media="(max-width: 639px)" srcset="{{ $mobile }}">
    @endif
    <img src="{{ $desktop }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}>
</picture>
