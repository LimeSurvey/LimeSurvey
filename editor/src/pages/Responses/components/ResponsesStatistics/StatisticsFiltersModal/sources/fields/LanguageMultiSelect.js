import React, { useMemo } from 'react'

import { FilterSelect } from '../../FilterSelect'

// "Language" → multi-select of the survey's languages (chips).
export const LanguageMultiSelect = ({ languages = [], value = [], onChange }) => {
  const options = useMemo(
    () => languages.map((lang) => ({ value: lang, label: lang })),
    [languages]
  )

  // Multiselect needs the selected option objects, not just their values.
  const selected = options.filter((option) => value.includes(option.value))

  return (
    <FilterSelect
      options={options}
      value={selected}
      isMultiselect
      placeholder={t('Please select ...')}
      update={onChange}
    />
  )
}
