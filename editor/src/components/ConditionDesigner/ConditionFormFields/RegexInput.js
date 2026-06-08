import { useCallback } from 'react'

export const RegexInput = ({ index, condition, updateCondition }) => {
  const handleChange = useCallback(
    (e) => updateCondition(index, 'value', e.target.value),
    [index]
  )

  return (
    <textarea
      value={condition.value}
      onChange={handleChange}
      placeholder={t('Enter regular expression')}
      className="mb-3 p-2 w-100 form-control"
      rows={5}
    />
  )
}
