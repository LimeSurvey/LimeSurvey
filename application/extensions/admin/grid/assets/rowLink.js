'use strict';
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.grid-view-ls [data-rowlink]').forEach(tr => {
        let link = tr.getAttribute('data-rowlink');
        tr.querySelectorAll('td:not(.ls-sticky-column)').forEach(td => {
            td.addEventListener('click', function () {
                window.location.href = link;
            });
        });
    });
});