import {
  FivePointChoiceQuestion,
  TextQuestion,
  ArrayQuestion,
  RatingQuestion,
  QuestionTypeInfo,
  MultipleChoice,
  SingleChoice,
  FileUpload,
  RankingQuestion,
  RankingAdvancedQuestion,
  Equation,
  DateTime,
  GenderQuestion,
  YesNoQuestion,
} from '../../QuestionTypes'

const questionComponents = {
  [QuestionTypeInfo.LIST_RADIO.theme]: SingleChoice,
  [QuestionTypeInfo.LIST_RADIO_WITH_COMMENT.theme]: SingleChoice,
  [QuestionTypeInfo.SINGLE_CHOICE_DROPDOWN.theme]: SingleChoice,
  [QuestionTypeInfo.SINGLE_CHOICE_BUTTONS.theme]: SingleChoice,
  [QuestionTypeInfo.SINGLE_CHOICE_LIST_IMAGE_SELECT.theme]: SingleChoice,
  [QuestionTypeInfo.MULTIPLE_CHOICE.theme]: MultipleChoice,
  [QuestionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.theme]: MultipleChoice,
  [QuestionTypeInfo.MULTIPLE_CHOICE_BUTTONS.theme]: MultipleChoice,
  [QuestionTypeInfo.MULTIPLE_CHOICE_IMAGE_SELECT.theme]: MultipleChoice,
  [QuestionTypeInfo.MULTIPLE_SHORT_TEXTS.theme]: MultipleChoice,
  [QuestionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.theme]: MultipleChoice,
  [QuestionTypeInfo.FIVE_POINT_CHOICE.theme]: FivePointChoiceQuestion,
  [QuestionTypeInfo.SHORT_TEXT.theme]: TextQuestion,
  [QuestionTypeInfo.BROWSER_DETECTION.theme]: TextQuestion,
  [QuestionTypeInfo.LONG_TEXT.theme]: TextQuestion,
  [QuestionTypeInfo.ARRAY.theme]: ArrayQuestion,
  [QuestionTypeInfo.ARRAY_NUMBERS.theme]: ArrayQuestion,
  [QuestionTypeInfo.ARRAY_TEXT.theme]: ArrayQuestion,
  [QuestionTypeInfo.ARRAY_COLUMN.theme]: ArrayQuestion,
  [QuestionTypeInfo.ARRAY_DUAL_SCALE.theme]: ArrayQuestion,
  [QuestionTypeInfo.RATING.theme]: RatingQuestion,
  [QuestionTypeInfo.FILE_UPLOAD.theme]: FileUpload,
  [QuestionTypeInfo.RANKING.theme]: RankingQuestion,
  [QuestionTypeInfo.RANKING_ADVANCED.theme]: RankingAdvancedQuestion,
  [QuestionTypeInfo.EQUATION.theme]: Equation,
  [QuestionTypeInfo.DATE_TIME.theme]: DateTime,
  [QuestionTypeInfo.GENDER.theme]: GenderQuestion,
  [QuestionTypeInfo.YES_NO.theme]: YesNoQuestion,
}

export const QuestionBody = ({
  question,
  handleUpdate,
  language,
  isFocused,
  isHovered,
}) => {
  const QuestionComponent = questionComponents[question.questionThemeName]

  if (!QuestionComponent) {
    return <></>
  }

  return (
    <div className="question-body">
      <QuestionComponent
        handleUpdate={handleUpdate}
        question={question}
        language={language}
        isFocused={isFocused}
        isHovered={isHovered}
      />
    </div>
  )
}
