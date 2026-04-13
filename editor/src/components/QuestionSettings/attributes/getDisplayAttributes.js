import {
  getVisualizationOptions,
  getSliderLayoutOptions,
  getOrderOptions,
  getDisplayButtonTypeOptions,
  getColumnWidthOptions,
  getLabelWrapperWidthOptions,
  getYesNoOptions,
  YESNO_LONGSTRING,
  getOnOffOptions,
} from 'helpers/options'
import { Input, Select, ToggleButtons } from 'components/UIComponents'

import { ImageAttributes } from '../attributes'

export const getDisplayAttributes = () => ({
  IMAGE_SETTINGS: {
    component: ImageAttributes,
    attributePath: 'attributes.image',
    props: {},
  },
  PAGE_BREAK_IN_PRINTABLE_VIEW: {
    component: ToggleButtons,
    attributePath: 'attributes.page_break',
    props: {
      id: 'insert-page-break-in-printable-view',
      labelText: t('Page break in printable view'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  INPUT_ON_DEMAND: {
    component: ToggleButtons,
    attributePath: 'attributes.input_on_demand',
    props: {
      labelText: t('Input on demand'),
      id: 'input-on-demand',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
    hidden: !process.env.REACT_APP_DEV_MODE,
  },
  USE_SLIDER_LAYOUT: {
    component: Select,
    attributePath: 'attributes.slider_rating',
    props: {
      dataTestId: 'use_slider_layout',
      labelText: t('Use slider layout'),
      options: getSliderLayoutOptions(),
    },
  },
  DISPLAY_TYPE: {
    component: ToggleButtons,
    attributePath: 'attributes.display_type',
    props: {
      labelText: t('Display type'),
      id: 'display-type',
      toggleOptions: Object.values(getDisplayButtonTypeOptions()),
      defaultValue: '0',
    },
  },
  MAXIMUM_DATE: {
    component: Input,
    attributePath: 'attributes.date_max',
    props: {
      type: 'date',
      labelText: t('Max date'),
      id: 'maximum-date',
      inputClass: 'selected',
      showClassWhenValue: true,
    },
  },
  MINIMUM_DATE: {
    component: Input,
    attributePath: 'attributes.date_min',
    props: {
      type: 'date',
      labelText: t('Min date'),
      id: 'minimum-date',
      inputClass: 'selected',
      showClassWhenValue: true,
    },
  },
  MONTH_DISPLAY_STYLE: {
    component: ToggleButtons,
    attributePath: 'attributes.dropdown_dates_month_style',
    props: {
      labelText: t('Month display style'),
      id: 'month-display-style',
      toggleOptions: [
        { name: t('Short'), value: '0' },
        { name: t('Full'), value: '1' },
        { name: t('Numbers'), value: '2' },
      ],
      defaultValue: '0',
    },
  },
  DISPLAY_DROPDOWN_BOXES: {
    component: ToggleButtons,
    attributePath: 'attributes.dropdown_dates',
    props: {
      labelText: t('Display dropdown boxes'),
      id: 'display-dropdown-boxes',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  SHOW_PLATFORM_INFORMATION: {
    component: ToggleButtons,
    attributePath: 'attributes.add_platform_info',
    props: {
      labelText: t('Show platform information'),
      id: 'show-platform-information',
      toggleOptions: getYesNoOptions(YESNO_LONGSTRING),
      defaultValue: 'no',
    },
  },
  MINIMUM_VALUE: {
    component: Input,
    attributePath: 'attributes.multiflexible_min',
    props: {
      labelText: t('Minimum value'),
      id: 'minimum-value',
    },
  },
  MAXIMUM_VALUE: {
    component: Input,
    attributePath: 'attributes.multiflexible_max',
    props: {
      labelText: t('Maximum value'),
      id: 'maximum-value',
    },
  },
  ANSWER_OPTIONS_ORDER: {
    component: Select,
    attributePath: 'attributes.answer_order',
    props: {
      dataTestId: 'answer-options-order',
      labelText: t('Answer options order'),
      options: getOrderOptions(),
    },
  },
  SUBQUESTION_OPTIONS_ORDER: {
    component: Select,
    attributePath: 'attributes.subquestion_order',
    props: {
      dataTestId: 'subquestions-order',
      labelText: t('Subquestions order'),
      options: getOrderOptions(),
    },
  },
  KEEP_CODE_ORDER: {
    component: Input,
    attributePath: 'attributes.keep_codes_order',
    props: {
      dataTestId: 'keep-code-order',
      labelText: t('Keep codes at original positions'),
      helpText: t(
        'Semicolon-separated list of codes that keep their original database position when items are randomized.'
      ),
    },
  },
  VISUALIZATION: {
    component: Select,
    attributePath: 'attributes.visualize',
    props: {
      labelText: t('Visualization'),
      dataTestId: 'visualization',
      options: getVisualizationOptions(),
    },
    hidden: !process.env.REACT_APP_DEV_MODE,
  },
  SAME_HEIGHT_FOR_ALL_ANSWER_OPTIONS: {
    component: ToggleButtons,
    attributePath: 'attributes.samechoiceheight',
    props: {
      id: 'same-height-for-all-answer-options',
      labelText: t('Same height for all answer options'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '1',
    },
  },
  SAME_HEIGHT_FOR_LIST: {
    component: ToggleButtons,
    attributePath: 'attributes.samelistheight',
    props: {
      id: 'same-height-for-lists',
      labelText: t('Same height for lists'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '1',
    },
  },
  ANSWER_PREFIX: {
    component: Input,
    attributePath: 'attributes.prefix',
    props: {
      dataTestId: 'answer-prefix',
      labelText: t('Answer prefix'),
    },
  },
  ANSWER_SUFFIX: {
    component: Input,
    attributePath: 'attributes.suffix',
    props: {
      dataTestId: 'answer-suffix',
      labelText: t('Answer suffix'),
    },
  },
  CHOICE_COLUMN_WIDTH: {
    component: Select,
    attributePath: 'attributes.choice_input_columns',
    props: {
      dataTestId: 'choice-column-width',
      labelText: t('Choice column width'),
      options: getColumnWidthOptions(),
    },
  },
  REVERSE_ANSWER_ORDER: {
    component: ToggleButtons,
    attributePath: 'attributes.reverse',
    props: {
      id: 'reverse-answer-order',
      labelText: t('Reverse answer order'),
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  SHOW_JAVASCRIPT_ALERT: {
    component: ToggleButtons,
    attributePath: 'attributes.showpopups',
    props: {
      id: 'show-javascript-alert',
      labelText: t('Show javascript alert'),
      toggleOptions: getOnOffOptions(),
      defaultValue: '1',
    },
  },
  SHOW_HANDLE: {
    component: ToggleButtons,
    attributePath: 'attributes.show_handle',
    props: {
      id: 'show-handle',
      name: 'show-handle',
      labelText: t('Show handle'),
      toggleOptions: getYesNoOptions(YESNO_LONGSTRING),
      defaultValue: 'no',
    },
  },
  SHOW_NUMBER: {
    component: ToggleButtons,
    attributePath: 'attributes.show_number',
    props: {
      id: 'show-number',
      name: 'show-number',
      labelText: t('Show number'),
      toggleOptions: getYesNoOptions(YESNO_LONGSTRING),
      defaultValue: 'no',
    },
  },
  WITHOUT_REORDER: {
    component: ToggleButtons,
    attributePath: 'attributes.only_pull',
    props: {
      id: 'without-reorder',
      name: 'without-reorder',
      labelText: t('Without reorder'),
      toggleOptions: getYesNoOptions(YESNO_LONGSTRING),
      defaultValue: 'no',
    },
  },

  // todo: check this
  FIX_WIDTH: {
    component: Input,
    attributePath: 'attributes.fix_width',
    props: {
      type: 'number',
      dataTestId: 'fix-width',
      labelText: t('Fix width'),
    },
  },
  // todo: check this
  FIX_HEIGHT: {
    component: Input,
    attributePath: 'attributes.fix_height',
    props: {
      type: 'number',
      dataTestId: 'fix-height',
      labelText: t('Fix height'),
    },
  },
  // todo: check this
  CROP_OR_RESIZE: {
    component: ToggleButtons,
    attributePath: 'attributes.crop_or_resize',
    props: {
      id: 'crop-or-resize',
      labelText: t('Crop or resize'),
      toggleOptions: [
        { name: t('Crop'), value: 'crop' },
        { name: t('Resize'), value: 'resize' },
      ],
      defaultValue: 'resize',
    },
  },
  // todo: check this
  KEEP_ASPECT_RATIO: {
    component: ToggleButtons,
    attributePath: 'attributes.keep_aspect',
    props: {
      id: 'keep-aspect-ratio',
      labelText: t('Keep aspect ratio'),
      toggleOptions: getYesNoOptions(YESNO_LONGSTRING),
      defaultValue: 'no',
    },
  },
  // todo: check this
  HORIZONTAL_SCROLL: {
    component: ToggleButtons,
    attributePath: 'attributes.horizontal_scroll',
    props: {
      id: 'horizontal-scroll',
      labelText: t('Horizontal scroll'),
      toggleOptions: getYesNoOptions(YESNO_LONGSTRING),
      defaultValue: 'no',
    },
  },
  CATEGORY_SERARATOR: {
    component: Input,
    attributePath: 'attributes.category_separator',
    props: {
      id: 'category-separator',
      labelText: t('Category separator'),
    },
  },
  PREFIX_FOR_LIST_ITEMS: {
    component: ToggleButtons,
    attributePath: 'attributes.dropdown_prefix',
    props: {
      id: 'prefix-for-list-items',
      labelText: t('Prefix for list items'),
      toggleOptions: [
        { name: t('None'), value: '0' },
        { name: t('Order - like 3)'), value: '1' },
      ],
      defaultValue: '0',
    },
  },
  HEIGHT_OF_DROPDOWN: {
    component: Input,
    attributePath: 'attributes.dropdown_size',
    props: {
      id: 'category-separator',
      labelText: t('Height of dropdown'),
      dataTestId: 'height-of-dropdown',
    },
  },
  LABEL_FOR_OTHER_OPTIONS: {
    component: Input,
    attributePath: 'attributes.other_replace_text',
    languageBased: true,
    props: {
      id: 'category-separator',
      labelText: t("Label for 'Other:' option"),
      dataTestId: 'label-for-other-option',
    },
  },
  POSITION_FOR_OTHER_OPTION: {
    component: Select,
    attributePath: 'attributes.other_position',
    props: {
      labelText: t("Position for 'Other:' option"),
      dataTestId: 'position-for-other-option',
      options: [
        {
          label: t('At beginning'),
          value: 'beginning',
        },

        {
          label: t('At end'),
          value: 'end',
        },
        {
          label: t('After specific answer option'),
          value: 'specific',
        },
      ],
    },
  },
  SUBQUESTION_TITLE: {
    component: Input,
    attributePath: 'attributes.other_position_code',
    props: {
      dataTestId: 'sub-question-title',
      labelText: t("Subquestion title for 'After specific subquestion'"),
    },
  },
  RANDOM_ORDER: {
    component: Select,
    attributePath: 'attributes.random_order',
    props: {
      id: 'random-order',
      labelText: t('Answer order'),
      options: [
        { label: t('Normal'), value: '0' },
        { label: t('Random'), value: '1' },
      ],
      defaultValue: { label: t('Normal'), value: '0' },
    },
  },
  ANSWER_CODE: {
    component: Input,
    attributePath: 'attributes.other_position_code',
    props: {
      dataTestId: 'answer-code',
      labelText: t("Answer code for 'After specific answer option'"),
    },
  },
  SUBQUESTION_WIDTH: {
    component: Input,
    attributePath: 'attributes.answer_width',
    props: {
      type: 'number',
      dataTestId: 'sub-question-width',
      labelText: t('(Sub-)question width'),
      max: 100,
      min: 0,
    },
  },
  REPEAT_HEADERS: {
    component: Input,
    attributePath: 'attributes.repeat_headings',
    props: {
      type: 'number',
      dataTestId: 'repeat-headers',
      labelText: t('Repeat headers'),
    },
  },
  GET_ORDER_FROM_PREVIEW_QUESTION: {
    component: Input,
    attributePath: 'attributes.parent_order',
    props: {
      dataTestId: 'get-order-from-preview-question',
      labelText: t('Get order from previous question'),
    },
  },
  HIDE_TIP: {
    component: ToggleButtons,
    attributePath: 'attributes.hide_tip',
    props: {
      id: 'hide-tip',
      labelText: t('Hide tip'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  SCALE_HEADER_A: {
    component: Input,
    attributeName: 'dualscale_headerA',
    attributePath: 'attributes.dualscale_headerA',
    props: {
      dataTestId: 'header-a-attribute-input',
      labelText: t('Header A'),
    },
  },
  SCALE_HEADER_B: {
    component: Input,
    attributeName: 'dualscale_headerB',
    attributePath: 'attributes.dualscale_headerB',
    props: {
      dataTestId: 'header-B-attribute-input',
      labelText: t('Header B'),
    },
  },
  ALWAYS_HIDE_THIS_QUESTION: {
    component: ToggleButtons,
    attributePath: 'attributes.hidden',
    props: {
      id: 'always-hide-this-question',
      labelText: t('Always hide this question'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  CSS_CLASSES: {
    component: Input,
    attributePath: 'attributes.cssclass',
    props: {
      dataTestId: 'css-classes-attribute-input',
      labelText: t('CSS class(es)'),
    },
  },
  CONDITION_HELP_FOR_PRINTABLE_SURVEY: {
    component: Input,
    attributePath: 'attributes.printable_help',
    languageBased: true,
    props: {
      dataTestId: 'condition-help-for-printable-survey',
      labelText: t('Condition help for printable survey'),
    },
  },
  TEXT_INPUT_WIDTH: {
    component: Select,
    attributePath: 'attributes.text_input_width',
    props: {
      dataTestId: 'text-input-width',
      labelText: t('Text input box width'),
      options: getColumnWidthOptions(),
    },
  },
  TEXT_INPUT_COLUMNS: {
    component: Select,
    attributePath: 'attributes.text_input_columns',
    props: {
      dataTestId: 'text-input-columns',
      labelText: t('Text input box width'),
      options: getColumnWidthOptions(),
    },
  },
  TEXT_INPUT_BOX_SIZE: {
    component: Input,
    attributePath: 'attributes.input_size',
    props: {
      type: 'number',
      dataTestId: 'text-input-box-size',
      labelText: t('Text input box size'),
    },
  },
  DISPLAY_ROWS: {
    component: Input,
    attributePath: 'attributes.display_rows',
    props: {
      type: 'number',
      dataTestId: 'display-rows',
      labelText: t('Display rows'),
    },
  },
  LABEL_WRAPPER_WIDTH: {
    component: Select,
    attributePath: 'attributes.label_input_columns',
    props: {
      labelText: t('Label wrapper width'),
      options: getLabelWrapperWidthOptions(),
    },
  },
  ANSWER_COLUMN_WIDTH: {
    component: Input,
    attributePath: 'attributes.answer_column_width',
    props: {
      dataTestId: 'answer-column-width',
      labelText: t('Answer column width'),
    },
  },
  USE_DROPDOWN_PRESENTATION: {
    component: ToggleButtons,
    attributePath: 'attributes.use_dropdown',
    props: {
      id: 'use-dropdown-presentation',
      labelText: t('Use dropdown presentation'),
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  DISPLAY_COLUMNS: {
    component: Input,
    attributePath: 'attributes.display_columns',
    props: {
      type: 'number',
      dataTestId: 'display-columns',
      labelText: t('Display columns'),
      min: 1,
    },
  },
  // todo: check this
  TEXT_INPUTS: {
    component: ToggleButtons,
    attributePath: 'attributes.input_boxes',
    props: {
      id: 'text-inputs',
      labelText: t('Text inputs'),
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  // todo: check this
  STEP_VALUE: {
    component: Input,
    attributePath: 'attributes.multiflexible_step',
    props: {
      type: 'number',
      dataTestId: 'step-value',
      labelText: t('Step value'),
    },
  },
  // todo: check this
  PLACEHOLDER_ANSWER: {
    component: Input,
    attributePath: 'attributes.placeholder',
    props: {
      dataTestId: 'placeholder-answer',
      labelText: t('Placeholder answer'),
    },
  },
  CHECKBOX_LAYOUT: {
    component: ToggleButtons,
    attributePath: 'attributes.multiflexible_checkbox',
    props: {
      id: 'checkbox-layout',
      labelText: t('Checkbox layout'),
      name: 'checkbox-layout',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  DROPDOWN_PREFIX_SUFFIX: {
    component: Input,
    attributePath: 'attributes.dropdown_prepostfix',
    props: {
      dataTestId: 'dropdown-prefix-suffix',
      labelText: t('Dropdown prefix/suffix'),
    },
  },
  DROPDOWN_SEPARATOR: {
    component: Input,
    attributePath: 'attributes.dropdown_separators',
    props: {
      dataTestId: 'dropdown-separator',
      labelText: t('Dropdown separator'),
    },
  },
})
