import { useState } from 'react'
import { format as formatDate } from 'date-fns'

import { DateRangePanel } from './DateRangePanel'
import { useDateRangePopover } from 'hooks/useDateRangePopover'
import { toDate } from 'helpers/dateRangeUtils'

export const DateRangePicker = ({
  startDate = null,
  endDate = null,
  minDate = new Date('2000-04-17T15:30'),
  maxDate = new Date('2050-04-17T15:30'),
  onUpdate = () => {},
  dateFormat = 'yyyy-MM-dd',
}) => {
  const initialRange = { from: toDate(startDate), to: toDate(endDate) }
  const [range, setRange] = useState(initialRange)
  const propsKey = `${initialRange.from?.getTime()}|${initialRange.to?.getTime()}`
  const [prevPropsKey, setPrevPropsKey] = useState(propsKey)
  if (propsKey !== prevPropsKey) {
    setPrevPropsKey(propsKey)
    setRange(initialRange)
  }
  const {
    showPicker,
    openPicker,
    closePicker,
    position,
    triggerRef,
    containerRef,
    clickRef,
    handleKeyDown,
  } = useDateRangePopover()

  const handleConfirm = (confirmedRange) => {
    setRange(confirmedRange)
    onUpdate([
      {
        startDate: formatDate(confirmedRange.from, dateFormat),
        endDate: formatDate(confirmedRange.to, dateFormat),
      },
    ])
    closePicker()
  }

  const handleClear = () => {
    setRange({ from: undefined, to: undefined })
    closePicker()
    onUpdate([
      {
        startDate: null,
        endDate: null,
        key: 'selection',
      },
    ])
  }

  return (
    <div className="date-range-picker">
      <button
        ref={triggerRef}
        type="button"
        className="date-range-placeholder"
        aria-haspopup="dialog"
        aria-expanded={showPicker}
        onClick={openPicker}
      >
        {range.from && formatDate(range.from, dateFormat)} —{' '}
        {range.to && formatDate(range.to, dateFormat)}
      </button>
      {showPicker && (
        <div
          ref={containerRef}
          role="dialog"
          aria-label={t('Select date range')}
          tabIndex={-1}
          className="date-range-container"
          style={{ top: position.top, left: position.left }}
          onKeyDown={handleKeyDown}
        >
          <div ref={clickRef}>
            <DateRangePanel
              key={propsKey}
              initialRange={range}
              minDate={minDate}
              maxDate={maxDate}
              dateFormat={dateFormat}
              onConfirm={handleConfirm}
              onCancel={closePicker}
              onClear={handleClear}
            />
          </div>
        </div>
      )}
    </div>
  )
}
