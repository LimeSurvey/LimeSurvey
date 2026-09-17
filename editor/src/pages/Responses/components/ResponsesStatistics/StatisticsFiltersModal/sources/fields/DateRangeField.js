import React from 'react'

import { DateField } from './DateField'

// Date fields (submit date, date last action) → Start date + End date inputs
export const DateRangeField = ({ from, to, onFromChange, onToChange }) => (
  <div className="responses-statistics-filters-dates">
    <DateField
      value={from}
      placeholder={t('Start date')}
      onChange={onFromChange}
    />
    <DateField value={to} placeholder={t('End date')} onChange={onToChange} />
  </div>
)
