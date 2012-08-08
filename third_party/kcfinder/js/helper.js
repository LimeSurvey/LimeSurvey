/** This file is part of KCFinder project
  *
  *      @desc Helper object
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
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

_.selection = function(field, start, end) {
    if (field.createTextRange) {
        var selRange = field.createTextRange();
        selRange.collapse(true);
        selRange.moveStart('character', start);
        selRange.moveEnd('character', end-start);
        selRange.select();
    } else if (field.setSelectionRange) {
        field.setSelectionRange(start, end);
    } else if (field.selectionStart) {
        field.selectionStart = start;
        field.selectionEnd = end;
    }
    field.focus();
};

_.htmlValue = function(value) {
    return value
        .replace(/\&/g, "&amp;")
        .replace(/\"/g, "&quot;")
        .replace(/\'/g, "&#39;");
};

_.htmlData = function(value) {
    return value
        .replace(/\&/g, "&amp;")
        .replace(/\</g, "&lt;")
        .replace(/\>/g, "&gt;")
        .replace(/\ /g, "&nbsp;");
}

_.jsValue = function(value) {
    return value
        .replace(/\\/g, "\\\\")
        .replace(/\r?\n/, "\\\n")
        .replace(/\"/g, "\\\"")
        .replace(/\'/g, "\\'");
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

_.inArray = function(needle, arr) {
    if ((typeof arr == 'undefined') || !arr.length || !arr.push)
        return false;
    for (var i = 0; i < arr.length; i++)
        if (arr[i] == needle)
            return true;
    return false;
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
    var fullDirExpr = /^([a-z]+)\:\/\/([^\/^\:]+)(\:(\d+))?\/(.+)$/,
        prefix = "";
    if (fullDirExpr.test(path)) {
        var port = path.replace(fullDirExpr, "$4");
        prefix = path.replace(fullDirExpr, "$1://$2")
        if (port.length)
            prefix += ":" + port;
        prefix += "/";
        path = path.replace(fullDirExpr, "$5");
    }

    var dirs = path.split('/');
    var escapePath = '';
    for (var i = 0; i < dirs.length; i++)
        escapePath += encodeURIComponent(dirs[i]) + '/';

    return prefix + escapePath.substr(0, escapePath.length - 1);
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

_.md5 = function(string) {

    var RotateLeft = function(lValue, iShiftBits) {
        return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
    };

    var AddUnsigned = function(lX,lY) {
        var lX4, lY4, lX8, lY8, lResult;
        lX8 = (lX & 0x80000000);
        lY8 = (lY & 0x80000000);
        lX4 = (lX & 0x40000000);
        lY4 = (lY & 0x40000000);
        lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
        if (lX4 & lY4)
            return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
        if (lX4 | lY4)
            return (lResult & 0x40000000)
                ? (lResult ^ 0xC0000000 ^ lX8 ^ lY8)
                : (lResult ^ 0x40000000 ^ lX8 ^ lY8);
        else
            return (lResult ^ lX8 ^ lY8);
    };

    var F = function(x, y, z) { return (x & y) | ((~x) & z); };
    var G = function(x, y, z) { return (x & z) | (y & (~z)); };
    var H = function(x, y, z) { return (x ^ y ^ z); };
    var I = function(x, y, z) { return (y ^ (x | (~z))); };

    var FF = function(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    var GG = function(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    var HH = function(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    var II = function(a, b, c, d, x, s, ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    };

    var ConvertToWordArray = function(string) {
        var lWordCount;
        var lMessageLength = string.length;
        var lNumberOfWords_temp1 = lMessageLength + 8;
        var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
        var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
        var lWordArray = [lNumberOfWords - 1];
        var lBytePosition = 0;
        var lByteCount = 0;
        while (lByteCount < lMessageLength) {
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount) << lBytePosition));
            lByteCount++;
        }
        lWordCount = (lByteCount - (lByteCount % 4)) / 4;
        lBytePosition = (lByteCount % 4) * 8;
        lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
        lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
        lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
        return lWordArray;
    };

    var WordToHex = function(lValue) {
        var WordToHexValue = "", WordToHexValue_temp = "", lByte, lCount;
        for (lCount = 0; lCount <= 3; lCount++) {
            lByte = (lValue >>> (lCount * 8)) & 255;
            WordToHexValue_temp = "0" + lByte.toString(16);
            WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length - 2,2);
        }
        return WordToHexValue;
    };

    var x = [];
    var k, AA, BB, CC, DD, a, b, c, d;
    var S11 = 7, S12 = 12, S13 = 17, S14 = 22;
    var S21 = 5, S22 = 9,  S23 = 14, S24 = 20;
    var S31 = 4, S32 = 11, S33 = 16, S34 = 23;
    var S41 = 6, S42 = 10, S43 = 15, S44 = 21;

    string = _.utf8encode(string);

    x = ConvertToWordArray(string);

    a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

    for (k = 0; k < x.length; k += 16) {
        AA = a; BB = b; CC = c; DD = d;
        a = FF(a, b, c, d, x[k + 0],  S11, 0xD76AA478);
        d = FF(d, a, b, c, x[k + 1],  S12, 0xE8C7B756);
        c = FF(c, d, a, b, x[k + 2],  S13, 0x242070DB);
        b = FF(b, c, d, a, x[k + 3],  S14, 0xC1BDCEEE);
        a = FF(a, b, c, d, x[k + 4],  S11, 0xF57C0FAF);
        d = FF(d, a, b, c, x[k + 5],  S12, 0x4787C62A);
        c = FF(c, d, a, b, x[k + 6],  S13, 0xA8304613);
        b = FF(b, c, d, a, x[k + 7],  S14, 0xFD469501);
        a = FF(a, b, c, d, x[k + 8],  S11, 0x698098D8);
        d = FF(d, a, b, c, x[k + 9],  S12, 0x8B44F7AF);
        c = FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
        b = FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
        a = FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
        d = FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
        c = FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
        b = FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
        a = GG(a, b, c, d, x[k + 1],  S21, 0xF61E2562);
        d = GG(d, a, b, c, x[k + 6],  S22, 0xC040B340);
        c = GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
        b = GG(b, c, d, a, x[k + 0],  S24, 0xE9B6C7AA);
        a = GG(a, b, c, d, x[k + 5],  S21, 0xD62F105D);
        d = GG(d, a, b, c, x[k + 10], S22, 0x2441453);
        c = GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
        b = GG(b, c, d, a, x[k + 4],  S24, 0xE7D3FBC8);
        a = GG(a, b, c, d, x[k + 9],  S21, 0x21E1CDE6);
        d = GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
        c = GG(c, d, a, b, x[k + 3],  S23, 0xF4D50D87);
        b = GG(b, c, d, a, x[k + 8],  S24, 0x455A14ED);
        a = GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
        d = GG(d, a, b, c, x[k + 2],  S22, 0xFCEFA3F8);
        c = GG(c, d, a, b, x[k + 7],  S23, 0x676F02D9);
        b = GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
        a = HH(a, b, c, d, x[k + 5],  S31, 0xFFFA3942);
        d = HH(d, a, b, c, x[k + 8],  S32, 0x8771F681);
        c = HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
        b = HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
        a = HH(a, b, c, d, x[k + 1],  S31, 0xA4BEEA44);
        d = HH(d, a, b, c, x[k + 4],  S32, 0x4BDECFA9);
        c = HH(c, d, a, b, x[k + 7],  S33, 0xF6BB4B60);
        b = HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
        a = HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
        d = HH(d, a, b, c, x[k + 0],  S32, 0xEAA127FA);
        c = HH(c, d, a, b, x[k + 3],  S33, 0xD4EF3085);
        b = HH(b, c, d, a, x[k + 6],  S34, 0x4881D05);
        a = HH(a, b, c, d, x[k + 9],  S31, 0xD9D4D039);
        d = HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
        c = HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
        b = HH(b, c, d, a, x[k + 2],  S34, 0xC4AC5665);
        a = II(a, b, c, d, x[k + 0],  S41, 0xF4292244);
        d = II(d, a, b, c, x[k + 7],  S42, 0x432AFF97);
        c = II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
        b = II(b, c, d, a, x[k + 5],  S44, 0xFC93A039);
        a = II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
        d = II(d, a, b, c, x[k + 3],  S42, 0x8F0CCC92);
        c = II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
        b = II(b, c, d, a, x[k + 1],  S44, 0x85845DD1);
        a = II(a, b, c, d, x[k + 8],  S41, 0x6FA87E4F);
        d = II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
        c = II(c, d, a, b, x[k + 6],  S43, 0xA3014314);
        b = II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
        a = II(a, b, c, d, x[k + 4],  S41, 0xF7537E82);
        d = II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
        c = II(c, d, a, b, x[k + 2],  S43, 0x2AD7D2BB);
        b = II(b, c, d, a, x[k + 9],  S44, 0xEB86D391);
        a = AddUnsigned(a, AA);
        b = AddUnsigned(b, BB);
        c = AddUnsigned(c, CC);
        d = AddUnsigned(d, DD);
    }

    var temp = WordToHex(a) + WordToHex(b) + WordToHex(c) + WordToHex(d);

    return temp.toLowerCase();
};

_.utf8encode = function(string) {
    string = string.replace(/\r\n/g,"\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {

        var c = string.charCodeAt(n);

        if (c < 128) {
            utftext += String.fromCharCode(c);
        } else if((c > 127) && (c < 2048)) {
            utftext += String.fromCharCode((c >> 6) | 192);
            utftext += String.fromCharCode((c & 63) | 128);
        } else {
            utftext += String.fromCharCode((c >> 12) | 224);
            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
            utftext += String.fromCharCode((c & 63) | 128);
        }

    }

    return utftext;
};
