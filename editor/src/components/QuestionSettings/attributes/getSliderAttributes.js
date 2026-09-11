import { Input, Select, ToggleButtons } from 'components/UIComponents'
import { THUMB_TYPES } from 'helpers'
import { getYesNoOptions } from 'helpers/options'

export const getSliderAttributes = () => ({
  USE_SLIDER_LAYOUT: {
    component: ToggleButtons,
    attributePath: 'attributes.slider_layout',
    props: {
      id: 'use-slider-layout',
      labelText: t('Use slider layout'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  ORIENTATION: {
    component: ToggleButtons,
    attributePath: 'attributes.slider_orientation',
    props: {
      id: 'orientation',
      labelText: t('Orientation'),
      toggleOptions: [
        { name: t('Horizontal'), value: '0' },
        { name: t('Vertical'), value: '1' },
      ],
      defaultValue: '0',
    },
  },
  SLIDER_MINIMUM_VALUE: {
    component: Input,
    attributePath: 'attributes.slider_min',
    props: {
      id: 'slider-minimum-value',
      labelText: t('Slider minimum value'),
      type: 'number',
    },
  },
  SLIDER_MAXIMUM_VALUE: {
    component: Input,
    attributePath: 'attributes.slider_max',
    props: {
      id: 'slider-maximum-value',
      labelText: t('Slider maximum value'),
      type: 'number',
    },
  },
  SLIDER_ACCURACY: {
    component: Input,
    attributePath: 'attributes.slider_accuracy',
    props: {
      id: 'slider-accuracy',
      labelText: t('Step value'),
      value: 1,
      type: 'number',
    },
  },
  DISPLAY_SLIDER_MIN_AND_MAX_VALUE: {
    component: ToggleButtons,
    attributePath: 'attributes.slider_showminmax',
    props: {
      id: 'display-slider-min-and-max-value',
      labelText: t('Display min and max value'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  SLIDER_LEFT_RIGHT_TEXT_SEPARATOR: {
    component: Input,
    attributePath: 'attributes.slider_separator',
    props: {
      id: 'slider-left-right-text-separator',
      labelText: t('Slider left/right text separator'),
    },
  },
  SLIDER_STARTS_AT_THE_MIDDLE_POSITION: {
    component: Select,
    attributePath: 'attributes.slider_middlestart',
    props: {
      id: 'slider-starts-at-the-middle-position',
      labelText: t('Start value'),
      options: [
        { label: t('Center position'), value: '1' },
        { label: t('Initial value'), value: '0' },
      ],
      defaultValue: { label: t('Initial value'), value: '0' },
    },
  },
  SLIDER_INITIAL_VALUE: {
    component: Input,
    attributePath: 'attributes.slider_default',
    props: {
      id: 'slider-initial-value',
      labelText: t('Slider initial value'),
      type: 'number',
    },
  },
  SLIDER_INITIAL_VALUE_SET_AT_STARTS: {
    component: ToggleButtons,
    attributePath: 'attributes.slider_default_set',
    props: {
      id: 'slider-initial-value-set-at-starts',
      labelText: t('Use initial value as answer'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '1',
    },
  },
  ALLOW_SLIDER_RESET: {
    component: ToggleButtons,
    attributePath: 'attributes.slider_reset',
    props: {
      id: 'allow-slider-reset',
      labelText: t('Allow slider reset'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  REVERSE_THE_SLIDER_DIRECTION: {
    component: ToggleButtons,
    attributePath: 'attributes.slider_reversed',
    props: {
      name: 'reverse-the-slider-direction',
      id: 'reverse-the-slider-direction',
      labelText: t('Slider direction'),
      toggleOptions: [
        { name: t('Default'), value: '0' },
        { name: t('Reverse'), value: '1' },
      ],
      defaultValue: '0',
    },
  },
  HANDLE_SHAPE: {
    component: Select,
    attributePath: 'attributes.slider_handle',
    props: {
      id: 'handle-shape',
      labelText: t('Handle shape'),
      options: [
        {
          label: t('Circle'),
          value: THUMB_TYPES.CIRCLE,
        },
        {
          label: t('Square'),
          value: THUMB_TYPES.SQUARE,
        },
        {
          label: t('Triangle'),
          value: THUMB_TYPES.TRIANGLE,
        },
        {
          label: t('Custom'),
          value: THUMB_TYPES.CUSTOM,
        },
      ],
    },
  },
  CUSTOM_HANDLE_UNICODE_CODE: {
    component: Input,
    attributePath: 'attributes.slider_custom_handle',
    props: {
      id: 'custom-handle-unicode-code',
      labelText: t('Custom handle Unicode code'),
      dataTestId: 'custom-handle-unicode-code',
    },
  },
})
