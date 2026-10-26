import React from 'react'
import { Form } from 'react-bootstrap'
import { filterToNumericOrEmpty, L10ns } from 'helpers'
import MultipleChoiceNumericalSlider from './MultipleChoiceNumericalSlider'

/**
 * By adding the onChange and having the value state,this acts as an controlled input,
 * although we don't actually need this.
 * This input is only displayed for the question view mode and by clicking on it we end up in edit mode.
 * If this will be changed in the future, the handleChange ensures that only numbers can be typed in.
 * @returns {Element}
 */
export const MultipleChoiceNumericalSubquestion = ({
  onChange,
  attributes,
  childrenInfo,
  language,
  child,
  hasSliderLayout = false,
  valueInfo = {},
  participantMode = false,
}) => {
  const handleOnChange = (event) => {
    const newValue = filterToNumericOrEmpty(event.target.value)

    if (onChange) {
      onChange(newValue)
    }
  }

  if (hasSliderLayout) {
    return (
      <MultipleChoiceNumericalSlider
        attributes={attributes}
        text={L10ns({
          prop: childrenInfo.titleKey,
          language,
          l10ns: child.l10ns,
        })}
        defaultValue={valueInfo?.value}
        onChange={([newValue]) => {
          onChange(newValue)
        }}
        participantMode={participantMode}
      />
    )
  }

  return (
    <Form.Group>
      <Form.Control
        type="text"
        placeholder={st('Enter your answer here.')}
        data-testid="text-question-answer-input"
        defaultValue={
          participantMode && valueInfo?.value
            ? Number(valueInfo?.value)
            : undefined
        }
        onChange={handleOnChange}
      />
    </Form.Group>
  )
}
