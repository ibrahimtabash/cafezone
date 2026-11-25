{{-- ثلج متساقط خفيف (CSS فقط) --}}
<div class="snowfall" aria-hidden="true">
    @for ($i = 0; $i < 28; $i++)
        <span style="--x: {{ ($i * 37 + 11) % 100 }}vw; --s: {{ 4 + ($i * 7) % 7 }}px; --d: {{ 9 + ($i * 5) % 11 }}s; --delay: -{{ ($i * 3) % 14 }}s; --drift: {{ (($i % 5) - 2) * 18 }}px; --o: {{ 0.45 + (($i * 13) % 5) / 10 }};"></span>
    @endfor
</div>
