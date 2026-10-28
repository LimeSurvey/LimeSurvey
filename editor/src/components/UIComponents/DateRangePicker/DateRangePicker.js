import { useState } from 'react'
import { format } from 'util'
import {
  format as formatDate,
  isBefore,
  isAfter,
  startOfDay,
  addDays,
  subDays,
  max,
  min,
} from 'date-fns'

import { DateInput } from './DateInput'
import { DateRangePresets } from './DateRangePresets'
import { DateRangeCalendar } from './DateRangeCalendar'
import { DateRangeActions } from './DateRangeActions'
import { useDateRangePopover } from 'hooks/useDateRangePopover'
import { EMPTY_DAY_COUNTS, toDate } from 'helpers/dateRangeUtils'

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
  const [tempRange, setTempRange] = useState(initialRange)
  const [month, setMonth] = useState(initialRange.from ?? new Date())
  const [dayCounts, setDayCounts] = useState(EMPTY_DAY_COUNTS)
  const [isFromValid, setIsFromValid] = useState(true)
  const [isToValid, setIsToValid] = useState(true)
  const hasInvalidInput = !isFromValid || !isToValid
  const propsKey = `${initialRange.from?.getTime()}|${initialRange.to?.getTime()}`
  const [prevPropsKey, setPrevPropsKey] = useState(propsKey)
  if (propsKey !== prevPropsKey) {
    setPrevPropsKey(propsKey)
    setRange(initialRange)
    setTempRange(initialRange)
    setMonth(initialRange.from ?? new Date())
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
  } = useDateRangePopover(() => handleCancel())

  const clampDate = (date) =>
    min([max([date, startOfDay(minDate)]), startOfDay(maxDate)])

  const applyPreset = (presetRange) => {
    const clampedRange = {
      from: clampDate(presetRange.from),
      to: clampDate(presetRange.to),
    }
    setTempRange(clampedRange)
    setDayCounts(EMPTY_DAY_COUNTS)
    setMonth(clampedRange.from)
  }

  const handleDayCountChange = (key) => (e) => {
    const value = e.target.value
    setDayCounts({ ...EMPTY_DAY_COUNTS, [key]: value })

    const days = parseInt(value, 10)
    if (!days || days < 1) return

    const today = startOfDay(new Date())
    const rawRange =
      key === 'upTo'
        ? { from: subDays(today, days - 1), to: today }
        : { from: today, to: addDays(today, days - 1) }
    const newRange = {
      from: clampDate(rawRange.from),
      to: clampDate(rawRange.to),
    }
    setTempRange(newRange)
    setMonth(newRange.from)
  }

  const handleFromInput = (from) => {
    if (!from) {
      setTempRange({ from: undefined, to: tempRange.to })
      setDayCounts(EMPTY_DAY_COUNTS)
      return
    }
    const to = tempRange.to && isAfter(from, tempRange.to) ? from : tempRange.to
    setTempRange({ from, to })
    setDayCounts(EMPTY_DAY_COUNTS)
    setMonth(from)
  }

  const handleToInput = (to) => {
    if (!to) {
      setTempRange({ from: tempRange.from, to: undefined })
      setDayCounts(EMPTY_DAY_COUNTS)
      return
    }
    const from =
      !tempRange.from || isBefore(to, tempRange.from) ? to : tempRange.from
    setTempRange({ from, to })
    setDayCounts(EMPTY_DAY_COUNTS)
    setMonth(to)
  }

  const handleCalendarSelect = (selected, triggerDate) => {
    setDayCounts(EMPTY_DAY_COUNTS)
    const isSelectingEnd = tempRange.from && !tempRange.to
    // click outside an in-progress selection starts a new range.
    setTempRange(
      isSelectingEnd && selected
        ? selected
        : { from: triggerDate, to: undefined }
    )
  }

  const handleConfirm = () => {
    if (hasInvalidInput) return

    const from = tempRange.from ?? tempRange.to
    if (!from) {
      handleClear()
      return
    }

    const confirmedRange = {
      from,
      to: tempRange.to ?? from,
    }
    setRange(confirmedRange)
    setTempRange(confirmedRange)

    onUpdate([
      {
        startDate: formatDate(confirmedRange.from, dateFormat),
        endDate: formatDate(confirmedRange.to, dateFormat),
      },
    ])
    closePicker()
  }

  const handleCancel = () => {
    setTempRange(range)
    setDayCounts(EMPTY_DAY_COUNTS)
    setMonth(range.from ?? new Date())
    closePicker()
  }

  const handleClear = () => {
    setRange({ from: undefined, to: undefined })
    setTempRange({ from: undefined, to: undefined })
    setDayCounts(EMPTY_DAY_COUNTS)

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
          <div ref={clickRef} className="date-range-component">
            <DateRangePresets
              tempRange={tempRange}
              dayCounts={dayCounts}
              onPresetSelect={applyPreset}
              onDayCountChange={handleDayCountChange}
            />
            <div className="date-range-main">
              <div className="date-range-inputs">
                <DateInput
                  label={t('Start date')}
                  value={tempRange.from}
                  dateFormat={dateFormat}
                  minDate={minDate}
                  maxDate={maxDate}
                  onCommit={handleFromInput}
                  onValidityChange={setIsFromValid}
                />
                <span aria-hidden="true">–</span>
                <DateInput
                  label={t('End date')}
                  value={tempRange.to}
                  dateFormat={dateFormat}
                  minDate={minDate}
                  maxDate={maxDate}
                  onCommit={handleToInput}
                  onValidityChange={setIsToValid}
                />
              </div>
              {hasInvalidInput && (
                <div className="date-range-error" role="alert">
                  {format(
                    t('Invalid date, please use "%s" format.'),
                    dateFormat
                  )}
                </div>
              )}
              <DateRangeCalendar
                selected={tempRange}
                month={month}
                onMonthChange={setMonth}
                onSelect={handleCalendarSelect}
                minDate={minDate}
                maxDate={maxDate}
              />
              <DateRangeActions
                onClear={handleClear}
                onCancel={handleCancel}
                onConfirm={handleConfirm}
                confirmDisabled={hasInvalidInput}
              />
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
