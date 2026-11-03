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
        const isFocusingAQuestion = questionIndex
        if (isFocusingAQuestion) {
          setFocused(focused, questionGroup, questionIndex)
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
