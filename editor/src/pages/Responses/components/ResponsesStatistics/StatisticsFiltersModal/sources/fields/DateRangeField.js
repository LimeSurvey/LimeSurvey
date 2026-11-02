import React from 'react'

import { DateField } from './DateField'

// Date fields (submit date, date last action) → Start date + End date inputs.
// Each calendar is bounded by the other so the range can't be inverted.
export const DateRangeField = ({ from, to, onFromChange, onToChange }) => (
  <div className="responses-statistics-filters-dates">
    <DateField
      value={from}
      placeholder={t('Start date')}
      onChange={onFromChange}
      maxDate={to}
    />
    <DateField
      value={to}
      placeholder={t('End date')}
      onChange={onToChange}
      minDate={from}
    />
  </div>
)
