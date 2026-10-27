import { useState } from 'react'
import { isAfter, isBefore, isSameDay } from 'date-fns'
import classNames from 'classnames'

import { DateInput } from './DateInput'
import { useDayPickerSetup } from './DateRangeCalendar'

/**
 * Date inputs for a range: one input for a single day, two with "until" for
 * a longer range. Dates can be typed; the calendar icon calls `onOpenPicker`.
 */
export const DateRangePickerField = ({
  value,
  onChange,
  onOpenPicker,
  dateFormat = 'MMM d, yyyy',
  minDate = new Date('1900-01-01T00:00'),
  maxDate = new Date('2100-12-31T23:59'),
  className,
}) => {
  const { locale } = useDayPickerSetup()
  const [isFromValid, setIsFromValid] = useState(true)
  const [isToValid, setIsToValid] = useState(true)
  const isRange = !!value.to && !isSameDay(value.from, value.to)

  const commitFrom = (from) => {
    if (!from) return
    const to = isRange && !isAfter(from, value.to) ? value.to : from
    onChange({ from, to })
  }

  const commitTo = (to) => {
    if (!to) return
    onChange({ from: isBefore(to, value.from) ? to : value.from, to })
  }

  const inputProps = {
    dateFormat,
    locale,
    minDate,
    maxDate,
    onIconClick: onOpenPicker,
  }

  return (
    <div
      className={classNames(
        'date-range-picker date-range-picker-field',
        className
      )}
    >
      <div className="date-range-picker-field-inputs">
        <DateInput
          {...inputProps}
          label={isRange ? t('Start date') : t('Date')}
          value={value.from}
          onCommit={commitFrom}
          onValidityChange={setIsFromValid}
        />
        {isRange && (
          <>
            <span className="date-range-picker-field-until">{t('until')}</span>
            <DateInput
              {...inputProps}
              label={t('End date')}
              value={value.to}
              onCommit={commitTo}
              onValidityChange={setIsToValid}
            />
          </>
        )}
      </div>
      {(!isFromValid || (isRange && !isToValid)) && (
        <div className="date-range-error" role="alert">
          {t('Invalid date.')}
        </div>
      )}
    </div>
  )
}
