import { Select } from 'components/UIComponents'
import React, { useEffect, useState } from 'react'

export const SingleChoiceDropdownAnswer = ({
  answers = [],
  handleUpdateAnswer,
  value,
}) => {
  const [options, setOptions] = useState([])

  useEffect(() => {
    const tmpOptions = answers.map((answer) => ({
      label: answer.assessmentValue.toString()
        ? answer.assessmentValue.toString()
        : value,
      value: answer.assessmentValue.toString()
        ? answer.assessmentValue.toString()
        : value,
    }))

    if (tmpOptions.length > 0) {
      setOptions([...tmpOptions])
    } else {
      setOptions([
        {
          label: 'No choices added yet',
          value: null,
        },
      ])
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [answers])

  return (
    <div className="pe-4">
      <Select
        options={options}
        onChange={handleUpdateAnswer}
        selectedOption={options[0]}
        className="pointer-events-none"
      />
      {options.length > 0 && options[0].value !== null && (
        <p className="text-primary mt-2">
          {options.length} choice{options.length > 1 ? 's' : ''}
        </p>
      )}
    </div>
  )
}
