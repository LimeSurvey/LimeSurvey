LS.rowlink = {
    create: function () {
        'use strict';
        document.querySelectorAll('.grid-view-ls [data-rowlink]').forEach(tr => {
            let link = tr.getAttribute('data-rowlink');
            tr.setAttribute('tabindex', '0');
            tr.querySelectorAll('td:not(.ls-sticky-column)').forEach(td => {
                td.addEventListener('click', function (e) {
                    if (e.target.matches('input, select, textarea, button, a')) {
                        return;
                    }
                    window.location.href = link;
                });
            });
            tr.addEventListener('keydown', function (e) {
                if (e.target.matches('input, select, textarea, button, a')) {
                    return;
                }
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.location.href = link;
                }
            });
        });
    }
};

LS.rowlink.create();