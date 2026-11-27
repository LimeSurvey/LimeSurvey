/**
 * Returns the type code and theme of the question types the editor renders.
 * The titles and the question type selector come from the API (see useQuestionTypes),
 * only the editor-only entries that are not question themes have a title here.
 * @returns {Object<string, {type: string, theme: string, title?: string, isQuestionType?: boolean}>}
 */
export const getQuestionTypeInfo = () => {
  return {
    ARRAY: {
      type: 'F',
      theme: 'arrays/array',
    },
    ARRAY_TEXT: {
      type: ';',
      theme: 'arrays/texts',
    },
    ARRAY_NUMBERS: {
      type: ':',
      theme: 'arrays/multiflexi',
    },
    ARRAY_COLUMN: {
      type: 'H',
      theme: 'arrays/column',
    },
    ARRAY_DUAL_SCALE: {
      type: '1',
      theme: 'arrays/dualscale',
    },
    BROWSER_DETECTION: {
      type: 'S',
      theme: 'browserdetect',
    },
    DATE_TIME: {
      type: 'D',
      theme: 'date',
    },
    FILE_UPLOAD: { type: '|', theme: 'file_upload' },
    FIVE_POINT_CHOICE: {
      type: 'A',
      theme: '5pointchoice',
    },
    EQUATION: { type: '*', theme: 'equation' },
    GENDER: { type: 'G', theme: 'gender' },
    INCREASE_SAME_DECREASE: {
      type: 'E',
      theme: 'increasedecrease',
    },
    LONG_TEXT: {
      type: 'T',
      theme: 'longfreetext',
    },
    MAP: {
      type: 'J',
      theme: 'map',
    },
    MULTIPLE_CHOICE: {
      type: 'M',
      theme: 'multiplechoice',
    },
    MULTIPLE_CHOICE_WITH_COMMENTS: {
      type: 'P',
      theme: 'multiplechoice_with_comments',
    },
    MULTIPLE_SHORT_TEXTS: {
      type: 'Q',
      theme: 'multipleshorttext',
    },
    MULTIPLE_NUMERICAL_INPUTS: {
      type: 'K',
      theme: 'multiplenumeric',
    },
    MULTIPLE_CHOICE_BUTTONS: {
      type: 'M',
      theme: 'bootstrap_buttons_multi',
    },
    MULTIPLE_CHOICE_IMAGE_SELECT: {
      type: 'M',
      theme: 'image_select-multiplechoice',
    },
    NUMERIC: {
      type: 'N',
      theme: 'numerical',
    },
    RANKING: { type: 'R', theme: 'ranking' },
    RANKING_ADVANCED: {
      type: 'R',
      theme: 'ranking_advanced',
    },
    RATING: { type: 'RT', theme: 'rating', hidden: true },
    QUESTION_GROUP: {
      type: 'QG',
      theme: 'QuestionGroup',
      title: t('Question group'),
      isQuestionType: false,
    },
    SINGLE_CHOICE_LIST_RADIO: {
      type: 'L',
      theme: 'listradio',
    },
    SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT: {
      type: 'O',
      theme: 'list_with_comment',
    },
    SINGLE_CHOICE_IMAGE_SELECT: {
      type: 'L',
      theme: 'image_select-listradio',
    },
    SINGLE_CHOICE_BUTTONS: {
      type: 'L',
      theme: 'bootstrap_buttons',
    },
    SINGLE_CHOICE_DROPDOWN: {
      type: '!',
      theme: 'bootstrap_dropdown',
    },
    SINGLE_CHOICE_FIVE_POINT_CHOICE: {
      type: '5',
      theme: '5pointchoice',
    },
    SHORT_TEXT: {
      type: 'S',
      theme: 'shortfreetext',
    },
    TEN_POINT_CHOICE: {
      type: 'B',
      theme: '5pointchoice',
    },
    TEXT_DISPLAY: { type: 'X', theme: 'boilerplate' },
    YES_NO: { type: 'Y', theme: 'yesno' },
    YES_NO_UNCERTAIN: {
      type: 'C',
      theme: 'yesno',
    },
    WELCOME_SCREEN: {
      type: 'WS',
      theme: 'WelcomeScreen',
      title: t('Welcome screen'),
      isQuestionType: false,
    },
    END_SCREEN: {
      type: 'ES',
      theme: 'EndScreen',
      title: t('End screen'),
      isQuestionType: false,
    },
  }
}
