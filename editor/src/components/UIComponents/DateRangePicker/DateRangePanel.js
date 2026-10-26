import { useState } from 'react'
import { format } from 'util'
import {
  isBefore,
  isAfter,
  isSameDay,
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
import {
  EMPTY_DAY_COUNTS,
  expandToUnit,
  getPresets,
} from 'helpers/dateRangeUtils'

const EMPTY_RANGE = { from: undefined, to: undefined }

export const DateRangePanel = ({
  initialRange = EMPTY_RANGE,
  initialMonth,
  presets,
  trailingPresets = [],
  dayCountKeys = ['upTo', 'from'],
  minDate,
  maxDate,
  dateFormat,
  locale,
  separator = '–',
  collapseSingleDay = false,
  confirmRequiresDate = false,
  onConfirm,
  onCancel,
  onClear,
}) => {
  const presetList = presets ?? getPresets()
  const rangeModeKey =
    [...presetList, ...trailingPresets].find(({ mode }) => mode === 'range')
      ?.key ?? null

  const [tempRange, setTempRange] = useState(initialRange)
  const [month, setMonth] = useState(
    initialMonth ?? initialRange.from ?? new Date()
  )
  const [dayCounts, setDayCounts] = useState(EMPTY_DAY_COUNTS)
  const [activeKey, setActiveKey] = useState(rangeModeKey)
  const [isFromValid, setIsFromValid] = useState(true)
  const [isToValid, setIsToValid] = useState(true)
  const hasInvalidInput = !isFromValid || !isToValid
  const hasDayCount = !!(dayCounts.upTo || dayCounts.from)
  const selectionMode =
    [...presetList, ...trailingPresets].find(({ key }) => key === activeKey)
      ?.mode ?? 'range'
  const isSingleDay =
    collapseSingleDay &&
    activeKey === null &&
    !!tempRange.from &&
    !!tempRange.to &&
    isSameDay(tempRange.from, tempRange.to)

  const clampDate = (date) =>
    min([max([date, startOfDay(minDate)]), startOfDay(maxDate)])

  const clampRange = (range) => ({
    from: clampDate(range.from),
    to: clampDate(range.to),
  })

  const isPresetActive = (preset) =>
    preset.mode
      ? activeKey === preset.key
      : activeKey === null &&
        !hasDayCount &&
        !!tempRange.from &&
        !!tempRange.to &&
        isSameDay(tempRange.from, preset.range.from) &&
        isSameDay(tempRange.to, preset.range.to)

  const handlePresetSelect = (preset) => {
    setDayCounts(EMPTY_DAY_COUNTS)

    if (preset.range) {
      const clampedRange = clampRange(preset.range)
      // The end date input may be hidden for a single day.
      setIsToValid(true)
      setActiveKey(null)
      setTempRange(clampedRange)
      setMonth(clampedRange.from)
      return
    }

    setActiveKey(preset.key)
    if (preset.mode !== 'range' && tempRange.from) {
      setTempRange(clampRange(expandToUnit(tempRange.from, preset.mode)))
    }
  }

  const handleDayCountChange = (key) => (e) => {
    const value = e.target.value
    setDayCounts({ ...EMPTY_DAY_COUNTS, [key]: value })
    setActiveKey(null)

    const days = parseInt(value, 10)
    if (!days || days < 1) return

    const today = startOfDay(new Date())
    const newRange = clampRange(
      key === 'upTo'
        ? { from: subDays(today, days - 1), to: today }
        : { from: today, to: addDays(today, days - 1) }
    )
    setTempRange(newRange)
    setMonth(newRange.from)
  }

  // Typing a date always makes it a custom range.
  const startCustomRange = () => {
    setDayCounts(EMPTY_DAY_COUNTS)
    setActiveKey(rangeModeKey)
  }

  const handleFromInput = (from) => {
    startCustomRange()
    if (!from) {
      setTempRange({ from: undefined, to: tempRange.to })
      return
    }
    const to = tempRange.to && isAfter(from, tempRange.to) ? from : tempRange.to
    setTempRange({ from, to })
    setMonth(from)
  }

  const handleToInput = (to) => {
    startCustomRange()
    if (!to) {
      setTempRange({ from: tempRange.from, to: undefined })
      return
    }
    const from =
      !tempRange.from || isBefore(to, tempRange.from) ? to : tempRange.from
    setTempRange({ from, to })
    setMonth(to)
  }

  const handleCalendarSelect = (selected, triggerDate) => {
    setDayCounts(EMPTY_DAY_COUNTS)

    if (selectionMode !== 'range') {
      setTempRange(clampRange(expandToUnit(triggerDate, selectionMode)))
      return
    }

    setActiveKey(rangeModeKey)
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

    onConfirm({ from, to: tempRange.to ?? from })
  }

  const handleClear = () => {
    setTempRange(EMPTY_RANGE)
    setDayCounts(EMPTY_DAY_COUNTS)
    setActiveKey(rangeModeKey)
    onClear()
  }

  return (
    <div className="date-range-component">
      <DateRangePresets
        presets={presetList}
        trailingPresets={trailingPresets}
        dayCountKeys={dayCountKeys}
        isPresetActive={isPresetActive}
        dayCounts={dayCounts}
        onPresetSelect={handlePresetSelect}
        onDayCountChange={handleDayCountChange}
      />
      <div className="date-range-main">
        <div className="date-range-inputs">
          <DateInput
            label={t('Start date')}
            value={tempRange.from}
            dateFormat={dateFormat}
            locale={locale}
            minDate={minDate}
            maxDate={maxDate}
            onCommit={handleFromInput}
            onValidityChange={setIsFromValid}
          />
          {!isSingleDay && (
            <>
              <span aria-hidden="true">{separator}</span>
              <DateInput
                label={t('End date')}
                value={tempRange.to}
                dateFormat={dateFormat}
                locale={locale}
                minDate={minDate}
                maxDate={maxDate}
                onCommit={handleToInput}
                onValidityChange={setIsToValid}
              />
            </>
          )}
        </div>
        {hasInvalidInput && (
          <div className="date-range-error" role="alert">
            {format(t('Invalid date, please use "%s" format.'), dateFormat)}
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
          onCancel={onCancel}
          onConfirm={handleConfirm}
          confirmDisabled={
            hasInvalidInput ||
            (confirmRequiresDate && !tempRange.from && !tempRange.to)
          }
        />
      </div>
    </div>
  )
}
