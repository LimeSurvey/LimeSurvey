export const getQuestionTypeInfo = () => {
  return {
    ARRAY: {
      type: 'F',
      theme: 'arrays/array',
      title: t('Array (Point choice)'),
    },
    ARRAY_TEXT: {
      type: ';',
      theme: 'arrays/texts',
      title: t('Array (Texts)'),
    },
    ARRAY_NUMBERS: {
      type: ':',
      theme: 'arrays/multiflexi',
      title: t('Array (Numbers)'),
    },
    ARRAY_COLUMN: {
      type: 'H',
      theme: 'arrays/column',
      title: t('Array by column'),
    },
    ARRAY_DUAL_SCALE: {
      type: '1',
      theme: 'arrays/dualscale',
      title: t('Array dual scale'),
    },
    BROWSER_DETECTION: {
      type: 'S',
      theme: 'browserdetect',
      title: t('Map/browser detection'),
    },
    DATE_TIME: {
      type: 'D',
      theme: 'date',
      title: t('Date/Time'),
    },
    FILE_UPLOAD: { type: '|', theme: 'file_upload', title: t('File upload') },
    FIVE_POINT_CHOICE: {
      type: 'A',
      theme: '5pointchoice',
      title: t('5 Point Choice'),
    },
    EQUATION: { type: '*', theme: 'equation', title: t('Equation') },
    GENDER: { type: 'G', theme: 'gender', title: t('Gender') },
    INCREASE_DECREASE: {
      type: 'E',
      theme: 'increasedecrease',
      title: t('Increase Decrease'),
    },
    LONG_TEXT: {
      type: 'T',
      theme: 'longfreetext',
      title: t('Long text'),
    },
    MULTIPLE_CHOICE: {
      type: 'M',
      theme: 'multiplechoice',
      title: t('Multiple choice'),
    },
    MULTIPLE_CHOICE_WITH_COMMENTS: {
      type: 'P',
      theme: 'multiplechoice_with_comments',
      title: t('Multiple choice with comments'),
    },
    MULTIPLE_SHORT_TEXTS: {
      type: 'Q',
      theme: 'multipleshorttext',
      title: t('Multiple short texts'),
    },
    MULTIPLE_NUMERICAL_INPUTS: {
      type: 'K',
      theme: 'multiplenumeric',
      title: t('Multiple numerical inputs'),
    },
    MULTIPLE_CHOICE_BUTTONS: {
      type: 'M',
      theme: 'bootstrap_buttons_multi',
      title: t('Multiple choice buttons'),
    },
    MULTIPLE_CHOICE_IMAGE_SELECT: {
      type: 'M',
      theme: 'image_select-multiplechoice',
      title: t('Image select multiple choice'),
    },
    NUMERIC: {
      type: 'N',
      theme: 'numerical',
      title: t('Numerical input'),
    },
    RANKING: { type: 'R', theme: 'ranking', title: t('Ranking') },
    RANKING_ADVANCED: {
      type: 'R',
      theme: 'ranking_advanced',
      title: t('Ranking advanced'),
    },
    RATING: { type: 'RT', theme: 'rating', title: t('Rating'), hidden: true },
    QUESTION_GROUP: {
      type: 'QG',
      theme: 'QuestionGroup',
      title: t('Question group'),
      isQuestionType: false,
    },
    SINGLE_CHOICE_LIST_RADIO: {
      type: 'L',
      theme: 'listradio',
      title: t('List (Radio)'),
    },
    SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT: {
      type: 'O',
      theme: 'list_with_comment',
      title: t('List with comment (Radio)'),
    },
    SINGLE_CHOICE_IMAGE_SELECT: {
      type: 'L',
      theme: 'image_select-listradio',
      title: t('List image select (Radio)'),
    },
    SINGLE_CHOICE_BUTTONS: {
      type: 'L',
      theme: 'bootstrap_buttons',
      title: t('Single choice buttons'),
    },
    SINGLE_CHOICE_DROPDOWN: {
      type: '!',
      theme: 'bootstrap_dropdown',
      title: t('Dropdown'),
    },
    SINGLE_CHOICE_FIVE_POINT_CHOICE: {
      type: '5',
      theme: '5pointchoice',
      title: t('5 point choice'),
    },
    SHORT_TEXT: {
      type: 'S',
      theme: 'shortfreetext',
      title: t('Short text'),
    },
    TEN_POINT_CHOICE: {
      type: 'B',
      theme: '5pointchoice',
      title: t('10 Point Choice'),
    },
    TEXT_DISPLAY: { type: 'X', theme: 'boilerplate', title: t('Text display') },
    YES_NO: { type: 'Y', theme: 'yesno', title: t('Yes/No') },
    YES_NO_UNCERTAIN: {
      type: 'C',
      theme: 'yesno',
      title: t('Yes/No/Uncertain'),
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
