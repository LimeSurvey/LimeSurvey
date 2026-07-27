ace.define("ace/snippets/cedar.snippets",["require","exports","module"], function(require, exports, module){module.exports = "snippet permit\n\tpermit (\n\t    principal == ${1:Principal}::\"${2:id}\",\n\t    action == Action::\"${3:action}\",\n\t    resource == ${4:Resource}::\"${5:id}\"\n\t)${0};\nsnippet permit_when\n\tpermit (principal, action, resource)\n\twhen { ${0:condition} };\nsnippet forbid\n\tforbid (principal, action, resource)\n\twhen { ${0:condition} };\nsnippet forbid_unless\n\tforbid (principal, action, resource)\n\tunless { ${0:condition} };\nsnippet when\n\twhen { ${0:condition} }\nsnippet unless\n\tunless { ${0:condition} }\nsnippet annotation\n\t@${1:name}(\"${2:value}\")\n";

});

ace.define("ace/snippets/cedar",["require","exports","module","ace/snippets/cedar.snippets"], function(require, exports, module){"use strict";
exports.snippetText = require("./cedar.snippets");
exports.scope = "cedar";

});                (function() {
                    ace.require(["ace/snippets/cedar"], function(m) {
                        if (typeof module == "object" && typeof exports == "object" && module) {
                            module.exports = m;
                        }
                    });
                })();
            