/**
*  Ajax Autocomplete for jQuery, version 2.0.4
*  (c) 2012-2026 Tomas Kirda and contributors
*
*  Ajax Autocomplete for jQuery is freely distributable under the terms of an MIT-style license.
*  For details, see the web site: https://github.com/tkirda/jQuery-Autocomplete
*/

// src/index.ts
import jQuery from "jquery";

// src/jquery-ref.ts
var $ = null;
function setJQuery(jq) {
  $ = jq;
}

// src/utils.ts
var utils = {
  escapeRegExChars(value) {
    return value.replace(/[|\\{}()[\]^$+*?.]/g, "\\$&");
  },
  createNode(containerClass) {
    const div = document.createElement("div");
    div.className = containerClass;
    div.style.position = "absolute";
    div.style.display = "none";
    return div;
  }
};
var keys = {
  ESC: 27,
  TAB: 9,
  RETURN: 13,
  LEFT: 37,
  UP: 38,
  RIGHT: 39,
  DOWN: 40
};

// src/format.ts
function lookupFilter(suggestion, _originalQuery, queryLowerCase) {
  return suggestion.value.toLowerCase().indexOf(queryLowerCase) !== -1;
}
function transformResult(response) {
  return typeof response === "string" ? JSON.parse(response) : response;
}
function formatResult(suggestion, currentValue) {
  if (!currentValue) {
    const span = document.createElement("span");
    span.textContent = suggestion.value;
    return span.innerHTML;
  }
  const pattern = "(" + utils.escapeRegExChars(currentValue) + ")";
  return suggestion.value.replace(new RegExp(pattern, "gi"), "<strong>$1</strong>").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/&lt;(\/?strong)&gt;/g, "<$1>");
}
function formatGroup(_suggestion, category) {
  const div = document.createElement("div");
  div.className = "autocomplete-group";
  div.textContent = category;
  return div.outerHTML;
}

// src/defaults.ts
var noop = () => {
};
var defaults = {
  ajaxSettings: {},
  autoSelectFirst: false,
  appendTo: "body",
  width: "auto",
  minChars: 1,
  maxHeight: 300,
  deferRequestBy: 0,
  params: {},
  formatResult,
  formatGroup,
  zIndex: 9999,
  type: "GET",
  noCache: false,
  onSearchStart: noop,
  onSearchComplete: noop,
  onSearchError: noop,
  preserveInput: false,
  containerClass: "autocomplete-suggestions",
  tabDisabled: false,
  dataType: "text",
  triggerSelectOnValidInput: true,
  preventBadQueries: true,
  lookupFilter,
  paramName: "query",
  transformResult,
  showNoSuggestionNotice: false,
  noSuggestionNotice: "No results",
  orientation: "bottom",
  forceFixPosition: false
};

// src/Autocomplete.ts
var _Autocomplete = class _Autocomplete {
  constructor(el, options) {
    this.suggestions = [];
    this.badQueries = [];
    this.selectedIndex = -1;
    this.cachedResponse = {};
    this.onChangeTimeout = null;
    this.isLocal = false;
    this.classes = {
      selected: "autocomplete-selected",
      suggestion: "autocomplete-suggestion"
    };
    this.hint = null;
    this.hintValue = "";
    this.selection = null;
    this.currentRequest = null;
    this.element = el;
    this.el = $(el);
    this.currentValue = el.value;
    this.options = $.extend(true, {}, _Autocomplete.defaults, options);
    this.initialize();
    this.setOptions(options);
  }
  initialize() {
    const self = this;
    const suggestionSelector = `.${this.classes.suggestion}`;
    const selected = this.classes.selected;
    const options = this.options;
    this.element.setAttribute("autocomplete", "off");
    this.$noSuggestionsContainer = $('<div class="autocomplete-no-suggestion"></div>').html(
      options.noSuggestionNotice
    );
    this.noSuggestionsContainer = this.$noSuggestionsContainer.get(0);
    this.suggestionsContainer = _Autocomplete.utils.createNode(options.containerClass);
    this.$container = $(this.suggestionsContainer);
    this.$container.appendTo(options.appendTo || "body");
    if (options.width !== "auto") {
      this.$container.css("width", options.width);
    }
    const container = this.$container;
    container.on("mouseover.autocomplete", suggestionSelector, function() {
      self.activate($(this).data("index"));
    });
    container.on("click.autocomplete", suggestionSelector, function() {
      self.select($(this).data("index"));
    });
    container.on("mouseout.autocomplete", () => {
      this.selectedIndex = -1;
      container.children(`.${selected}`).removeClass(selected);
    });
    container.on("click.autocomplete", () => {
      if (this.blurTimeoutId !== void 0) {
        clearTimeout(this.blurTimeoutId);
      }
    });
    this.fixPositionCapture = () => {
      if (this.visible) {
        this.fixPosition();
      }
    };
    $(window).on("resize.autocomplete", this.fixPositionCapture);
    this.el.on("keydown.autocomplete", (e) => this.onKeyPress(e));
    this.el.on("keyup.autocomplete", (e) => this.onKeyUp(e));
    this.el.on("blur.autocomplete", () => this.onBlur());
    this.el.on("focus.autocomplete", () => this.onFocus());
    this.el.on("change.autocomplete", (e) => this.onKeyUp(e));
    this.el.on("input.autocomplete", (e) => this.onKeyUp(e));
  }
  onFocus() {
    if (this.disabled) {
      return;
    }
    this.fixPosition();
    if (this.el.val().length >= this.options.minChars) {
      this.onValueChange();
    }
  }
  onBlur() {
    const options = this.options;
    const query = this.getQuery(this.el.val());
    this.blurTimeoutId = setTimeout(() => {
      this.hide();
      if (this.selection && this.currentValue !== query) {
        options.onInvalidateSelection?.call(this.element);
      }
    }, 200);
  }
  abortAjax() {
    if (this.currentRequest) {
      this.currentRequest.abort();
      this.currentRequest = null;
    }
  }
  setOptions(suppliedOptions) {
    const options = { ...this.options, ...suppliedOptions };
    this.isLocal = Array.isArray(options.lookup);
    if (this.isLocal) {
      options.lookup = this.verifySuggestionsFormat(options.lookup);
    }
    options.orientation = this.validateOrientation(options.orientation, "bottom");
    this.$container.css({
      "max-height": `${options.maxHeight}px`,
      width: `${options.width}px`,
      "z-index": options.zIndex
    });
    this.options = options;
  }
  clearCache() {
    this.cachedResponse = {};
    this.badQueries = [];
  }
  clear() {
    this.clearCache();
    this.currentValue = "";
    this.suggestions = [];
  }
  disable() {
    this.disabled = true;
    if (this.onChangeTimeout) {
      clearTimeout(this.onChangeTimeout);
    }
    this.abortAjax();
  }
  enable() {
    this.disabled = false;
  }
  fixPosition() {
    const $container = this.$container;
    const containerParent = $container.parent().get(0);
    if (containerParent !== document.body && !this.options.forceFixPosition) {
      return;
    }
    let orientation = this.options.orientation;
    const containerHeight = $container.outerHeight() ?? 0;
    const height = this.el.outerHeight() ?? 0;
    const offset = this.el.offset() ?? { top: 0, left: 0 };
    const styles = {
      top: offset.top,
      left: offset.left
    };
    if (orientation === "auto") {
      const viewPortHeight = $(window).height() ?? 0;
      const scrollTop = $(window).scrollTop() ?? 0;
      const topOverflow = -scrollTop + offset.top - containerHeight;
      const bottomOverflow = scrollTop + viewPortHeight - (offset.top + height + containerHeight);
      orientation = Math.max(topOverflow, bottomOverflow) === topOverflow ? "top" : "bottom";
    }
    styles.top += orientation === "top" ? -containerHeight : height;
    if (containerParent !== document.body && containerParent !== void 0) {
      const opacity = $container.css("opacity");
      if (!this.visible) {
        $container.css("opacity", 0).show();
      }
      const parentOffsetDiff = $container.offsetParent().offset() ?? { top: 0, left: 0 };
      styles.top -= parentOffsetDiff.top;
      styles.top += containerParent.scrollTop;
      styles.left -= parentOffsetDiff.left;
      if (!this.visible) {
        $container.css("opacity", opacity).hide();
      }
    }
    if (this.options.width === "auto") {
      styles.width = `${this.el.outerWidth() ?? 0}px`;
    }
    $container.css(styles);
  }
  isCursorAtEnd() {
    const valLength = this.el.val().length;
    const { selectionStart } = this.element;
    return typeof selectionStart === "number" ? selectionStart === valLength : true;
  }
  onKeyPress(e) {
    if (!this.disabled && !this.visible && e.which === keys.DOWN && this.currentValue) {
      this.suggest();
      return;
    }
    if (this.disabled || !this.visible) {
      return;
    }
    switch (e.which) {
      case keys.ESC:
        this.el.val(this.currentValue);
        this.hide();
        break;
      case keys.RIGHT:
        if (this.hint && this.options.onHint && this.isCursorAtEnd()) {
          this.selectHint();
          break;
        }
        return;
      case keys.TAB:
        if (this.hint && this.options.onHint) {
          this.selectHint();
          return;
        }
        if (this.selectedIndex === -1) {
          this.hide();
          return;
        }
        this.select(this.selectedIndex);
        if (this.options.tabDisabled === false) {
          return;
        }
        break;
      case keys.RETURN:
        if (this.selectedIndex === -1) {
          this.hide();
          return;
        }
        this.select(this.selectedIndex);
        break;
      case keys.UP:
        this.moveUp();
        break;
      case keys.DOWN:
        this.moveDown();
        break;
      default:
        return;
    }
    e.stopImmediatePropagation();
    e.preventDefault();
  }
  onKeyUp(e) {
    if (this.disabled) {
      return;
    }
    if (e.which === keys.UP || e.which === keys.DOWN) {
      return;
    }
    if (this.onChangeTimeout) {
      clearTimeout(this.onChangeTimeout);
    }
    if (this.currentValue !== this.el.val()) {
      this.findBestHint();
      if (this.options.deferRequestBy > 0) {
        this.onChangeTimeout = setTimeout(
          () => this.onValueChange(),
          this.options.deferRequestBy
        );
      } else {
        this.onValueChange();
      }
    }
  }
  onValueChange() {
    if (this.ignoreValueChange) {
      this.ignoreValueChange = false;
      return;
    }
    const options = this.options;
    const value = this.el.val();
    const query = this.getQuery(value);
    if (this.selection && this.currentValue !== query) {
      this.selection = null;
      options.onInvalidateSelection?.call(this.element);
    }
    if (this.onChangeTimeout) {
      clearTimeout(this.onChangeTimeout);
    }
    this.currentValue = value;
    this.selectedIndex = -1;
    if (options.triggerSelectOnValidInput && this.isExactMatch(query)) {
      this.select(0);
      return;
    }
    if (query.length < options.minChars) {
      this.hide();
    } else {
      this.getSuggestions(query);
    }
  }
  isExactMatch(query) {
    const { suggestions } = this;
    return suggestions.length === 1 && suggestions[0].value.toLowerCase() === query.toLowerCase();
  }
  getQuery(value) {
    const { delimiter } = this.options;
    if (!delimiter) {
      return value;
    }
    const parts = value.split(delimiter);
    return parts[parts.length - 1].trim();
  }
  getSuggestionsLocal(query) {
    const options = this.options;
    const queryLowerCase = query.toLowerCase();
    const filter = options.lookupFilter;
    const limit = parseInt(options.lookupLimit, 10);
    const lookup = options.lookup;
    const matched = lookup.filter((suggestion) => filter(suggestion, query, queryLowerCase));
    return {
      suggestions: limit && matched.length > limit ? matched.slice(0, limit) : matched
    };
  }
  getSuggestions(q) {
    const options = this.options;
    let serviceUrl = options.serviceUrl;
    let response;
    let cacheKey;
    options.params[options.paramName] = q;
    if (options.onSearchStart.call(this.element, options.params) === false) {
      return;
    }
    const params = options.ignoreParams ? null : options.params;
    if (typeof options.lookup === "function") {
      options.lookup(q, (data) => {
        const suggestions = this.verifySuggestionsFormat(data.suggestions);
        this.suggestions = suggestions;
        options.onSearchComplete.call(this.element, q, suggestions);
        this.suggest();
      });
      return;
    }
    if (this.isLocal) {
      response = this.getSuggestionsLocal(q);
    } else {
      if (typeof serviceUrl === "function") {
        serviceUrl = serviceUrl.call(this.element, q);
      }
      cacheKey = `${serviceUrl}?${$.param(params ?? {})}`;
      response = this.cachedResponse[cacheKey];
    }
    if (response && Array.isArray(response.suggestions)) {
      this.suggestions = response.suggestions;
      options.onSearchComplete.call(this.element, q, response.suggestions);
      this.suggest();
    } else if (!this.isBadQuery(q)) {
      this.abortAjax();
      const ajaxSettings = {
        url: serviceUrl,
        data: params ?? void 0,
        type: options.type,
        dataType: options.dataType,
        ...options.ajaxSettings
      };
      this.currentRequest = $.ajax(ajaxSettings).done((data) => {
        this.currentRequest = null;
        const result = options.transformResult(data, q);
        result.suggestions = this.verifySuggestionsFormat(result.suggestions);
        options.onSearchComplete.call(this.element, q, result.suggestions);
        this.processResponse(result, q, cacheKey);
      }).fail((jqXHR, textStatus, errorThrown) => {
        options.onSearchError.call(this.element, q, jqXHR, textStatus, errorThrown);
      });
    } else {
      options.onSearchComplete.call(this.element, q, []);
    }
  }
  isBadQuery(q) {
    if (!this.options.preventBadQueries) {
      return false;
    }
    return this.badQueries.some((bad) => q.indexOf(bad) === 0);
  }
  hide() {
    if (this.options.onHide && this.visible) {
      this.options.onHide.call(this.element, this.$container);
    }
    this.visible = false;
    this.selectedIndex = -1;
    if (this.onChangeTimeout) {
      clearTimeout(this.onChangeTimeout);
    }
    this.$container.hide();
    this.onHint(null);
  }
  groupSuggestionsByCategory(suggestions, key) {
    const groups = /* @__PURE__ */ new Map();
    for (const s of suggestions) {
      const cat = s.data[key];
      const arr = groups.get(cat);
      if (arr) {
        arr.push(s);
      } else {
        groups.set(cat, [s]);
      }
    }
    return Array.from(groups.values()).flat();
  }
  suggest() {
    if (!this.suggestions.length) {
      if (this.options.showNoSuggestionNotice) {
        this.noSuggestions();
      } else {
        this.hide();
      }
      return;
    }
    const options = this.options;
    const { groupBy, formatResult: formatResultFn, beforeRender } = options;
    const value = this.getQuery(this.currentValue);
    const className = this.classes.suggestion;
    const classSelected = this.classes.selected;
    const container = this.$container;
    if (options.triggerSelectOnValidInput && this.isExactMatch(value)) {
      this.select(0);
      return;
    }
    if (groupBy) {
      this.suggestions = this.groupSuggestionsByCategory(this.suggestions, groupBy);
    }
    let category;
    const formatGroupFn = (suggestion) => {
      const currentCategory = suggestion.data[groupBy];
      if (category === currentCategory) {
        return "";
      }
      category = currentCategory;
      return options.formatGroup(suggestion, category);
    };
    const html = this.suggestions.map((suggestion, i) => {
      const group = groupBy ? formatGroupFn(suggestion) : "";
      return `${group}<div class="${className}" data-index="${i}">${formatResultFn(suggestion, value, i)}</div>`;
    }).join("");
    this.adjustContainerWidth();
    this.$noSuggestionsContainer.detach();
    container.html(html);
    beforeRender?.call(this.element, container, this.suggestions);
    this.fixPosition();
    container.show();
    if (options.autoSelectFirst) {
      this.selectedIndex = 0;
      container.scrollTop(0);
      container.children(`.${className}`).first().addClass(classSelected);
    }
    this.visible = true;
    this.findBestHint();
  }
  noSuggestions() {
    const { beforeRender } = this.options;
    const container = this.$container;
    this.adjustContainerWidth();
    this.$noSuggestionsContainer.detach();
    container.empty().append(this.$noSuggestionsContainer);
    beforeRender?.call(this.element, container, this.suggestions);
    this.fixPosition();
    container.show();
    this.visible = true;
  }
  adjustContainerWidth() {
    const { width } = this.options;
    if (width === "auto") {
      const w = this.el.outerWidth() ?? 0;
      this.$container.css("width", w > 0 ? w : 300);
    } else if (width === "flex") {
      this.$container.css("width", "");
    }
  }
  findBestHint() {
    const value = this.el.val().toLowerCase();
    if (!value) {
      return;
    }
    const bestMatch = this.suggestions.find((s) => s.value.toLowerCase().indexOf(value) === 0) ?? null;
    this.onHint(bestMatch);
  }
  onHint(suggestion) {
    const { onHint: onHintCallback } = this.options;
    const hintValue = suggestion ? this.currentValue + suggestion.value.substr(this.currentValue.length) : "";
    if (this.hintValue !== hintValue) {
      this.hintValue = hintValue;
      this.hint = suggestion;
      onHintCallback?.call(this.element, hintValue);
    }
  }
  verifySuggestionsFormat(suggestions) {
    if (suggestions.length && typeof suggestions[0] === "string") {
      return suggestions.map((value) => ({ value, data: null }));
    }
    return suggestions.map(
      (s) => typeof s.value === "string" ? s : { ...s, value: String(s.value) }
    );
  }
  validateOrientation(orientation, fallback) {
    const normalized = (orientation || "").trim().toLowerCase();
    if (normalized === "auto" || normalized === "top" || normalized === "bottom") {
      return normalized;
    }
    return fallback;
  }
  processResponse(result, originalQuery, cacheKey) {
    const options = this.options;
    result.suggestions = this.verifySuggestionsFormat(result.suggestions);
    if (!options.noCache) {
      this.cachedResponse[cacheKey] = result;
      if (options.preventBadQueries && !result.suggestions.length && originalQuery) {
        this.badQueries.push(originalQuery);
      }
    }
    if (originalQuery !== this.getQuery(this.currentValue)) {
      return;
    }
    this.suggestions = result.suggestions;
    this.suggest();
  }
  activate(index) {
    const selected = this.classes.selected;
    const container = this.$container;
    const children = container.find(`.${this.classes.suggestion}`);
    container.find(`.${selected}`).removeClass(selected);
    this.selectedIndex = index;
    if (this.selectedIndex !== -1 && children.length > this.selectedIndex) {
      const activeItem = children.get(this.selectedIndex);
      $(activeItem).addClass(selected);
      return activeItem;
    }
    return null;
  }
  selectHint() {
    this.select(this.suggestions.indexOf(this.hint));
  }
  select(i) {
    this.hide();
    this.onSelect(i);
  }
  moveUp() {
    if (this.selectedIndex === -1) {
      return;
    }
    if (this.selectedIndex === 0) {
      this.$container.children(`.${this.classes.suggestion}`).first().removeClass(this.classes.selected);
      this.selectedIndex = -1;
      this.ignoreValueChange = false;
      this.el.val(this.currentValue);
      this.findBestHint();
      return;
    }
    this.adjustScroll(this.selectedIndex - 1);
  }
  moveDown() {
    if (this.selectedIndex === this.suggestions.length - 1) {
      return;
    }
    this.adjustScroll(this.selectedIndex + 1);
  }
  adjustScroll(index) {
    const activeItem = this.activate(index);
    if (!activeItem) {
      return;
    }
    const heightDelta = $(activeItem).outerHeight() ?? 0;
    const offsetTop = activeItem.offsetTop;
    const container = this.$container;
    const upperBound = container.scrollTop() ?? 0;
    const lowerBound = upperBound + this.options.maxHeight - heightDelta;
    if (offsetTop < upperBound) {
      container.scrollTop(offsetTop);
    } else if (offsetTop > lowerBound) {
      container.scrollTop(offsetTop - this.options.maxHeight + heightDelta);
    }
    if (!this.options.preserveInput) {
      this.ignoreValueChange = true;
      this.el.val(this.getValue(this.suggestions[index].value));
    }
    this.onHint(null);
  }
  onSelect(index) {
    const onSelectCallback = this.options.onSelect;
    const suggestion = this.suggestions[index];
    this.currentValue = this.getValue(suggestion.value);
    if (this.currentValue !== this.el.val() && !this.options.preserveInput) {
      this.el.val(this.currentValue);
    }
    this.onHint(null);
    this.suggestions = [];
    this.selection = suggestion;
    onSelectCallback?.call(this.element, suggestion);
  }
  getValue(value) {
    const { delimiter } = this.options;
    if (!delimiter) {
      return value;
    }
    const currentValue = this.currentValue;
    const parts = currentValue.split(delimiter);
    if (parts.length === 1) {
      return value;
    }
    return currentValue.substr(0, currentValue.length - parts[parts.length - 1].length) + value;
  }
  dispose() {
    this.el.off(".autocomplete").removeData("autocomplete");
    if (this.fixPositionCapture) {
      $(window).off("resize.autocomplete", this.fixPositionCapture);
    }
    this.$container.remove();
  }
};
_Autocomplete.defaults = defaults;
_Autocomplete.utils = utils;
var Autocomplete = _Autocomplete;

// src/jquery-plugin.ts
var dataKey = "autocomplete";
function installAutocomplete($2) {
  setJQuery($2);
  $2.Autocomplete = Autocomplete;
  $2.fn.devbridgeAutocomplete = function(options, args) {
    if (!arguments.length) {
      return this.first().data(dataKey);
    }
    return this.each(function() {
      const inputElement = $2(this);
      let instance = inputElement.data(dataKey);
      if (typeof options === "string") {
        if (instance && typeof instance[options] === "function") {
          instance[options](
            args
          );
        }
      } else {
        if (instance && instance.dispose) {
          instance.dispose();
        }
        instance = new Autocomplete(this, options);
        inputElement.data(dataKey, instance);
      }
    });
  };
  if (!$2.fn.autocomplete) {
    $2.fn.autocomplete = $2.fn.devbridgeAutocomplete;
  }
}

// src/index.ts
installAutocomplete(jQuery);
export {
  Autocomplete
};
