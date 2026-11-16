ace.define("ace/layer/text_markers",["require","exports","module","ace/layer/text","ace/lib/lang","ace/edit_session","ace/editor","ace/config"], function(require, exports, module){var Text = require("./text").Text;
var lang = require("../lib/lang");
var textMarkerMixin = {
    $removeClass: function (className) {
        if (!this.element || !className)
            return;
        var selectedElements = this.element.querySelectorAll('.' + className);
        for (var i = 0; i < selectedElements.length; i++) {
            var element = selectedElements[i];
            element.classList.remove(className);
            if (element.hasAttribute('data-whitespace')) {
                var originalWhitespace = element.getAttribute('data-whitespace');
                var textNode = this.dom.createTextNode(originalWhitespace, this.element);
                textNode["charCount"] = element["charCount"];
                element.parentNode.replaceChild(textNode, element);
            }
        }
    },
    $applyTextMarkers: function () {
        var _this = this;
        if (this.session.$scheduleForRemove) {
            this.session.$scheduleForRemove.forEach(function (className) {
                _this.$removeClass(className);
            });
            this.session.$scheduleForRemove = new Set();
        }
        var textMarkers = this.session.getTextMarkers();
        if (textMarkers.length === 0) {
            return;
        }
        var classNameGroups = new Set();
        textMarkers.forEach(function (marker) {
            classNameGroups.add(marker.className);
        });
        classNameGroups.forEach(function (className) {
            _this.$removeClass(className);
        });
        textMarkers.forEach(function (marker) {
            var _loop_1 = function (row) {
                cell = _this.$lines.cells.find(function (el) { return el.row === row; });
                if (cell) {
                    _this.$modifyDomForMarkers(cell.element, row, marker);
                }
            };
            var cell;
            for (var row = marker.range.start.row; row <= marker.range.end.row; row++) {
                _loop_1(row);
            }
        });
    },
    $modifyDomForMarkers: function (lineElement, row, marker) {
        var _this = this;
        var lineLength = this.session.getLine(row).length;
        var startCol = row > marker.range.start.row ? 0 : marker.range.start.column;
        var endCol = row < marker.range.end.row ? lineLength : marker.range.end.column;
        if (startCol === endCol) {
            return;
        }
        var lineElements = [];
        if (lineElement.classList.contains('ace_line_group')) {
            lineElements = Array.from(lineElement.childNodes);
        }
        else {
            lineElements = [lineElement];
        }
        var currentColumn = 0;
        lineElements.forEach(function (lineElement) {
            var childNodes = Array.from(lineElement.childNodes);
            for (var i = 0; i < childNodes.length; i++) {
                var subChildNodes = [childNodes[i]];
                var parentNode = lineElement;
                if (childNodes[i].childNodes && childNodes[i].childNodes.length > 0) {
                    subChildNodes = Array.from(childNodes[i].childNodes);
                    parentNode = childNodes[i];
                }
                for (var j = 0; j < subChildNodes.length; j++) {
                    var node = subChildNodes[j];
                    var nodeText = node.textContent || '';
                    if (node.parentNode["charCount"]) {
                        node["charCount"] = node.parentNode["charCount"];
                    }
                    var contentLength = node["charCount"] || nodeText.length;
                    var nodeStart = currentColumn;
                    var nodeEnd = currentColumn + contentLength;
                    if (node["charCount"] === 0 || contentLength === 0) {
                        continue;
                    }
                    if (nodeStart < endCol && nodeEnd > startCol) {
                        var beforeSelection = Math.max(0, startCol - nodeStart);
                        var afterSelection = Math.max(0, nodeEnd - endCol);
                        var selectionLength = contentLength - beforeSelection - afterSelection;
                        if (marker.type === "invisible") {
                            _this.$processInvisibleMarker(node, parentNode, {
                                beforeSelection: beforeSelection,
                                selectionLength: selectionLength,
                                afterSelection: afterSelection
                            }, marker);
                        }
                        else {
                            _this.$processRegularMarker(node, parentNode, {
                                beforeSelection: beforeSelection,
                                selectionLength: selectionLength,
                                afterSelection: afterSelection
                            }, marker, nodeStart, startCol, endCol);
                        }
                    }
                    currentColumn = nodeEnd;
                }
            }
        });
    },
    $processInvisibleMarker: function (node, parentNode, selectionSegment, marker) {
        var nodeText = node.textContent || '';
        if (node.nodeType === 3) { // Text node
            var fragment = this.dom.createFragment(this.element);
            if (selectionSegment.beforeSelection > 0) {
                fragment.appendChild(this.dom.createTextNode(nodeText.substring(0, selectionSegment.beforeSelection), this.element));
            }
            if (selectionSegment.selectionLength > 0) {
                var selectedText = selectionSegment.beforeSelection === 0 && selectionSegment.afterSelection === 0
                    ? nodeText : nodeText.substring(selectionSegment.beforeSelection, selectionSegment.beforeSelection + selectionSegment.selectionLength);
                var segments = selectedText.match(/\s+|[^\s]+/g) || [];
                for (var k = 0; k < segments.length; k++) {
                    var segment = segments[k];
                    var span = void 0;
                    if (/^\s+$/.test(segment)) {
                        span = this.dom.createElement("span");
                        span.className = marker.className;
                        var symbol = node["charCount"] ? this.TAB_CHAR : this.SPACE_CHAR;
                        span.textContent = lang.stringRepeat(symbol, segment.length);
                        span.setAttribute("data-whitespace", segment);
                        fragment.appendChild(span);
                    }
                    else {
                        span = this.dom.createElement("span");
                        span.textContent = segment;
                    }
                    if (node["charCount"] && segments.length === 1) { //this is for real tabs
                        span["charCount"] = node["charCount"];
                    }
                    fragment.appendChild(span);
                }
            }
            if (selectionSegment.afterSelection > 0) {
                fragment.appendChild(this.dom.createTextNode(nodeText.substring(selectionSegment.beforeSelection + selectionSegment.selectionLength), this.element));
            }
            parentNode.replaceChild(fragment, node);
        }
    },
    $processRegularMarker: function (node, parentNode, selectionSegment, marker, nodeStart, startCol, endCol) {
        var nodeText = node.textContent || '';
        if (node.nodeType === 3) { // Text node
            if (selectionSegment.beforeSelection > 0 || selectionSegment.afterSelection > 0) {
                var fragment = this.dom.createFragment(this.element);
                if (selectionSegment.beforeSelection > 0) {
                    fragment.appendChild(this.dom.createTextNode(nodeText.substring(0, selectionSegment.beforeSelection), this.element));
                }
                if (selectionSegment.selectionLength > 0) {
                    var selectedSpan = this.dom.createElement('span');
                    selectedSpan.classList.add(marker.className);
                    selectedSpan.textContent = nodeText.substring(selectionSegment.beforeSelection, selectionSegment.beforeSelection + selectionSegment.selectionLength);
                    fragment.appendChild(selectedSpan);
                }
                if (selectionSegment.afterSelection > 0) {
                    fragment.appendChild(this.dom.createTextNode(nodeText.substring(selectionSegment.beforeSelection + selectionSegment.selectionLength), this.element));
                }
                parentNode.replaceChild(fragment, node);
            }
            else {
                var selectedSpan = this.dom.createElement('span');
                selectedSpan.classList.add(marker.className);
                selectedSpan.textContent = nodeText;
                selectedSpan["charCount"] = node["charCount"];
                parentNode.replaceChild(selectedSpan, node);
            }
        }
        else if (node.nodeType === 1) { // Element node
            if (nodeStart >= startCol && nodeStart + (nodeText.length || 0) <= endCol) {
                node.classList.add(marker.className);
            }
            else {
                if (selectionSegment.beforeSelection > 0 || selectionSegment.afterSelection > 0) {
                    var nodeClasses = node.className;
                    var fragment = this.dom.createFragment(this.element);
                    if (selectionSegment.beforeSelection > 0) {
                        var beforeSpan = this.dom.createElement('span');
                        beforeSpan.className = nodeClasses;
                        beforeSpan.textContent = nodeText.substring(0, selectionSegment.beforeSelection);
                        fragment.appendChild(beforeSpan);
                    }
                    if (selectionSegment.selectionLength > 0) {
                        var selectedSpan = this.dom.createElement('span');
                        selectedSpan.className = nodeClasses + ' ' + marker.className;
                        selectedSpan.textContent = nodeText.substring(selectionSegment.beforeSelection, selectionSegment.beforeSelection + selectionSegment.selectionLength);
                        fragment.appendChild(selectedSpan);
                    }
                    if (selectionSegment.afterSelection > 0) {
                        var afterSpan = this.dom.createElement('span');
                        afterSpan.className = nodeClasses;
                        afterSpan.textContent = nodeText.substring(selectionSegment.beforeSelection + selectionSegment.selectionLength);
                        fragment.appendChild(afterSpan);
                    }
                    parentNode.replaceChild(fragment, node);
                }
            }
        }
    }
};
Object.assign(Text.prototype, textMarkerMixin);
var EditSession = require("../edit_session").EditSession;
var editSessionTextMarkerMixin = {
    addTextMarker: function (range, className, type) {
        this.$textMarkerId = this.$textMarkerId || 0;
        this.$textMarkerId++;
        var marker = {
            range: range,
            id: this.$textMarkerId,
            className: className,
            type: type
        };
        if (!this.$textMarkers) {
            this.$textMarkers = [];
        }
        this.$textMarkers[marker.id] = marker;
        return marker.id;
    },
    removeTextMarker: function (markerId) {
        if (!this.$textMarkers) {
            return;
        }
        var marker = this.$textMarkers[markerId];
        if (!marker) {
            return;
        }
        if (!this.$scheduleForRemove) {
            this.$scheduleForRemove = new Set();
        }
        this.$scheduleForRemove.add(marker.className);
        delete this.$textMarkers[markerId];
    },
    getTextMarkers: function () {
        return this.$textMarkers || [];
    }
};
Object.assign(EditSession.prototype, editSessionTextMarkerMixin);
var onAfterRender = function (e, renderer) {
    renderer.$textLayer.$applyTextMarkers();
};
var Editor = require("../editor").Editor;
require("../config").defineOptions(Editor.prototype, "editor", {
    enableTextMarkers: {
        set: function (val) {
            if (val) {
                this.renderer.on("afterRender", onAfterRender);
            }
            else {
                this.renderer.off("afterRender", onAfterRender);
            }
        },
        value: true
    }
});
exports.textMarkerMixin = textMarkerMixin;
exports.editSessionTextMarkerMixin = editSessionTextMarkerMixin;

});

ace.define("ace/ext/whitespaces_in_selection",["require","exports","module","ace/layer/text_markers","ace/editor","ace/config","ace/lib/dom"], function(require, exports, module){/**
 * ## Show whitespaces in the current selection
 *
 * This extension adds a configuration option `showWhitespacesInSelection` to the editor
 * that highlights whitespaces within the current selection. When enabled, it adds a
 * marker to the selection that makes whitespaces visible.
 */
"use strict";
require("../layer/text_markers");
var Editor = require("../editor").Editor;
var config = require("../config");
var dom = require("../lib/dom");
dom.importCssString("\n.ace_whitespaces_in_selection {\n    color: rgba(0,0,0,0.29);\n}\n\n.ace_dark .ace_whitespaces_in_selection {\n    color: rgba(187, 181, 181, 0.5);\n}\n", "ace_whitespaces_in_selection", false);
config.defineOptions(Editor.prototype, "editor", {
    showWhitespacesInSelection: {
        set: function (val) {
            this.$showWhitespacesInSelection = val;
            if (val) {
                if (!this.$boundChangeSelectionForWhitespace) {
                    this.$boundChangeSelectionForWhitespace = $onChangeSelectionForWhitespace.bind(this);
                }
                this.on("changeSelection", this.$boundChangeSelectionForWhitespace);
            }
            else {
                this.off("changeSelection", this.$boundChangeSelectionForWhitespace);
                if (this.session && this.session.$invisibleMarkerId) {
                    this.session.removeTextMarker(this.session.$invisibleMarkerId);
                    this.session.$invisibleMarkerId = null;
                }
                this.$boundChangeSelectionForWhitespace = null;
            }
        },
        get: function () {
            return this.$showWhitespacesInSelection;
        },
        initialValue: false
    }
});
function $onChangeSelectionForWhitespace() {
    var invisibleMarkerId = this.session.$invisibleMarkerId;
    if (invisibleMarkerId) {
        this.session.removeTextMarker(invisibleMarkerId);
        this.session.$invisibleMarkerId = null;
    }
    var currentRange = this.selection.getRange();
    if (!currentRange.isEmpty()) {
        this.session.$invisibleMarkerId = this.session.addTextMarker(currentRange, "ace_whitespaces_in_selection", "invisible");
    }
}

});                (function() {
                    ace.require(["ace/ext/whitespaces_in_selection"], function(m) {
                        if (typeof module == "object" && typeof exports == "object" && module) {
                            module.exports = m;
                        }
                    });
                })();
            