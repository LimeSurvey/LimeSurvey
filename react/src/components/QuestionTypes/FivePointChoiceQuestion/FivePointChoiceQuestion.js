import { FormCheck } from 'react-bootstrap'

import { FivePointChoiceAnswerType } from './FivePointChoiceAnswertype'
import './FivePointChoiceQuestion.scss'

export const FivePointChoiceQuestion = ({ question, handleUpdate }) => {
  const handleUpdateAnswer = (point) => {
    const updatedQuestion = { ...question }
    updatedQuestion.answers.assessmentValue = point
    handleUpdate({ answers: updatedQuestion.answers })
  }

  return (
    <div className="d-flex gap-4 mb-3 five-point-choice-question question-body-content">
      {Object.values(FivePointChoiceAnswerType).map((point) => {
        if (
          point === FivePointChoiceAnswerType.EMPTY ||
          point === FivePointChoiceAnswerType.NO_ANSWER ||
          typeof point !== 'number'
        ) {
          return ''
        }

        return (
          <FormCheck
            key={`${question.qid}-five-point-${point}`}
            type="radio"
            label={point}
            name={`${question.qid}-five-point`}
            onChange={(e) => handleUpdateAnswer(+e.target.value)}
            defaultChecked={question.answers.assessmentValue === point}
            data-testid="five-point-choice-question-answer"
            value={point}
          />
        )
      })}
      {!question.mandatory && (
        <FormCheck
          key={`${question.qid}-five-point-empty`}
          type="radio"
          label={'No answer'}
          name={`${question.qid}-five-point`}
          onChange={(e) => handleUpdateAnswer(+e.target.value)}
          defaultChecked={
            question.answers.assessmentValue ===
            FivePointChoiceAnswerType.NO_ANSWER
          }
          data-testid="five-point-choice-question-answer"
          value={FivePointChoiceAnswerType.NO_ANSWER}
        />
      )}
    </div>
  )
}
