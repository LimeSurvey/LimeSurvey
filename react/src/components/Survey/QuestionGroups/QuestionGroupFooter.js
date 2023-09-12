import classNames from 'classnames'

import { AddQuestion } from '../AddQuestion'
import { useAppState } from 'hooks'

export const QuestionGroupFooter = ({
  questionGroup,
  language,
  defaultLanguage,
  handleAddQuestion,
  handleAddQuestionGroup,
  onToggleAddQuestionOverlay,
}) => {
  const [isSurveyActive] = useAppState('isSurveyActive', false)

  return (
    <div
      className={classNames(
        'add-question',
        'd-flex',
        'justify-content-center',
        'mt-4'
      )}
      style={{ color: isSurveyActive ? '#63c792' : '#14ae5c' }}
    >
      <AddQuestion
        questionGroup={questionGroup}
        onToggle={onToggleAddQuestionOverlay}
        handleAddQuestion={handleAddQuestion}
        handleAddQuestionGroup={handleAddQuestionGroup}
        toggleDarkOnOpen={false}
        surveyId={questionGroup.sid}
      />
    </div>
  )
}
