import { SURVEY_FIELD } from './filterModel'

// Which control a given survey-data field renders.
export const FIELD_KIND = {
  INCLUDED: 'included', // All / Complete / Incomplete toggle
  DATE_RANGE: 'dateRange', // Start date + End date pickers
  NUMBER_RANGE: 'numberRange', // Min + Max number inputs
  LANGUAGE_MULTI: 'languageMulti', // multi-select of survey languages
}

export const getSurveyDataFieldOptions = () => [
  { value: SURVEY_FIELD.RESPONSE_ID, label: t('Response ID') },
  { value: SURVEY_FIELD.SEED, label: t('Seed') },
  { value: SURVEY_FIELD.SUBMIT_DATE, label: t('Submit date') },
  { value: SURVEY_FIELD.LAST_ACTION, label: t('Date last action') },
  { value: SURVEY_FIELD.INCLUDED, label: t('Included responses') },
  { value: SURVEY_FIELD.LANGUAGE, label: t('Language') },
]

export const getSurveyFieldKind = (field) => {
  switch (field) {
    case SURVEY_FIELD.INCLUDED:
      return FIELD_KIND.INCLUDED
    case SURVEY_FIELD.SUBMIT_DATE:
    case SURVEY_FIELD.LAST_ACTION:
      return FIELD_KIND.DATE_RANGE
    case SURVEY_FIELD.RESPONSE_ID:
    case SURVEY_FIELD.SEED:
      return FIELD_KIND.NUMBER_RANGE
    case SURVEY_FIELD.LANGUAGE:
      return FIELD_KIND.LANGUAGE_MULTI
    default:
      return null
  }
}
