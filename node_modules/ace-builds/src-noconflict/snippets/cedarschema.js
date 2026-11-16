ace.define("ace/snippets/cedarschema.snippets",["require","exports","module"], function(require, exports, module){module.exports = "snippet namespace\n\tnamespace ${1:Namespace} {\n\t    ${0}\n\t}\nsnippet entity\n\tentity ${1:EntityName} {\n\t    ${0}\n\t};\nsnippet entity_in\n\tentity ${1:EntityName} in [${2:Parent}] {\n\t    ${0}\n\t};\nsnippet action\n\taction \"${1:actionName}\" appliesTo {\n\t    principal: [${2:Principal}],\n\t    resource: [${3:Resource}],\n\t    context: {${0}}\n\t};\nsnippet action_in\n\taction \"${1:actionName}\" in [${2:ParentAction}] appliesTo {\n\t    principal: [${3:Principal}],\n\t    resource: [${4:Resource}],\n\t    context: {${0}}\n\t};\nsnippet type\n\ttype ${1:TypeName} = {\n\t    ${0}\n\t};\n";

});

ace.define("ace/snippets/cedarschema",["require","exports","module","ace/snippets/cedarschema.snippets"], function(require, exports, module){"use strict";
exports.snippetText = require("./cedarschema.snippets");
exports.scope = "cedarschema";

});                (function() {
                    ace.require(["ace/snippets/cedarschema"], function(m) {
                        if (typeof module == "object" && typeof exports == "object" && module) {
                            module.exports = m;
                        }
                    });
                })();
            