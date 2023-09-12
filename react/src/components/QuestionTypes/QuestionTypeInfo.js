export const QuestionTypeInfo = {
  ARRAY: { type: 'F', theme: 'Array', title: 'Array (Point choice)' },
  ARRAY_TEXT: {
    type: ';',
    theme: 'arrays/texts',
    title: 'Array (Texts)',
  },
  ARRAY_NUMBERS: {
    type: ':',
    theme: 'arrays/multiflexi',
    title: 'Array (Numbers)',
  },
  ARRAY_COLUMN: {
    type: 'H',
    theme: 'arrays/column',
    title: 'Array by column',
  },
  ARRAY_DUAL_SCALE: {
    type: '1',
    theme: 'arrays/dualscale',
    title: 'Array dual scale',
  },
  MULTIPLE_CHOICE: {
    type: 'M',
    theme: 'multiplechoice',
    title: 'Multiple choice',
  },
  MULTIPLE_CHOICE_WITH_COMMENTS: {
    type: 'P',
    theme: 'multiplechoice_with_comments',
    title: 'Multiple choice with comments',
  },
  MULTIPLE_SHORT_TEXTS: {
    type: 'Q',
    theme: 'multipleshorttext',
    title: 'Multiple short texts',
  },
  MULTIPLE_NUMERICAL_INPUTS: {
    type: 'K',
    theme: 'multiplenumeric',
    title: 'Multiple numerical inputs',
  },
  MULTIPLE_CHOICE_BUTTONS: {
    type: 'M',
    theme: 'bootstrap_buttons_multi',
    title: 'Multiple choice buttons',
  },
  MULTIPLE_CHOICE_IMAGE_SELECT: {
    type: 'M',
    theme: 'image_select-multiplechoice',
    title: 'Image select multiple choice',
  },
  LIST_RADIO: { type: 'L', theme: 'listradio', title: 'List (Radio)' },
  LIST_RADIO_WITH_COMMENT: {
    type: 'O',
    theme: 'list_with_comment',
    title: 'List with comment (Radio)',
  },
  SINGLE_CHOICE_LIST_IMAGE_SELECT: {
    type: 'L',
    theme: 'image_select-listradio',
    title: 'List image Select (Radio)',
  },
  SINGLE_CHOICE_BUTTONS: {
    type: 'L',
    theme: 'bootstrap_buttons',
    title: 'Single choice buttons',
  },
  SINGLE_CHOICE_DROPDOWN: {
    type: '!',
    theme: 'list_dropdown',
    title: 'Dropdown',
  },
  SHORT_TEXT: {
    type: 'S',
    theme: 'shortfreetext',
    title: 'Short Text',
  },
  BROWSER_DETECTION: {
    type: 'S',
    theme: 'browserdetect',
    title: 'Browser detection',
  },
  LONG_TEXT: {
    type: 'T',
    theme: 'longfreetext',
    title: 'Long Text',
  },
  FIVE_POINT_CHOICE: {
    type: '5',
    theme: '5pointchoice',
    title: 'Five Point Choice',
  },
  QUESTION_GROUP: {
    type: 'QG',
    theme: 'Question Group',
    title: 'Question Group',
  },
  RATING: { type: 'R', theme: 'rating', title: 'Rating' },
  GENDER: { type: 'G', theme: 'gender', title: 'Gender' },
  YES_NO: { type: 'Y', theme: 'yesno', title: 'Yes/No' },
  DATE_TIME: {
    type: 'DT',
    theme: 'date_time',
    title: 'Date/Time',
  },
  RANKING: { type: 'R', theme: 'ranking', title: 'Ranking' },
  RANKING_ADVANCED: {
    type: 'RA',
    theme: 'ranking_advanced',
    title: 'Ranking advanced',
  },
  FILE_UPLOAD: { type: '|', theme: 'file_upload', title: 'File upload' },
  TEXT_DISPLAY: { type: '|', theme: 'text_display', title: 'Text Display' },
  EQUATION: { type: '*', theme: 'equation', title: 'Equation' },
  WELCOME_SCREEN: {
    type: 'WS',
    theme: 'Welcome Screen',
    title: 'Welcome Screen',
  },
  END_SCREEN: {
    type: 'ES',
    theme: 'End Screen',
    title: 'End Screen',
  },
}
