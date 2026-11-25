/**
 * Returns the type code and theme of the question types the editor only previews and can't add.
 * Their titles come from the API (see useQuestionTypes).
 * @returns {Object<string, {type: string, theme: string}>}
 */
export const getNotSupportedQuestionTypeInfo = () => {
  return {
    HUGE_FREE_TEXT: {
      type: 'U',
      theme: 'hugefreetext',
    },
    INPUT_ON_DEMAND: {
      type: 'Q',
      theme: 'inputondemand',
    },
    LANGUAGE_SWITCH: {
      type: 'I',
      theme: 'language',
    },
    LIST_DROPDOWN_DEFAULT: {
      type: '!',
      theme: 'list_dropdown',
    },
    ARRAY_FIVE_POINT: {
      type: 'A',
      theme: 'arrays/5point',
    },
    ARRAY_TEN_POINT: {
      type: 'B',
      theme: 'arrays/10point',
    },
    ARRAY_INCREASE_SAME_DECREASE: {
      type: 'E',
      theme: 'arrays/increasesamedecrease',
    },
    ARRAY_YES_NO_UNCERTAIN: {
      type: 'C',
      theme: 'arrays/yesnouncertain',
    },
  }
}
