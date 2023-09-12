import { Select } from 'components/UIComponents'
import { useState } from 'react'

const possibleDateFormats = [
  {
    label: 'YYYY-MM-DD',
    value: 'YYYY-MM-DD',
  },
  {
    label: 'YYYY/MM/DD',
    value: 'YYYY/MM/DD',
  },
  {
    label: 'MM-DD-YYYY',
    value: 'MM-DD-YYYY',
  },
  {
    label: 'MM/DD/YYYY',
    value: 'MM/DD/YYYY',
  },
  {
    label: 'DD-MM-YYYY',
    value: 'DD-MM-YYYY',
  },
  {
    label: 'DD/MM/YYYY',
    value: 'DD/MM/YYYY',
  },
]

export const DateTimeFormat = ({ condition: { value }, update }) => {
  const possibleDateTimeFormats = [
    ...possibleDateFormats,
    ...possibleDateFormats.map((option) => ({
      label: `${option.label} HH:MM`,
      value: `${option.value} HH:MM`,
    })),
  ]
  const [selectedFormat, setSelectedFormat] = useState('')
  return (
    <Select
      labelText="Date/Time Format"
      options={possibleDateTimeFormats}
      onChange={({ target: { value } }) => {
        update({ value })
        setSelectedFormat(value)
      }}
      selectedOption={{
        label: possibleDateTimeFormats.find(
          (option) => option.value === selectedFormat
        ),
        value: selectedFormat,
      }}
    />
  )
}
