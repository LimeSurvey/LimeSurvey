/** This file is part of KCFinder project
  *
  *      @desc Helper object
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

var _ = function(id) {
    return document.getElementById(id);
};

_.nopx = function(val) {
    return parseInt(val.replace(/^(\d+)px$/, "$1"));
};

_.unselect = function() {
    if (document.selection && document.selection.empty)
        document.selection.empty() ;
    else if (window.getSelection) {
        var sel = window.getSelection();
        if (sel && sel.removeAllRanges)
        sel.removeAllRanges();
    }
};

_.htmlValue = function(value) {
    return value.replace('"', "&quot;").replace("'", "&#39;");
};

_.htmlData = function(value) {
    return value.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\ /g, "&nbsp;");
}

_.jsValue = function(value) {
    return value.replace(/\r?\n/, "\\\n").replace('"', "\\\"").replace("'", "\\'");
};

_.basename = function(path) {
    var expr = /^.*\/([^\/]+)\/?$/g;
    return expr.test(path)
        ? path.replace(expr, "$1")
        : path;
};

_.dirname = function(path) {
    var expr = /^(.*)\/[^\/]+\/?$/g;
    return expr.test(path)
        ? path.replace(expr, "$1")
        : '';
};

_.getFileExtension = function(filename, toLower) {
    if (typeof(toLower) == 'undefined') toLower = true;
    if (/^.*\.[^\.]*$/.test(filename)) {
        var ext = filename.replace(/^.*\.([^\.]*)$/, "$1");
        return toLower ? ext.toLowerCase(ext) : ext;
    } else
        return "";
};

_.escapeDirs = function(path) {
    var dirs = path.split('/');
    var escapePath = '';
    for (var i = 0; i < dirs.length; i++)
        escapePath += encodeURIComponent(dirs[i]) + '/';
    return escapePath.substr(0, escapePath.length - 1);
};

_.outerSpace = function(selector, type, mbp) {
    if (!mbp) mbp = "mbp";
    var r = 0;
    if (/m/i.test(mbp)) {
        var m = _.nopx($(selector).css('margin-' + type));
        if (m) r += m;
    }
    if (/b/i.test(mbp)) {
        var b = _.nopx($(selector).css('border-' + type + '-width'));
        if (b) r += b;
    }
    if (/p/i.test(mbp)) {
        var p = _.nopx($(selector).css('padding-' + type));
        if (p) r += p;
    }
    return r;
};

_.outerLeftSpace = function(selector, mbp) {
    return _.outerSpace(selector, 'left', mbp);
};

_.outerTopSpace = function(selector, mbp) {
    return _.outerSpace(selector, 'top', mbp);
};

_.outerRightSpace = function(selector, mbp) {
    return _.outerSpace(selector, 'right', mbp);
};

_.outerBottomSpace = function(selector, mbp) {
    return _.outerSpace(selector, 'bottom', mbp);
};

_.outerHSpace = function(selector, mbp) {
    return (_.outerLeftSpace(selector, mbp) + _.outerRightSpace(selector, mbp));
};

_.outerVSpace = function(selector, mbp) {
    return (_.outerTopSpace(selector, mbp) + _.outerBottomSpace(selector, mbp));
};

_.kuki = {
    prefix: '',
    duration: 356,
    domain: '',
    path: '',
    secure: false,

    set: function(name, value, duration, domain, path, secure) {
        name = this.prefix + name;
        if (duration == null) duration = this.duration;
        if (secure == null) secure = this.secure;
        if ((domain == null) && this.domain) domain = this.domain;
        if ((path == null) && this.path) path = this.path;
        secure = secure ? true : false;

        var date = new Date();
        date.setTime(date.getTime() + (duration * 86400000));
        var expires = date.toGMTString();

        var str = name + '=' + value + '; expires=' + expires;
        if (domain != null) str += '; domain=' + domain;
        if (path != null) str += '; path=' + path;
        if (secure) str += '; secure';

        return (document.cookie = str) ? true : false;
    },

    get: function(name) {
        name = this.prefix + name;
        var nameEQ = name + '=';
        var kukis = document.cookie.split(';');
        var kuki;

        for (var i = 0; i < kukis.length; i++) {
            kuki = kukis[i];
            while (kuki.charAt(0) == ' ')
                kuki = kuki.substring(1, kuki.length);

            if (kuki.indexOf(nameEQ) == 0)
                return kuki.substring(nameEQ.length, kuki.length);
        }

        return null;
    },

    del: function(name) {
        return this.set(name, '', -1);
    },

    isSet: function(name) {
        return (this.get(name) != null);
    }
};
