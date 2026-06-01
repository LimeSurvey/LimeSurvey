import { useState } from 'react'
import { Select } from '../Select/Select'

export default {
  title: 'UIComponents/Select',
  component: Select,
}

const USERS = [
  { value: '1', label: 'Jack' },
  { value: '2', label: 'Han' },
  { value: '3', label: 'Alex' },
  { value: '4', label: 'Lucia' },
]

export function Basic() {
  const [selected, setSelected] = useState(USERS[2])

  const handleChange = (event) => {
    setSelected(USERS.find((el) => el.value === event.target.value))
  }

  return (
    <Select
      size="md"
      labelText="Select"
      selectedOption={selected}
      onChange={handleChange}
      options={USERS}
    />
  )
}
