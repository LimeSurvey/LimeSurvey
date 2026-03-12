import './FivePointChoiceQuestion.scss'
import { ContentEditor, FormCheck } from 'components/UIComponents'
import { getNoAnswerLabel } from 'helpers'

export const FivePointChoiceQuestion = ({
  question = {},
  surveySettings = {},
  values = [],
  participantMode = false,
  onValueChange = () => {},
}) => {
  const showNoAnswer = !question?.mandatory && surveySettings?.showNoAnswer
  const valueInfo = participantMode ? values[0] : {}

  return (
    <div className="d-flex gap-4 mb-3 flex-wrap question-body-content">
      {Object.values([1, 2, 3, 4, 5]).map((point, index) => {
        return (
          <FormCheck
            key={`${question.qid}-five-point-${point}`}
            type="radio"
            label={
              <ContentEditor className="choice" value={point} disabled={true} />
            }
            name={`${question.qid}-five-point`}
            data-testid="five-point-choice-question-answer"
            value={point}
            className="choice"
            defaultChecked={
              valueInfo?.value?.toString() === (index + 1).toString()
            }
            update={() => onValueChange(index + 1, valueInfo.key)}
            groupName={valueInfo.key}
          />
        )
      })}
      {showNoAnswer && (
        <FormCheck
          key={`${question.qid}-five-point-empty`}
          type="radio"
          label={getNoAnswerLabel(true)}
          name={`${question.qid}-five-point`}
          data-testid="five-point-choice-question-answer"
          value=""
          className="choice"
          update={() => onValueChange(null, valueInfo.key)}
          groupName={valueInfo.key}
          defaultChecked={!valueInfo.checked}
        />
      )}
    </div>
  )
}
