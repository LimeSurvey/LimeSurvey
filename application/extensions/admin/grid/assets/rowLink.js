/**
 * rowLink.js – Grid row-link and disabled-link initialisation.
 *
 * Runs once on page load and sets up two behaviours for `.grid-view-ls`:
 *
 * 1. Disabled links – any `<a class="disabled">` inside the grid is made
 *    fully non-interactive: its `href` attribute is removed, `tabindex` is
 *    set to `-1`, `aria-disabled` is set to `"true"`, and a click handler
 *    is added that prevents both the default action and event propagation.
 *
 * 2. Row links – table rows that carry a `data-rowlink` attribute become
 *    clickable: a click on any `<td>` that is not a sticky column (and that
 *    does not originate from an interactive child element such as `<a>`,
 *    `<button>`, `<input>`, `<select>`, or `<textarea>`) navigates to the
 *    URL stored in the attribute.
 */
LS.rowlink = {
    /**
     * Initialises disabled-link handling and row-link navigation for all
     * `.grid-view-ls` grids present in the current document.
     *
     * Must be called after the DOM is ready. It is invoked automatically at
     * the bottom of this file and can be re-called after dynamic grid updates.
     */
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