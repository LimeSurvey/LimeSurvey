import { Input, ToggleButtons } from 'components/UIComponents'

import { QuestionTypeAttribute } from './QuestionTypeAttribute'
import { QuestionCodeAttribute } from './QuestionCodeAttribute'
import { RatingItems } from 'components/UIComponents/RatingItems'
import {
  getOnOffOptions,
  getYesNoOptions,
  ONOFF_BOOLEAN,
  ONOFF_SHORTSTRING,
  YESNO_BOOLEAN,
  YESNO_SHORTSTRING,
} from 'helpers/options'

export const getGeneralAttributes = () => ({
  QUESTION_CODE: {
    component: QuestionCodeAttribute,
    attributePath: 'title',
    props: {},
    returnValues: ['title'],
  },
  QUESTION_TYPE: {
    component: QuestionTypeAttribute,
    attributePath: 'questionThemeName',
    props: {},
    returnValues: ['type', 'questionThemeName'],
  },
  MANDATORY: {
    component: ToggleButtons,
    attributePath: 'mandatory',
    props: {
      labelText: t('Mandatory'),
      id: 'mandatory',
      toggleOptions: [
        { name: t('On'), value: true },
        { name: t('Soft'), value: 'S' },
        { name: t('Off'), value: false },
      ],
      defaultValue: false,
    },
    returnValues: ['mandatory'],
  },
  NUMBERS_ONLY: {
    component: ToggleButtons,
    attributePath: 'attributes.numbers_only',
    props: {
      labelText: t('Numbers only'),
      id: 'numbers-only-attribute-question-settings',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  MAX_CHARACTERS: {
    component: Input,
    attributePath: 'attributes.maximum_chars',
    props: {
      labelText: t('Max characters'),
      id: 'maximum-characters',
      allowEmpty: true,
      placeholder: 500,
      type: 'number',
    },
  },
  ENCRYPTED: {
    component: ToggleButtons,
    attributePath: 'encrypted',
    props: {
      labelText: t('Store answers encrypted'),
      id: 'general-encrypted',
      toggleOptions: getYesNoOptions(YESNO_BOOLEAN),
      defaultValue: false,
    },
    returnValues: ['encrypted'],
  },
  SAVE_AS_DEFAULT: {
    component: ToggleButtons,
    attributePath: 'attributes.save_as_default',
    props: {
      labelText: t('Save as default values'),
      id: 'save-as-default-values',
      toggleOptions: getYesNoOptions(YESNO_SHORTSTRING),
      defaultValue: 'N',
    },
  },
  CLEAR_DEFAULT_VALUES: {
    component: ToggleButtons,
    attributePath: 'attributes.clear_default',
    props: {
      labelText: t('Clear default values'),
      id: 'clear-default-values',
      toggleOptions: getOnOffOptions(ONOFF_SHORTSTRING),
    },
  },
  OTHER: {
    component: ToggleButtons,
    attributePath: 'other',
    props: {
      labelText: t('Other'),
      id: 'general-other',
      toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
      defaultValue: false,
    },
    returnValues: ['other'],
  },
  INPUT_VALIDATION: {
    component: Input,
    attributePath: 'preg',
    props: {
      labelText: t('Input validation'),
    },
    returnValues: ['preg'],
  },
  LOGIC: {
    component: Input,
    attributePath: 'relevance',
    props: {
      labelText: t('Logic'),
    },
    returnValues: ['relevance'],
    hidden: !process.env.REACT_APP_DEV_MODE,
  },
  RATING_ITEMS: {
    component: RatingItems,
    attributePath: '',
    props: {
      labelText: t('Rating items'),
    },
  },
})
