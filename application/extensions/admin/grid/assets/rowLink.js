LS.rowlink = {
    create: function () {
        'use strict';

        // Make disabled links non-focusable and non-interactive
        document.querySelectorAll('.grid-view-ls a.disabled').forEach(link => {
            link.setAttribute('aria-disabled', 'true');
            link.setAttribute('tabindex', '-1');
            link.removeAttribute('href');

            link.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Row click support
        document.querySelectorAll('.grid-view-ls [data-rowlink]').forEach(tr => {
            const link = tr.getAttribute('data-rowlink');

            // No tabindex on <tr>

            tr.querySelectorAll('td:not(.ls-sticky-column)').forEach(td => {
                td.addEventListener('click', function (e) {

                    // Ignore clicks on interactive elements
                    if (e.target.closest('a, button, input, select, textarea')) {
                        return;
                    }

                    window.location.href = link;
                });
            });
        });
    }
};

LS.rowlink.create();