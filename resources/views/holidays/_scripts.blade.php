<script @cspNonce>
(function () {
    document.querySelectorAll('[data-counter-for]').forEach(function (counter) {
        const area = document.getElementById(counter.dataset.counterFor);
        if (!area) return;

        function update() {
            counter.textContent = area.value.length;
            counter.classList.toggle('text-danger', area.value.length > 140);
        }

        update();
        area.addEventListener('input', update);
    });

    document.querySelectorAll('.placeholder-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ta = document.getElementById(btn.dataset.target);
            if (!ta) return;

            const ph = btn.dataset.placeholder;
            const s  = ta.selectionStart;
            ta.value = ta.value.substring(0, s) + ph + ta.value.substring(ta.selectionEnd);
            ta.selectionStart = ta.selectionEnd = s + ph.length;
            ta.focus();
            ta.dispatchEvent(new Event('input'));
        });
    });
})();
</script>
