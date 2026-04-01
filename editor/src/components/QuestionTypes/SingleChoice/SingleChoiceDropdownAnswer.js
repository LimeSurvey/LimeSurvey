import { Select } from 'components/UIComponents'
import { RemoveHTMLTagsInString } from 'helpers'
import React, { useEffect, useState } from 'react'

export const SingleChoiceDropdownAnswer = ({
  answers = [],
  surveyLanguage = 'en',
}) => {
  const [options, setOptions] = useState([])

  useEffect(() => {
    const tmpOptions = answers?.map((answer = { l10ns: {} }) => ({
      label:
        RemoveHTMLTagsInString(answer?.l10ns[surveyLanguage]?.answer) || '',
      value: answer?.aid,
    }))

    if (tmpOptions.length > 0) {
      setOptions([...tmpOptions])
    } else {
      setOptions([
        {
          label: t('No choices added yet'),
          value: '',
        },
      ])
    }
  }, [answers])

  return (
    <div
      className="pe-4"
      onClick={(e) => {
        e.stopPropagation()
      }}
    >
      <Select options={options} placeholder={st('Please choose...')} />
    </div>
  )
}
