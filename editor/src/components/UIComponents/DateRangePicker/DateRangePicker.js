import { useState } from 'react'
import {
  format,
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
  inputFormat = 'MMM d, yyyy',
}) => {
  const initialRange = { from: toDate(startDate), to: toDate(endDate) }
  const [range, setRange] = useState(initialRange)
  const [tempRange, setTempRange] = useState(initialRange)
  const [month, setMonth] = useState(initialRange.from ?? new Date())
  const [dayCounts, setDayCounts] = useState(EMPTY_DAY_COUNTS)
  const {
    showPicker,
    openPicker,
    closePicker,
    position,
    triggerRef,
    containerRef,
    clickRef,
  } = useDateRangePopover()

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
    const to = tempRange.to && isAfter(from, tempRange.to) ? from : tempRange.to
    setTempRange({ from, to })
    setDayCounts(EMPTY_DAY_COUNTS)
    setMonth(from)
  }

  const handleToInput = (to) => {
    const from =
      !tempRange.from || isBefore(to, tempRange.from) ? to : tempRange.from
    setTempRange({ from, to })
    setDayCounts(EMPTY_DAY_COUNTS)
    setMonth(to)
  }

  const handleCalendarSelect = (selected) => {
    setDayCounts(EMPTY_DAY_COUNTS)
    setTempRange(selected ?? { from: undefined, to: undefined })
  }

  const handleConfirm = () => {
    if (!tempRange?.from) {
      handleClear()
      return
    }

    const confirmedRange = {
      from: tempRange.from,
      to: tempRange.to ?? tempRange.from,
    }
    setRange(confirmedRange)
    setTempRange(confirmedRange)

    onUpdate([
      {
        startDate: format(confirmedRange.from, dateFormat),
        endDate: format(confirmedRange.to, dateFormat),
      },
    ])
    closePicker()
  }

  const handleCancel = () => {
    setTempRange(range)
    setDayCounts(EMPTY_DAY_COUNTS)
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
    <div ref={triggerRef} className="date-range-picker">
      <div className="date-range-placeholder" onClick={openPicker}>
        {range.from && format(range.from, dateFormat)} —{' '}
        {range.to && format(range.to, dateFormat)}
      </div>
      {showPicker && (
        <div
          ref={containerRef}
          className="date-range-container"
          style={{ top: position.top, left: position.left }}
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
                  inputFormat={inputFormat}
                  fallbackFormat={dateFormat}
                  minDate={minDate}
                  maxDate={maxDate}
                  onCommit={handleFromInput}
                  onIconClick={setMonth}
                />
                <span>{t('until')}</span>
                <DateInput
                  label={t('End date')}
                  value={tempRange.to}
                  inputFormat={inputFormat}
                  fallbackFormat={dateFormat}
                  minDate={minDate}
                  maxDate={maxDate}
                  onCommit={handleToInput}
                  onIconClick={setMonth}
                />
              </div>
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
              />
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
