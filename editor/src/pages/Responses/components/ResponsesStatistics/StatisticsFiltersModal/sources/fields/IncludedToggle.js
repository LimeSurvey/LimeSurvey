import React from 'react'

import { ToggleButtons } from 'components'

import { INCLUDED } from '../../utils'

const includedOptions = () => [
  { name: t('All'), value: INCLUDED.ALL },
  { name: t('Complete'), value: INCLUDED.COMPLETE },
  { name: t('Incomplete'), value: INCLUDED.INCOMPLETE },
]

// "Included responses" → All / Complete / Incomplete segmented toggle.
export const IncludedToggle = ({ id, value, onChange }) => (
  <ToggleButtons
    id={id}
    toggleOptions={includedOptions()}
    value={value}
    onChange={onChange}
  />
)
