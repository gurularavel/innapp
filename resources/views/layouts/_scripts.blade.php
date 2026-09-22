{{--
    Shared behaviour every layout needs, in one nonce-marked block.

    These replace the `onclick="return confirm(…)"` style attributes the views
    used to carry: an inline handler is script inside an attribute, which no
    nonce can vouch for, so the Content Security Policy blocks it. Markup now
    declares intent with a data attribute and the listeners live here.
--}}
<script @cspNonce>
// Escape a value before it goes into innerHTML — patient names, phones and
// anything else that came from a form must never be rendered as markup.
window.escHtml = function (value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
};

// Report policy violations ourselves. Chrome's own report-uri/report-to
// delivery is batched and frequently never arrives, which would leave the
// "report only" mode blind — the mode exists so a broken policy can be
// diagnosed, so the page sends the report itself. Capped per page load.
(function () {
    let left = 5;

    document.addEventListener('securitypolicyviolation', function (e) {
        if (left-- <= 0) {
            return;
        }

        try {
            fetch(@json(\App\Support\Csp::REPORT_PATH), {
                method: 'POST',
                headers: { 'Content-Type': 'application/csp-report' },
                keepalive: true,
                body: JSON.stringify({
                    'csp-report': {
                        'document-uri':       e.documentURI,
                        'violated-directive': e.violatedDirective,
                        'effective-directive': e.effectiveDirective,
                        'blocked-uri':        e.blockedURI,
                        'script-sample':      e.sample,
                        'line-number':        e.lineNumber,
                    },
                }),
            }).catch(function () {});
        } catch (err) {
            /* reporting must never break the page */
        }
    });
})();

document.addEventListener('DOMContentLoaded', function () {
    // <button data-confirm="…">  /  <form data-confirm="…">
    // Asking on click keeps the old behaviour for buttons that sit in a form
    // with other submit buttons; forms are also caught on submit, so a keyboard
    // submit cannot slip past the question.
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-confirm]');

        if (!trigger || trigger.tagName === 'FORM') {
            return;
        }

        if (!window.confirm(trigger.dataset.confirm)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    document.addEventListener('submit', function (e) {
        const form = e.target.closest('form[data-confirm]');

        if (form && !window.confirm(form.dataset.confirm)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    // <select data-auto-submit> — filter dropdowns that post their own form.
    document.addEventListener('change', function (e) {
        const el = e.target.closest('[data-auto-submit]');

        if (el && el.form) {
            el.form.submit();
        }
    });

    // <a data-open-blank="url"> / <button data-open-blank="url">
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-open-blank]');

        if (el) {
            window.open(el.dataset.openBlank, '_blank', 'noopener');
        }
    });

    // <a data-submit-parent> — a link that posts the form around it, e.g. logout.
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-submit-parent]');

        if (el && el.closest('form')) {
            e.preventDefault();
            el.closest('form').submit();
        }
    });

    // <el data-click-target="#selector"> — forwards a click to another element,
    // e.g. a styled tile that opens a hidden file input.
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-click-target]');

        if (!el || e.target.closest(el.dataset.clickTarget)) {
            return;
        }

        const target = document.querySelector(el.dataset.clickTarget);

        if (target) {
            target.click();
        }
    });
});
</script>
