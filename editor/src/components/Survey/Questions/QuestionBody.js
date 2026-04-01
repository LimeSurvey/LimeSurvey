import classNames from 'classnames'

import { useAppState, useQuestionChildren } from 'hooks'
import { STATES } from 'helpers'

import {
  questionViewComponents,
  questionEditComponents,
} from '../../QuestionTypes'

export const QuestionBody = ({
  question,
  handleUpdate,
  language,
  isFocused,
  isHovered,
  isQuestionDisabled,
  surveySettings,
  isTitleFocused = false,
}) => {
  const QuestionViewComponent =
    questionViewComponents[question.questionThemeName]
  const QuestionEditComponent =
    questionEditComponents[question.questionThemeName]

  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )

  const isFocusedWithPermission = isFocused && hasSurveyUpdatePermission

  const {
    children,
    handleChildAdd,
    handleChildDelete,
    handleOnChildDragEnd,
    handleChildLUpdate,
    activeLanguage,
  } = useQuestionChildren({
    question,
    handleUpdate,
    surveySettings,
    language,
  })

  if (!QuestionViewComponent) {
    return <div></div>
  }

  return (
    <div
      className={classNames('question-body', {
        'disabled opacity-50': isQuestionDisabled,
      })}
    >
      {isFocused && QuestionEditComponent ? (
        <QuestionEditComponent
          handleUpdate={handleUpdate}
          handleChildLUpdate={handleChildLUpdate}
          handleChildAdd={handleChildAdd}
          handleChildDelete={handleChildDelete}
          handleOnChildDragEnd={handleOnChildDragEnd}
          question={question}
          language={activeLanguage}
          isFocused={isFocusedWithPermission}
          isHovered={isHovered}
          surveySettings={surveySettings}
          _children={children}
          isTitleFocused={isTitleFocused}
          valueInfo={{ value: [] }}
        />
      ) : (
        <QuestionViewComponent
          handleUpdate={handleUpdate}
          handleChildLUpdate={handleChildLUpdate}
          handleChildAdd={handleChildAdd}
          handleChildDelete={handleChildDelete}
          handleOnChildDragEnd={handleOnChildDragEnd}
          question={question}
          language={activeLanguage}
          isFocused={isFocusedWithPermission}
          isHovered={isHovered}
          surveySettings={surveySettings}
          _children={children}
          valueInfo={{ value: [] }}
        />
      )}
    </div>
  )
}
