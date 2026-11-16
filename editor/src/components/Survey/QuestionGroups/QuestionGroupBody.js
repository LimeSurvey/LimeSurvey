import { Collapse } from 'react-bootstrap'

import { Questions } from '../Questions'

export const QuestionGroupBody = ({
  showQuestions,
  setFocused,
  focused,
  questionGroup,
  questionIndex,
  handleUpdateQuestions,
  language,
  firstQuestionNumber,
  groupIndex,
  surveySettings,
}) => {
  return (
    <Collapse
      in={showQuestions}
      unmountOnExit={true}
      onEntered={() => {
        // Re-focus only a question of this group, with this group's index
        const isFocusInThisGroup = questionGroup.questions?.some(
          (question) => question.qid?.toString() === focused?.qid?.toString()
        )
        if (questionIndex !== undefined && isFocusInThisGroup) {
          setFocused(focused, groupIndex, questionIndex)
        }
      }}
    >
      <div className="question-group-body">
        <Questions
          language={language}
          questions={questionGroup.questions}
          update={handleUpdateQuestions}
          firstQuestionNumber={firstQuestionNumber}
          questionGroupIsOpen={showQuestions}
          groupIndex={groupIndex}
          questionGroup={questionGroup}
          surveySettings={surveySettings}
        />
      </div>
    </Collapse>
  )
}
