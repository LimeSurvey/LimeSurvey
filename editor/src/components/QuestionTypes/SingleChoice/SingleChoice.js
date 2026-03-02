import { useRef } from 'react'

import { Entities, STATES } from 'helpers'
import { useAppState } from 'hooks'

import { SingleChoiceAnswers } from './SingleChoiceAnswers'

export const SingleChoice = ({
  question: { answers } = {},
  question,
  handleUpdate = () => {},
  handleChildLUpdate,
  isFocused,
  surveySettings,
  handleChildAdd,
  handleOnChildDragEnd,
  handleChildDelete,
  value,
}) => {
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)

  const answersRef = useRef(null)
  answersRef.current = answers

  const handleUpdateNoAnswerAttribute = (value) => {
    question.attributes.no_answer = {
      ...question.attributes.no_answer,
      [activeLanguage]: {
        value: value,
      },
    }

    handleUpdate({ attributes: question.attributes })
  }

  const handleOnDragEnd = (dropResult) => {
    handleOnChildDragEnd(dropResult, answers, Entities.answer)
  }

  const handleRemovingAnswers = (aid) => {
    handleChildDelete(aid, answers, Entities.answer)
  }

  return (
    <SingleChoiceAnswers
      handleChildAdd={() => handleChildAdd(answers, Entities.answer)}
      handleOnDragEnd={handleOnDragEnd}
      handleRemovingAnswers={handleRemovingAnswers}
      handleUpdateAnswer={(value, index) =>
        handleChildLUpdate(value, index, answersRef.current, Entities.answer)
      }
      isFocused={isFocused}
      question={question}
      handleUpdateNoAnswerAttribute={handleUpdateNoAnswerAttribute}
      surveyLanguage={activeLanguage}
      surveySettings={surveySettings}
      value={value}
    />
  )
}
