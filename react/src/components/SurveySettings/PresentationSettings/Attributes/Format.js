import { Select } from 'components/UIComponents'
import { useState } from 'react'

const formatOptions = [
  {
    label: 'All in one',
    value: 'de-informal',
  },
  {
    label: 'Question by Question',
    value: '',
  },
  {
    label: 'Group by Group',
    value: 'fr',
  },
]

export const Format = ({ handleUpdate = () => {} }) => {
  const [format, setFormat] = useState(formatOptions[0])

  const handleLanguageChange = (evt) => {
    const format = formatOptions.find(
      (option) => option.value === evt.target.value
    )

    handleUpdate({ format: format.value })
    setFormat({ ...format })
  }

  return (
    <div className="mt-3">
      <Select
        labelText="Format"
        options={formatOptions}
        onChange={handleLanguageChange}
        selectedOption={format}
      />
    </div>
  )
}
