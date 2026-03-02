export const getNotSupportedQuestionTypeInfo = () => {
  return {
    HUGE_FREE_TEXT: {
      type: 'U',
      theme: 'hugefreetext',
      title: t('Huge text'),
    },
    INPUT_ON_DEMAND: {
      type: 'Q',
      theme: 'inputondemand',
      title: t('Input on demand'),
    },
    LANGUAGE_SWITCH: {
      type: 'I',
      theme: 'language',
      title: t('Language switch'),
    },
    LIST_DROPDOWN_DEFAULT: {
      type: '!',
      theme: 'list_dropdown',
      title: t('List dropdown'),
    },
    ARRAY_FIVE_POINT: {
      type: 'A',
      theme: 'arrays/5point',
      title: t('Array five points'),
    },
    ARRAY_TEN_POINT: {
      type: 'B',
      theme: 'arrays/10point',
      title: t('Array ten points'),
    },
    ARRAY_INCREASE_SAME_DECREASE: {
      type: 'E',
      theme: 'arrays/increasesamedecrease',
      title: t('Array increase, same, and decrease'),
    },
    ARRAY_YES_NO_UNCERTAIN: {
      type: 'C',
      theme: 'arrays/yesnouncertain',
      title: t('Array yes, no, and uncertain'),
    },
  }
}
