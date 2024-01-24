/**
 * Define custom SelectionAdapter
 */
$.fn.select2.amd.define(
  'select2/selection/languagesWidgetSelectionAdapter',
  [
    'jquery',
    './multiple',
    'select2/selection/eventRelay',
    'select2/selection/search',
    '../utils'
  ],
  function ($, MultipleSelection, EventRelay, SelectionSearch, Utils) {
    function LanguagesWidgetSelectionAdapter($element, options) {
      LanguagesWidgetSelectionAdapter.__super__.constructor.apply(this, arguments);
      //this.baseLanguage = this.options.get('baselanguage');
      var baseLanguageElementSelector = this.options.get('baselanguage_selector');
      if (baseLanguageElementSelector) {
        this.baseLanguageElement = $(baseLanguageElementSelector);
        this.baseLanguage = this.baseLanguageElement.val();
      }
    }

    Utils.Extend(LanguagesWidgetSelectionAdapter, MultipleSelection);

    LanguagesWidgetSelectionAdapter.prototype.update = function (data) {
      this.clear();

      // Clear base language options
      if (this.baseLanguageElement) {
        this.baseLanguage = this.baseLanguageElement.val();
        this.baseLanguageElement.find('*').remove().trigger('change');
      }

      if (data.length === 0) {
        return;
      }

      var $selections = [];

      var $first = null;

      for (var d = 0; d < data.length; d++) {
        var selection = data[d];
        selection.isBaseLanguage = this.baseLanguage && selection.id == this.baseLanguage;

        var $selection = this.selectionContainer();
        var formatted = this.display(selection, $selection);

        $selection.append(formatted);
        $selection.prop('title', selection.title || selection.text);

        $selection.data('data', selection);

        // Add base language options
        if (this.baseLanguageElement) {
          var newOption = new Option(selection.title || selection.text, selection.id, false, false);
          this.baseLanguageElement.append(newOption);
        }

        // Skip the base language
        if (selection.isBaseLanguage) {
          $first = $selection.clone(true);
          $first.data('isBaseLanguage', true);
          $first.addClass("select2-selection__baselanguage");
          continue;
        }

        $selections.push($selection);
      }

      if ($first) {
        $selections.unshift($first);
      }

      if (this.baseLanguageElement) {
        if (this.baseLanguage) {
          this.baseLanguageElement.val(this.baseLanguage);
        }
        this.baseLanguageElement.trigger('change')
      }

      var $rendered = this.$selection.find('.select2-selection__rendered');

      //Utils.appendMany($rendered, $selections); // /Utils.appendMany (from v4.0.13) no longer exists
      $rendered.append($selections); // New method since 4.1.0-rc.0
    };
    LanguagesWidgetSelectionAdapter.prototype.bind = function (container, $container) {
      var self = this;

      MultipleSelection.__super__.bind.apply(this, arguments);

      this.$selection.on('click', function (evt) {
        self.trigger('toggle', {
          originalEvent: evt
        });
      });

      this.$selection.on(
        'click',
        '.select2-selection__choice__remove',
        function (evt) {
          evt.stopPropagation();

          // Ignore the event if it is disabled
          if (self.options.get('disabled')) {
            return;
          }

          var $remove = $(this);
          var $selection = $remove.parent();

          var data = $selection.data('data');

          self.trigger('unselect', {
            originalEvent: evt,
            data: data
          });
        }
      );

      if (this.baseLanguageElement) {
        this.baseLanguageElement.off('change.langwidget').on('change.langwidget', function () {
          var selectedLanguage = $(this).val();
          if (selectedLanguage && self.baseLanguage != selectedLanguage) {
            self.$element.trigger("change.select2");
          }
        });
      }
    };

    var decoratedAdapter = Utils.Decorate(LanguagesWidgetSelectionAdapter, EventRelay);
    decoratedAdapter = Utils.Decorate(decoratedAdapter, SelectionSearch);
    return decoratedAdapter;
  }
);

/**
 * Define custom DataAdapter
 */
$.fn.select2.amd.define(
  'select2/data/languagesWidgetDataAdapter',
  [
    './select',
    '../utils',
    'jquery'
  ],
  function (SelectAdapter, Utils, $) {
    function LanguagesWidgetDataAdapter($element, options) {
      LanguagesWidgetDataAdapter.__super__.constructor.call(this, $element, options);

      this.messages = options.get('messages');
    }

    Utils.Extend(LanguagesWidgetDataAdapter, SelectAdapter);

    LanguagesWidgetDataAdapter.prototype.unselect = function (data) {
      var self = this;

      if (!this.$element.prop('multiple')) {
        return;
      }

      data.selected = false;

      if (data.isBaseLanguage) {
        LS.LsGlobalNotifier.createAlert(this.messages.cannotRemoveBaseLanguage, 'danger', {showCloseButton: true});
        return;
      } else {
        $.fn.bsconfirm(
          this.messages.removeLanguageConfirmation,
          {
            confirm_cancel: this.messages.cancel,
            confirm_ok: this.messages.delete,
          },
          function () {
            $('#identity__bsconfirmModal').modal('hide');

            if ($(data.element).is('option')) {
              data.element.selected = false;
              self.$element.trigger('change');
              return;
            }

            self.current(function (currentData) {
              var val = [];
              for (var d = 0; d < currentData.length; d++) {
                var id = currentData[d].id;
                if (id !== data.id && $.inArray(id, val) === -1) {
                  val.push(id);
                }
              }

              self.$element.val(val);
              self.$element.trigger('change');
            });
          }
        );
      }
    };

    return LanguagesWidgetDataAdapter;
  }
);
