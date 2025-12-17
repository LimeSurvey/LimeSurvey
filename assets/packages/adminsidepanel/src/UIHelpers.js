/**
 * UIHelpers - Utility functions for UI operations
 */
const UIHelpers = (function() {
    'use strict';

    /**
     * Translate a string using SideMenuData translations
     * @param {string} str - String to translate
     * @returns {string}
     */
    function translate(str) {
        if (window.SideMenuData && window.SideMenuData.translate) {
            return window.SideMenuData.translate[str] || str;
        }
        return str;
    }

    /**
     * Re-initialize tooltips
     */
    function redoTooltips() {
        if (window.LS && window.LS.doToolTip) {
            window.LS.doToolTip();
        }
    }

    /**
     * Convert HTML entities back to characters
     * Uses the same character mapping as the original Vue component
     * @param {string} string
     * @returns {string}
     */
    function reConvertHTML(string) {
        if (!string) return '';

        // HTML entity decode map (subset of commonly used)
        var entityMap = {
            '&#039;': "'",
            '&copy;': '\u00A9',
            '&reg;': '\u00AE',
            '&#36;': '$',
            '&#37;': '%',
            '&#64;': '@',
            '&Agrave;': '\u00C0',
            '&Aacute;': '\u00C1',
            '&Acirc;': '\u00C2',
            '&Atilde;': '\u00C3',
            '&Auml;': '\u00C4',
            '&Aring;': '\u00C5',
            '&AElig;': '\u00C6',
            '&Ccedil;': '\u00C7',
            '&Egrave;': '\u00C8',
            '&Eacute;': '\u00C9',
            '&Ecirc;': '\u00CA',
            '&Euml;': '\u00CB',
            '&Igrave;': '\u00CC',
            '&Iacute;': '\u00CD',
            '&Icirc;': '\u00CE',
            '&Iuml;': '\u00CF',
            '&ETH;': '\u00D0',
            '&Ntilde;': '\u00D1',
            '&Otilde;': '\u00D5',
            '&Ouml;': '\u00D6',
            '&Oslash;': '\u00D8',
            '&Ugrave;': '\u00D9',
            '&Uacute;': '\u00DA',
            '&Ucirc;': '\u00DB',
            '&Uuml;': '\u00DC',
            '&Yacute;': '\u00DD',
            '&THORN;': '\u00DE',
            '&szlig;': '\u00DF',
            '&agrave;': '\u00E0',
            '&aacute;': '\u00E1',
            '&acirc;': '\u00E2',
            '&atilde;': '\u00E3',
            '&auml;': '\u00E4',
            '&aring;': '\u00E5',
            '&aelig;': '\u00E6',
            '&ccedil;': '\u00E7',
            '&egrave;': '\u00E8',
            '&eacute;': '\u00E9',
            '&ecirc;': '\u00EA',
            '&euml;': '\u00EB',
            '&igrave;': '\u00EC',
            '&iacute;': '\u00ED',
            '&icirc;': '\u00EE',
            '&iuml;': '\u00EF',
            '&eth;': '\u00F0',
            '&ntilde;': '\u00F1',
            '&ograve;': '\u00F2',
            '&oacute;': '\u00F3',
            '&ocirc;': '\u00F4',
            '&otilde;': '\u00F5',
            '&ouml;': '\u00F6',
            '&oslash;': '\u00F8',
            '&ugrave;': '\u00F9',
            '&uacute;': '\u00FA',
            '&ucirc;': '\u00FB',
            '&yacute;': '\u00FD',
            '&thorn;': '\u00FE',
            '&yuml;': '\u00FF'
        };

        for (var entity in entityMap) {
            if (entityMap.hasOwnProperty(entity)) {
                string = string.split(entity).join(entityMap[entity]);
            }
        }

        // Also handle numeric entities
        string = string.replace(/&#(\d+);/g, function(match, dec) {
            return String.fromCharCode(dec);
        });

        return string;
    }

    /**
     * Render a menu icon based on type
     * @param {string} iconType
     * @param {string} icon
     * @returns {string} HTML string
     */
    function renderMenuIcon(iconType, icon) {
        if (!icon) return '';

        switch (iconType) {
            case 'fontawesome':
                return '<i class="fa fa-' + escapeHtml(icon) + '">&nbsp;</i>';
            case 'image':
                return '<img width="32px" src="' + escapeHtml(icon) + '" />';
            case 'iconclass':
            case 'remix':
                return '<i class="' + escapeHtml(icon) + '">&nbsp;</i>';
            default:
                return '';
        }
    }

    /**
     * Create a loader widget HTML
     * @param {string} id
     * @param {string} extraClass
     * @returns {string}
     */
    function createLoaderWidget(id, extraClass) {
        id = id || 'loader-' + Math.floor(1000 * Math.random());
        extraClass = extraClass || '';

        return '<div id="' + escapeHtml(id) + '" class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">' +
            '<div class="ls-flex align-content-center align-items-center">' +
                '<div class="loader-adminpanel text-center ' + escapeHtml(extraClass) + '">' +
                    '<div class="contain-pulse animate-pulse">' +
                        '<div class="square"></div>' +
                        '<div class="square"></div>' +
                        '<div class="square"></div>' +
                        '<div class="square"></div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    /**
     * Parse integer or return default value
     * @param {*} val
     * @param {number} defaultVal
     * @returns {number}
     */
    function parseIntOr(val, defaultVal) {
        defaultVal = defaultVal !== undefined ? defaultVal : 999999;
        var intVal = parseInt(val, 10);
        if (isNaN(intVal)) {
            return defaultVal;
        }
        return intVal;
    }

    /**
     * Check if we're in mobile view
     * @returns {boolean}
     */
    function useMobileView() {
        return window.innerWidth < 768;
    }

    /**
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    return {
        translate: translate,
        redoTooltips: redoTooltips,
        reConvertHTML: reConvertHTML,
        renderMenuIcon: renderMenuIcon,
        createLoaderWidget: createLoaderWidget,
        parseIntOr: parseIntOr,
        useMobileView: useMobileView,
        escapeHtml: escapeHtml
    };
})();

export default UIHelpers;
