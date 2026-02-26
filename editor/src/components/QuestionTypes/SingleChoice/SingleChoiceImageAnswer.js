import React from 'react'

import { ImageChoice } from '../ImageChoice'
export const SingleChoiceImageAnswer = ({
  index,
  isFocused,
  answer,
  update,
  value,
  isNoAnswer,
  answerErrors,
  setAnswerErrors,
}) => {
  return (
    <ImageChoice
      index={index}
      inputType="radio"
      isFocused={isFocused}
      idPrefix="a"
      id={answer?.aid}
      update={update}
      value={value}
      isNoAnswer={isNoAnswer}
      errors={answerErrors}
      setErrors={setAnswerErrors}
    />
  )
}
