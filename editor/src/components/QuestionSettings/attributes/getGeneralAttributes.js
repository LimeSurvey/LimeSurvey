import { Input, ToggleButtons } from 'components/UIComponents'
import { DefaultValuesActions } from '../DefaultValuesActions'

import { QuestionTypeAttribute } from './QuestionTypeAttribute'
import { QuestionCodeAttribute } from './QuestionCodeAttribute'
import { RatingItems } from 'components/UIComponents/RatingItems'
import {
  getOnOffOptions,
  getYesNoOptions,
  ONOFF_BOOLEAN,
  YESNO_BOOLEAN,
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
      labelText: t('Maximum characters'),
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
    component: DefaultValuesActions,
    attributePath: 'defaultAttributeValuesActions',
    action: true,
    props: {},
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
