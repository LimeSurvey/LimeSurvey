import React from 'react'

import { Input } from 'components'

// Numeric fields (response ID, seed) → Min + Max number inputs.
export const NumberRangeField = ({ min, max, onMinChange, onMaxChange }) => (
  <div className="responses-statistics-filters-row-range">
    <Input
      type="number"
      placeholder={t('Min')}
      value={min}
      update={onMinChange}
    />
    <Input
      type="number"
      placeholder={t('Max')}
      value={max}
      update={onMaxChange}
    />
  </div>
)
