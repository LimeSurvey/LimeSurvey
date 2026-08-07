import React from 'react'

import { ImageChoice } from '../ImageChoice'
export const MultipleChoiceImageSubquestion = ({
  index,
  isFocused,
  subQuestion,
  update,
  value,
  subquestionErrors,
  setSubquestionErrors,
}) => {
  return (
    <ImageChoice
      index={index}
      inputType="checkbox"
      isFocused={isFocused}
      idPrefix="q"
      id={subQuestion?.qid}
      update={update}
      value={value}
      errors={subquestionErrors}
      setErrors={setSubquestionErrors}
    />
  )
}
