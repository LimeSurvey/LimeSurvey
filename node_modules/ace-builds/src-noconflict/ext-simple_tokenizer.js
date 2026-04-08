ace.define("ace/ext/simple_tokenizer",["require","exports","module","ace/tokenizer","ace/layer/text_util"], function(require, exports, module){"use strict";
var Tokenizer = require("../tokenizer").Tokenizer;
var isTextToken = require("../layer/text_util").isTextToken;
var SimpleTokenizer = /** @class */ (function () {
    function SimpleTokenizer(content, tokenizer) {
        this._lines = content.split(/\r\n|\r|\n/);
        this._states = [];
        this._tokenizer = tokenizer;
    }
    SimpleTokenizer.prototype.getTokens = function (row) {
        var line = this._lines[row];
        var previousState = this._states[row - 1];
        var data = this._tokenizer.getLineTokens(line, previousState);
        this._states[row] = data.state;
        return data.tokens;
    };
    SimpleTokenizer.prototype.getLength = function () {
        return this._lines.length;
    };
    return SimpleTokenizer;
}());
function tokenize(content, highlightRules) {
    var tokenizer = new SimpleTokenizer(content, new Tokenizer(highlightRules.getRules()));
    var result = [];
    for (var lineIndex = 0; lineIndex < tokenizer.getLength(); lineIndex++) {
        var lineTokens = tokenizer.getTokens(lineIndex);
        result.push(lineTokens.map(function (token) { return ({
            className: isTextToken(token.type) ? undefined : "ace_" + token.type.replace(/\./g, " ace_"),
            value: token.value
        }); }));
    }
    return result;
}
exports.tokenize = tokenize;

});                (function() {
                    ace.require(["ace/ext/simple_tokenizer"], function(m) {
                        if (typeof module == "object" && typeof exports == "object" && module) {
                            module.exports = m;
                        }
                    });
                })();
            