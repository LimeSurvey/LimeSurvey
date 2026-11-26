import classNames from 'classnames'

import { useAppState, useQuestionChildren } from 'hooks'
import { STATES } from 'helpers'

import {
  questionViewComponents,
  questionEditComponents,
} from '../../QuestionTypes'

/**
 * The options of a question (answers, subquestions) in edit or view mode.
 *
 * @param {Object} question the question
 * @param {Function} handleUpdate saves question changes and focuses the
 *   question
 * @param {Function} handleUpdateWithoutFocus saves question changes without
 *   focusing the question, see useQuestionChildren
 * @param {string} language the active language
 * @param {boolean} isFocused whether the question is focused
 * @param {boolean} isHovered whether the question is hovered
 * @param {boolean} isQuestionDisabled whether the question type is disabled
 * @param {Object} surveySettings the survey settings
 * @param {boolean} isTitleFocused whether the question title has the focus
 * @returns {JSX.Element} the question body
 */
export const QuestionBody = ({
  question,
  handleUpdate,
  handleUpdateWithoutFocus,
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
    handleLabelSetLoad,
    handleChildDelete,
    handleOnChildDragEnd,
    handleChildLUpdate,
    handleChildCodeUpdate,
    handleChildAssessmentValueUpdate,
    activeLanguage,
  } = useQuestionChildren({
    question,
    handleUpdate,
    handleUpdateWithoutFocus,
    isFocused,
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
          handleLabelSetLoad={handleLabelSetLoad}
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
          handleChildCodeUpdate={handleChildCodeUpdate}
          handleChildAssessmentValueUpdate={handleChildAssessmentValueUpdate}
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
