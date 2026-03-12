import { getQuestionTypeInfo } from '../QuestionTypes'
import {
  TableIcon,
  QuestionInserterRankIcon,
  QuestionInserterDataIcon,
  QuestionInserterTextIcon,
  QuestionInserterNumberIcon,
  QuestionInserterSingleChoiceIcon,
  QuestionInserterMultipleChoiceIcon,
} from 'components/icons'

export const getQuestionGroupItem = () => ({
  value: getQuestionTypeInfo().QUESTION_GROUP.type,
  label: getQuestionTypeInfo().QUESTION_GROUP.title,
  theme: getQuestionTypeInfo().QUESTION_GROUP.theme,
})

export const getQuestionItemsList = () => [
  {
    title: t('Single choice'),
    icon: <QuestionInserterSingleChoiceIcon />,
    items: [
      {
        value: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.type,
        label: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.title,
        theme: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
      },
      {
        value: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.type,
        label:
          getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.title,
        theme:
          getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme,
      },
      {
        value: getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.type,
        label: getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.title,
        theme: getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
      },
      {
        value: getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.type,
        label: getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.title,
        theme: getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.theme,
      },
      {
        value: getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.type,
        label: getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.title,
        theme: getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme,
      },
      {
        value: getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.type,
        label: getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.title,
        theme: getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme,
      },
    ],
  },
  {
    title: t('Multiple choice'),
    icon: <QuestionInserterMultipleChoiceIcon />,
    items: [
      {
        value: getQuestionTypeInfo().MULTIPLE_CHOICE.type,
        label: getQuestionTypeInfo().MULTIPLE_CHOICE.title,
        theme: getQuestionTypeInfo().MULTIPLE_CHOICE.theme,
      },
      {
        value: getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.type,
        label: getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.title,
        theme: getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme,
      },
      {
        value: getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.type,
        label: getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.title,
        theme: getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme,
      },
      {
        value: getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.type,
        label: getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.title,
        theme: getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
      },
    ],
  },
  {
    title: t('Ranking & Rating'),
    icon: <QuestionInserterRankIcon />,
    items: [
      {
        value: getQuestionTypeInfo().RANKING.type,
        label: getQuestionTypeInfo().RANKING.title,
        theme: getQuestionTypeInfo().RANKING.theme,
        hidden: !process.env.REACT_APP_DEV_MODE,
      },
      {
        value: getQuestionTypeInfo().RANKING_ADVANCED.type,
        label: getQuestionTypeInfo().RANKING_ADVANCED.title,
        theme: getQuestionTypeInfo().RANKING_ADVANCED.theme,
      },
      {
        value: getQuestionTypeInfo().RATING.type,
        label: getQuestionTypeInfo().RATING.title,
        theme: getQuestionTypeInfo().RATING.theme,
        hidden: !process.env.REACT_APP_DEV_MODE,
      },
      {
        value: getQuestionTypeInfo().GENDER.type,
        label: getQuestionTypeInfo().GENDER.title,
        theme: getQuestionTypeInfo().GENDER.theme,
      },
      {
        value: getQuestionTypeInfo().YES_NO.type,
        label: getQuestionTypeInfo().YES_NO.title,
        theme: getQuestionTypeInfo().YES_NO.theme,
      },
    ],
  },
  {
    title: t('Dates & data'),
    icon: <QuestionInserterDataIcon />,
    items: [
      {
        value: getQuestionTypeInfo().BROWSER_DETECTION.type,
        label: getQuestionTypeInfo().BROWSER_DETECTION.title,
        theme: getQuestionTypeInfo().BROWSER_DETECTION.theme,
      },
      {
        value: getQuestionTypeInfo().DATE_TIME.type,
        label: getQuestionTypeInfo().DATE_TIME.title,
        theme: getQuestionTypeInfo().DATE_TIME.theme,
      },
      {
        value: getQuestionTypeInfo().FILE_UPLOAD.type,
        label: getQuestionTypeInfo().FILE_UPLOAD.title,
        theme: getQuestionTypeInfo().FILE_UPLOAD.theme,
      },
    ],
  },
  {
    title: t('Text'),
    icon: <QuestionInserterTextIcon />,
    items: [
      {
        value: getQuestionTypeInfo().SHORT_TEXT.type,
        label: getQuestionTypeInfo().SHORT_TEXT.title,
        theme: getQuestionTypeInfo().SHORT_TEXT.theme,
      },
      {
        value: getQuestionTypeInfo().LONG_TEXT.type,
        label: getQuestionTypeInfo().LONG_TEXT.title,
        theme: getQuestionTypeInfo().LONG_TEXT.theme,
      },
      {
        value: getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.type,
        label: getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.title,
        theme: getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme,
      },
    ],
  },
  {
    title: t('Number'),
    icon: <QuestionInserterNumberIcon />,
    items: [
      {
        value: getQuestionTypeInfo().NUMERIC.type,
        label: getQuestionTypeInfo().NUMERIC.title,
        theme: getQuestionTypeInfo().NUMERIC.theme,
      },
      {
        value: getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.type,
        label: getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.title,
        theme: getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme,
      },
      {
        value: getQuestionTypeInfo().TEXT_DISPLAY.type,
        label: getQuestionTypeInfo().TEXT_DISPLAY.title,
        theme: getQuestionTypeInfo().TEXT_DISPLAY.theme,
      },
      {
        value: getQuestionTypeInfo().EQUATION.type,
        label: getQuestionTypeInfo().EQUATION.title,
        theme: getQuestionTypeInfo().EQUATION.theme,
      },
    ],
  },
  {
    title: t('Array'),
    icon: <TableIcon />,
    items: [
      {
        value: getQuestionTypeInfo().ARRAY.type,
        label: getQuestionTypeInfo().ARRAY.title,
        theme: getQuestionTypeInfo().ARRAY.theme,
      },
      {
        value: getQuestionTypeInfo().ARRAY_TEXT.type,
        label: getQuestionTypeInfo().ARRAY_TEXT.title,
        theme: getQuestionTypeInfo().ARRAY_TEXT.theme,
      },
      {
        value: getQuestionTypeInfo().ARRAY_NUMBERS.type,
        label: getQuestionTypeInfo().ARRAY_NUMBERS.title,
        theme: getQuestionTypeInfo().ARRAY_NUMBERS.theme,
      },
      {
        value: getQuestionTypeInfo().ARRAY_COLUMN.type,
        label: getQuestionTypeInfo().ARRAY_COLUMN.title,
        theme: getQuestionTypeInfo().ARRAY_COLUMN.theme,
      },
      {
        value: getQuestionTypeInfo().ARRAY_DUAL_SCALE.type,
        label: getQuestionTypeInfo().ARRAY_DUAL_SCALE.title,
        theme: getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
      },
    ],
  },
]
