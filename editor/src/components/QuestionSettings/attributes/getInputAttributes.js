import { Input, Select, ToggleButtons } from 'components/UIComponents'
import { getYesNoOptions } from 'helpers/options'

export const getInputAttributes = () => ({
  DATE_FORMAT: {
    component: Select,
    attributePath: 'attributes.date_format',
    props: {
      labelText: t('Date/Time Format'),
      dataTestId: 'date-time-format',
      options: [
        {
          label: 'yyyy-mm-dd',
          value: 'yyyy-mm-dd',
        },
        {
          label: 'yyyy/mm/dd',
          value: 'yyyy/mm/dd',
        },
        {
          label: 'mm-dd-yyyy',
          value: 'mm-dd-yyyy',
        },
        {
          label: 'mm/dd/yyyy',
          value: 'mm/dd/yyyy',
        },
        {
          label: 'dd-mm-yyyy',
          value: 'dd-mm-yyyy',
        },
        {
          label: 'dd/mm/yyyy',
          value: 'dd/mm/yyyy',
        },
        {
          label: 'yyyy-mm-dd HH:MM',
          value: 'yyyy-mm-dd HH:MM',
        },
        {
          label: 'yyyy/mm/dd HH:MM',
          value: 'yyyy/mm/dd HH:MM',
        },
        {
          label: 'mm-dd-yyyy HH:MM',
          value: 'mm-dd-yyyy HH:MM',
        },
        {
          label: 'mm/dd/yyyy HH:MM',
          value: 'mm/dd/yyyy HH:MM',
        },
        {
          label: 'dd-mm-yyyy HH:MM',
          value: 'dd-mm-yyyy HH:MM',
        },
        {
          label: 'dd/mm/yyyy HH:MM',
          value: 'dd/mm/yyyy HH:MM',
        },
      ],
    },
  },
  MINUTE_STEP_INTERVAL: {
    component: Input,
    attributePath: 'attributes.dropdown_dates_minute_step',
    props: {
      labelText: t('Minute step interval'),
      dataTestId: 'minute-step-interval',
      max: 30,
      min: 0,
      type: 'number',
    },
  },
  MINIMUM_VALUE: {
    component: Input,
    attributePath: 'attributes.min_num_value_n',
    props: {
      labelText: t('Minimum value'),
      dataTestId: 'minimum-value',
    },
  },
  EQUAL_SUM_VALUE: {
    component: Input,
    attributePath: 'attributes.equals_num_value',
    props: {
      labelText: t('Equals sum value'),
    },
  },
  INTEGER_ONLY: {
    component: ToggleButtons,
    attributePath: 'attributes.num_value_int_only',
    props: {
      labelText: t('Integer only'),
      id: 'integer-only',
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  MAXIMUM_SUM_VALUE: {
    component: Input,
    attributePath: 'attributes.max_num_value',
    props: {
      labelText: t('Maximum sum value'),
    },
  },
  MINIMUM_SUM_VALUE: {
    component: Input,
    attributePath: 'attributes.min_num_value',
    props: {
      labelText: t('Minimum sum value'),
    },
  },
  MAXIMUM_VALUE: {
    component: Input,
    attributePath: 'attributes.max_num_value_n',
    props: {
      labelText: t('Maximum value'),
      dataTestId: 'maximum-value',
    },
  },
  VALUE_RANGE_ALLOWS_MISSING: {
    component: ToggleButtons,
    attributePath: 'attributes.value_range_allows_missing',
    props: {
      labelText: t('Value range allows missing'),
      id: 'value-range-allows-missing',
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
})
