/**
 * Sidemenu Component - Vanilla JS
 * Displays menu items
 */

import ConsoleShim from '../../../meta/lib/ConsoleShim.js';

const LOG = new ConsoleShim('globalsidepanel');

class Sidemenu {
    constructor(container, store, menu) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.store = store;
        this.menu = menu;

        this.render();
        this.attachEventListeners();
        this.updatePjaxLinks();
        this.redoTooltips();
    }

    get sortedMenuEntries() {
        if (!this.menu || !this.menu.entries) return [];
        return LS.ld.orderBy(
            this.menu.entries,
            a => parseInt(a.ordering || 999999),
            ['asc']
        );
    }

    render() {
        if (!this.container) return;

        const level = this.menu && typeof this.menu.level !== 'undefined'
            ? this.menu.level : 0;

        this.container.innerHTML = `
            <ul class="list-group subpanel col-12 level-${level}">
                ${this.sortedMenuEntries.map(menuItem => this.renderMenuItem(menuItem)).join('')}
            </ul>
        `;
    }

    renderMenuItem(menuItem) {
        const esc = this.escapeHTML;
        const href = this.getHref(menuItem);
        const linkClass = this.getLinkClass(menuItem);
        const target = menuItem.link_external ? '_blank' : '';
        const tooltip = this.reConvertHTML(menuItem.menu_description);
        const menuItemPartial = menuItem.partial.split('/').pop();
        const isSelected = this.store.get('lastMenuItemOpen') === menuItemPartial;

        return `
            <a data-menu-item="${esc(menuItemPartial)}"
               href="${esc(href)}"
               target="${target}"
               id="sidemenu_${esc(menuItem.name)}"
               class="list-group-item ${linkClass}"
               title="${esc(tooltip)}"
               data-bs-toggle="tooltip">
                <div class="col-12 ${esc(menuItem.menu_class || '')}">
                    <div class="ls-space padding all-0 ${isSelected ? 'col-md-10' : 'col-12'}">
                        ${this.renderMenuIcon(menuItem)}
                        <span>${esc(menuItem.menu_title)}</span>
                        ${menuItem.link_external ? '<i class="ri-external-link-fill">&nbsp;</i>' : ''}
                    </div>
                    ${isSelected ? `
                        <div class="col-md-2 text-center ls-space padding all-0 background white">
                            <i class="ri-arrow-right-s-line">&nbsp;</i>
                        </div>
                    ` : ''}
                </div>
            </a>
        `;
    }

    renderMenuIcon(menuItem) {
        if (!menuItem.menu_icon) return '';

        const esc = this.escapeHTML;
        const iconType = menuItem.menu_icon_type || 'fontawesome';
        let iconClass = '';

        if (iconType === 'fontawesome') {
            iconClass = `fa fa-${esc(menuItem.menu_icon)}`;
        } else if (iconType === 'remix') {
            iconClass = `ri-${esc(menuItem.menu_icon)}`;
        } else if (iconType === 'iconClass') {
            iconClass = esc(menuItem.menu_icon);
        }

        return `<i class="${iconClass}"></i>`;
    }

    getHref(menuItem) {
        const menuItemPartial = menuItem.partial.split('/').pop();
        return LS.createUrl(window.GlobalSideMenuData.baseLinkUrl, { partial: menuItemPartial });
    }

    getLinkClass(menuItem) {
        let classes = "ls-flex-row nowrap ";
        const isSelected = this.store.get('lastMenuItemOpen') === menuItem.partial.split('/').pop();
        classes += isSelected ? 'selected ' : ' ';
        return classes;
    }

    escapeHTML(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    reConvertHTML(string) {
        if (!string) return '';

        // Using Unicode escape sequences to avoid Babel parse errors with smart quotes
        const chars = ["'","©","Û","®","ž","Ü","Ÿ","Ý","$","Þ","%","¡","ß","¢","à","£","á","À","¤","â","Á","¥","ã","Â","¦","ä","Ã","§","å","Ä","¨","æ","Å","©","ç","Æ","ª","è","Ç","«","é","È","¬","ê","É","­","ë","Ê","®","ì","Ë","¯","í","Ì","°","î","Í","±","ï","Î","²","ð","Ï","³","ñ","Ð","´","ò","Ñ","µ","ó","Õ","¶","ô","Ö","·","õ","Ø","¸","ö","Ù","¹","÷","Ú","º","ø","Û","»","ù","Ü","@","¼","ú","Ý","½","û","Þ","€","¾","ü","ß","¿","ý","à","‚","À","þ","á","ƒ","Á","ÿ","å","„","Â","æ","…","Ã","ç","†","Ä","è","‡","Å","é","ˆ","Æ","ê","‰","Ç","ë","Š","È","ì","‹","É","í","Œ","Ê","î","Ë","ï","Ž","Ì","ð","Í","ñ","Î","ò","\u2018","Ï","ó","\u2019","Ð","ô","\u201C","Ñ","õ","\u201D","Ò","ö","•","Ó","ø","–","Ô","ù","—","Õ","ú","˜","Ö","û","™","×","ý","š","Ø","þ","›","Ù","ÿ","œ","Ú"];
        const codes = ["&#039;","&copy;","&#219;","&reg;","&#158;","&#220;","&#159;","&#221;","&#36;","&#222;","&#37;","&#161;","&#223;","&#162;","&#224;","&#163;","&#225;","&Agrave;","&#164;","&#226;","&Aacute;","&#165;","&#227;","&Acirc;","&#166;","&#228;","&Atilde;","&#167;","&#229;","&Auml;","&#168;","&#230;","&Aring;","&#169;","&#231;","&AElig;","&#170;","&#232;","&Ccedil;","&#171;","&#233;","&Egrave;","&#172;","&#234;","&Eacute;","&#173;","&#235;","&Ecirc;","&#174;","&#236;","&Euml;","&#175;","&#237;","&Igrave;","&#176;","&#238;","&Iacute;","&#177;","&#239;","&Icirc;","&#178;","&#240;","&Iuml;","&#179;","&#241;","&ETH;","&#180;","&#242;","&Ntilde;","&#181;","&#243;","&Otilde;","&#182;","&#244;","&Ouml;","&#183;","&#245;","&Oslash;","&#184;","&#246;","&Ugrave;","&#185;","&#247;","&Uacute;","&#186;","&#248;","&Ucirc;","&#187;","&#249;","&Uuml;","&#64;","&#188;","&#250;","&Yacute;","&#189;","&#251;","&THORN;","&#128;","&#190;","&#252","&szlig;","&#191;","&#253;","&agrave;","&#130;","&#192;","&#254;","&aacute;","&#131;","&#193;","&#255;","&aring;","&#132;","&#194;","&aelig;","&#133;","&#195;","&ccedil;","&#134;","&#196;","&egrave;","&#135;","&#197;","&eacute;","&#136;","&#198;","&ecirc;","&#137;","&#199;","&euml;","&#138;","&#200;","&igrave;","&#139;","&#201;","&iacute;","&#140;","&#202;","&icirc;","&#203;","&iuml;","&#142;","&#204;","&eth;","&#205;","&ntilde;","&#206;","&ograve;","&#145;","&#207;","&oacute;","&#146;","&#208;","&ocirc;","&#147;","&#209;","&otilde;","&#148;","&#210;","&ouml;","&#149;","&#211;","&oslash;","&#150;","&#212;","&ugrave;","&#151;","&#213;","&uacute;","&#152;","&#214;","&ucirc;","&#153;","&#215;","&yacute;","&#154;","&#216;","&thorn;","&#155;","&#217;","&yuml;","&#156;","&#218;"];

        LS.ld.each(codes, (code, i) => {
            string = string.replace(new RegExp(code, 'g'), chars[i]);
        });

        return string;
    }

    attachEventListeners() {
        if (!this.container) {
            return;
        }
        const menuItems = this.container.querySelectorAll('[data-menu-item]');
        menuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                const menuItemPartial = item.getAttribute('data-menu-item');
                this.store.commit('setLastMenuItemOpen', menuItemPartial);
                LOG.log('Opened Menuitem', menuItemPartial);
            });
        });
    }

    updatePjaxLinks() {
        // Force update of pjax links
    }

    redoTooltips() {
        if (window.LS && window.LS.doToolTip) {
            window.LS.doToolTip();
        }
    }

    update() {
        this.render();
        this.attachEventListeners();
        this.updatePjaxLinks();
        this.redoTooltips();
    }
}

export default Sidemenu;
