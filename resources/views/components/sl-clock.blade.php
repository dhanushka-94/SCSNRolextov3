<span {{ $attributes->merge(['class' => 'tabular-nums']) }} data-sl-clock>{{ sl_datetime(now()) }}</span>

@once
    <script>
        (function () {
            var formatter = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Colombo',
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
            });

            function tick() {
                var text = formatter.format(new Date()) + ' SLST';
                document.querySelectorAll('[data-sl-clock]').forEach(function (el) { el.textContent = text; });
            }

            tick();
            setInterval(tick, 15000);
        })();
    </script>
@endonce
