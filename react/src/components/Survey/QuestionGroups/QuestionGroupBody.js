import { Collapse } from 'react-bootstrap'

import { Questions } from '../Questions'

export const QuestionGroupBody = ({
  showQuestions,
  setFocused,
  focused,
  questionGroup,
  questionIndex,
  handleFocusGroup,
  handleUpdateQuestions,
  setFocusDescription,
  language,
  handleUpdate,
  firstQuestionNumber,
  groupIndex,
}) => {
  return (
    <Collapse
      in={showQuestions}
      onEntered={() => {
        const isFocusingAQuestion = questionIndex
        if (isFocusingAQuestion) {
          setFocused(focused, questionGroup, questionIndex)
        }
      }}
    >
      <div className={'question-group-body'}>
        {/* <div
          onClick={() => {
            handleFocusGroup()
            setFocusDescription(true)
          }}
          className="description py-3"
        >
          <ContentEditor
            value={L10ns({
              prop: 'description',
              language,
              l10ns: questionGroup.l10ns,
            })}
            update={(description) => handleUpdate({ description })}
            placeholder="Add a description about the question group."
          />
        </div> */}
        <Questions
          language={language}
          questions={questionGroup.questions}
          update={handleUpdateQuestions}
          firstQuestionNumber={firstQuestionNumber}
          questionGroupIsOpen={showQuestions}
          groupIndex={groupIndex}
        />
      </div>
    </Collapse>
  )
}
