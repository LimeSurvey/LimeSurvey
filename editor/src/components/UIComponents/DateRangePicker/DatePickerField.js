import { useState } from 'react'
import classNames from 'classnames'

import { useDateRangePopover } from 'hooks/useDateRangePopover'

import { DateInput } from './DateInput'
import { DateRangeCalendar, useDayPickerSetup } from './DateRangeCalendar'

export const DatePickerField = ({
  value,
  onChange,
  label,
  dateFormat = 'MMM d, yyyy',
  minDate = new Date('1900-01-01T00:00'),
  maxDate = new Date('2100-12-31T23:59'),
  className,
}) => {
  const { locale } = useDayPickerSetup()
  const [month, setMonth] = useState(value ?? new Date())
  const [isValid, setIsValid] = useState(true)
  const {
    showPicker,
    openPicker,
    closePicker,
    position,
    triggerRef,
    containerRef,
    clickRef,
    handleKeyDown,
  } = useDateRangePopover(undefined, { positionOffset: 0 })

  const open = () => {
    setMonth(value ?? new Date())
    openPicker()
  }

  const select = (date) => {
    if (date) {
      onChange(date)
    }
    closePicker()
  }

  return (
    <div
      className={classNames('date-range-picker date-picker-field', className)}
    >
      <DateInput
        label={label}
        value={value}
        dateFormat={dateFormat}
        locale={locale}
        minDate={minDate}
        maxDate={maxDate}
        onCommit={(date) => date && onChange(date)}
        onValidityChange={setIsValid}
        onIconClick={open}
        iconRef={triggerRef}
      />
      {!isValid && (
        <div className="date-range-error" role="alert">
          {t('Invalid date.')}
        </div>
      )}
      {showPicker && (
        <div
          ref={containerRef}
          role="dialog"
          aria-label={label}
          tabIndex={-1}
          className="date-range-container date-picker-field-popover"
          style={{ top: position.top, left: position.left }}
          onKeyDown={handleKeyDown}
        >
          <div ref={clickRef} className="date-range-component">
            <DateRangeCalendar
              mode="single"
              selected={value}
              month={month}
              onMonthChange={setMonth}
              onSelect={select}
              minDate={minDate}
              maxDate={maxDate}
            />
          </div>
        </div>
      )}
    </div>
  )
}
