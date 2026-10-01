import { useEffect, useState } from 'react'

import './FivePointChoiceQuestion.scss'
import { ContentEditor, FormCheck } from 'components/UIComponents'
import { getNoAnswerLabel } from 'helpers'

const POINT_OPTIONS = [1, 2, 3, 4, 5]
const NO_ANSWER_INDEX = POINT_OPTIONS.length

const getSelectedOptionIndex = (valueInfo, preselectNoAnswer) => {
  const selectedIndex = POINT_OPTIONS.findIndex(
    (point) => point.toString() === valueInfo?.value?.toString()
  )

  if (selectedIndex !== -1) {
    return selectedIndex
  }

  return preselectNoAnswer && !valueInfo?.checked ? NO_ANSWER_INDEX : -1
}

export const FivePointChoiceQuestion = ({
  question = {},
  surveySettings = {},
  values = [],
  participantMode = false,
  onValueChange = () => {},
}) => {
  const showNoAnswer = !question?.mandatory && surveySettings?.showNoAnswer
  const valueInfo = participantMode ? values[0] : {}
  const [selectedOptionIndex, setSelectedOptionIndex] = useState(() =>
    getSelectedOptionIndex(valueInfo, surveySettings?.preselectNoAnswer)
  )

  useEffect(() => {
    setSelectedOptionIndex(
      getSelectedOptionIndex(valueInfo, surveySettings?.preselectNoAnswer)
    )
  }, [valueInfo?.checked, valueInfo?.value, surveySettings?.preselectNoAnswer])

  const handleValueChange = (value, index) => {
    setSelectedOptionIndex(index)
    onValueChange(value, valueInfo.key)
  }

  return (
    <div className="d-flex gap-4 mb-3 flex-wrap question-body-content">
      {POINT_OPTIONS.map((point, index) => {
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
            checked={selectedOptionIndex === index}
            update={() => handleValueChange(point, index)}
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
          update={() => handleValueChange(null, NO_ANSWER_INDEX)}
          groupName={valueInfo.key}
          checked={selectedOptionIndex === NO_ANSWER_INDEX}
        />
      )}
    </div>
  )
}
