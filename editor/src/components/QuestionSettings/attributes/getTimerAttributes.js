import { Input, Select, ToggleButtons } from 'components/UIComponents'
import { getOnOffOptions, getYesNoOptions } from 'helpers/options'

export const getTimerAttributes = () => ({
  TIME_LIMIT_TIMER_CSS_STYLE: {
    component: Input,
    attributePath: 'attributes.time_limit_timer_style',
    props: {
      labelText: t('Time limit timer CSS style'),
      dataTestId: 'minimum-value',
    },
  },
  TIME_LIMIT_EXPIRY_MESSAGE_DISPLAY_TIME: {
    component: Input,
    attributePath: 'attributes.time_limit_message_delay',
    props: {
      labelText: t('Expiry message display time'),
      dataTestId: 'time-limit-expiry-message-display-time',
      rightInputText: 'sec',
      type: 'number',
    },
  },
  TIME_LIMIT_EXPIRY_MESSAGE: {
    component: Input,
    attributePath: 'attributes.time_limit_message',
    languageBased: true,
    props: {
      labelText: t('Expiry message'),
      as: 'textarea',
      type: 'textarea',
      role: 'textarea',
      rows: '4',
    },
  },
  TIME_LIMIT_MESSAGE_CSS_STYLE: {
    component: Input,
    attributePath: 'attributes.time_limit_message_style',
    props: {
      labelText: t('Time limit message CSS style'),
    },
  },
  FIRST_TIME_LIMIT: {
    component: ToggleButtons,
    attributePath: 'attributes.use_first_limit_warning',
    props: {
      labelText: t('1st time limit warning'),
      id: '1st-time-limit',
      dataTestId: '1st-time-limit',
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  FIRST_TIME_LIMIT_WARNING_TIMER: {
    component: Input,
    attributePath: 'attributes.time_limit_warning',
    get dependsOn() {
      return getTimerAttributes().FIRST_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('1st time limit warning timer'),
      rightInputText: 'sec',
      type: 'number',
    },
  },
  FIRST_TIME_LIMIT_WARNING_DISPLAY_TIMER: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_display_time',
    get dependsOn() {
      return getTimerAttributes().FIRST_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('1st time limit display timer'),
      dataTestId: 'first-time-limit-warning-timer',
      rightInputText: 'sec',
      type: 'number',
    },
  },
  FIRST_TIME_LIMIT_WARNING_MESSAGE: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_message',
    languageBased: true,
    get dependsOn() {
      return getTimerAttributes().FIRST_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('1st time limit message'),
    },
  },
  FIRST_TIME_LIMIT_TIMER_CSS_STYLE: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_style',
    get dependsOn() {
      return getTimerAttributes().FIRST_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('1st time limit message CSS style'),
    },
  },
  SECOND_TIME_LIMIT: {
    component: ToggleButtons,
    attributePath: 'attributes.use_second_limit_warning',
    props: {
      labelText: t('2nd time limit warning'),
      dataTestId: 'second-time-limit-warning',
      id: 'second-time-limit-warning',
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  SECOND_TIME_LIMIT_WARNING_TIMER: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_2',
    get dependsOn() {
      return getTimerAttributes().SECOND_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('2nd time limit warning timer'),
      dataTestId: 'second-time-limit-warning-timer',
      rightInputText: 'sec',
      type: 'number',
    },
  },
  SECOND_TIME_LIMIT_WARNING_DISPLAY_TIMER: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_2_display_time',
    get dependsOn() {
      return getTimerAttributes().SECOND_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('2nd time limit display timer'),
      dataTestId: 'second-time-limit-warning-timer',
      rightInputText: 'sec',
      type: 'number',
    },
  },
  SECOND_TIME_LIMIT_WARNING_MESSAGE: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_2_message',
    languageBased: true,
    get dependsOn() {
      return getTimerAttributes().SECOND_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('2nd time limit message'),
      as: 'textarea',
      type: 'textarea',
      role: 'textarea',
      rows: '4',
    },
  },
  SECOND_TIME_LIMIT_TIMER_CSS_STYLE: {
    component: Input,
    attributePath: 'attributes.time_limit_warning_2_style',
    get dependsOn() {
      return getTimerAttributes().SECOND_TIME_LIMIT
    },
    onDependsToggle: {
      onFalse: '',
    },
    props: {
      labelText: t('2nd time limit message CSS style'),
    },
  },
  TIME_LIMIT: {
    component: Input,
    attributePath: 'attributes.time_limit',
    props: {
      labelText: t('Time limit'),
      dataTestId: 'time-limit',
      rightInputText: 'sec',
      type: 'number',
    },
  },
  TIME_LIMIT_ACTION: {
    component: Select,
    attributePath: 'attributes.time_limit_action',
    props: {
      labelText: t('Time limit action'),
      options: [
        {
          label: t('Warn and move on'),
          value: '1',
        },
        {
          label: t('Move on without warning'),
          value: '2',
        },
        {
          label: t('Disable only'),
          value: '3',
        },
      ],
    },
  },
  TIME_LIMIT_DISABLE_NEXT: {
    component: ToggleButtons,
    attributePath: 'attributes.time_limit_disable_next',
    props: {
      labelText: t('Time limit disable next'),
      id: 'time-limit-disable-next',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  TIME_LIMIT_DISABLE_PREV: {
    component: ToggleButtons,
    attributePath: 'attributes.time_limit_disable_prev',
    props: {
      labelText: t('Time limit disable prev'),
      id: 'time-limit-disable-prev',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  TIME_LIMIT_COUNTDOWN_MESSAGE: {
    component: Input,
    attributePath: 'attributes.time_limit_countdown_message',
    languageBased: true,
    props: {
      labelText: t('Time limit countdown message'),
      as: 'textarea',
      type: 'textarea',
      role: 'textarea',
      rows: '4',
    },
  },
})
