import { useState } from 'react'
import { format } from 'util'
import {
  format as formatDate,
  isBefore,
  isAfter,
  isSameDay,
  startOfDay,
  subDays,
  max,
  min,
} from 'date-fns'

import { DateInput } from './DateInput'
import { DateRangePresets } from './DateRangePresets'
import { DateRangeCalendar, useDayPickerSetup } from './DateRangeCalendar'
import { DateRangeActions } from './DateRangeActions'
import {
  CUSTOM_RANGE_PRESET,
  DATE_FORMAT,
  expandToUnit,
  getPresets,
} from 'helpers/dateRangeUtils'

const EMPTY_RANGE = { from: undefined, to: undefined }

export const DateRangePanel = ({
  initialRange = EMPTY_RANGE,
  initialMonth,
  minDate = new Date('1900-01-01T00:00'),
  maxDate = new Date('2100-12-31T23:59'),
  dateFormat = DATE_FORMAT,
  onConfirm,
  onCancel,
}) => {
  const { locale } = useDayPickerSetup()
  const { presets, trailingPresets } = getPresets()

  const [tempRange, setTempRange] = useState(initialRange)
  const [month, setMonth] = useState(
    initialMonth ?? initialRange.from ?? new Date()
  )
  const [daysUpToToday, setDaysUpToToday] = useState('')
  // Nothing is highlighted until something is picked.
  const [activeKey, setActiveKey] = useState(
    initialRange.from ? CUSTOM_RANGE_PRESET : null
  )
  const [isFromValid, setIsFromValid] = useState(true)
  const [isToValid, setIsToValid] = useState(true)
  // Clear starts the inputs over, along with any invalid text in them.
  const [inputsKey, setInputsKey] = useState(0)
  const [isEnteringEnd, setIsEnteringEnd] = useState(false)
  // The end date input shows up once the range has an end of its own.
  const isSingleDay =
    !isEnteringEnd &&
    !(tempRange.from && !tempRange.to) &&
    !(
      tempRange.from &&
      tempRange.to &&
      !isSameDay(tempRange.from, tempRange.to)
    )
  const hasInvalidInput = !isFromValid || (!isSingleDay && !isToValid)
  const selectionMode =
    [...presets, ...trailingPresets].find(({ key }) => key === activeKey)
      ?.mode ?? 'range'

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
        !daysUpToToday &&
        !!tempRange.from &&
        !!tempRange.to &&
        isSameDay(tempRange.from, preset.range.from) &&
        isSameDay(tempRange.to, preset.range.to)

  const handlePresetSelect = (preset) => {
    setDaysUpToToday('')
    setIsEnteringEnd(preset.mode === 'range')

    if (preset.range) {
      const clampedRange = clampRange(preset.range)
      setActiveKey(null)
      setTempRange(clampedRange)
      setMonth(clampedRange.from)
      return
    }

    setActiveKey(preset.key)
    const anchor =
      tempRange.from ??
      (preset.mode === 'month' || preset.mode === 'year' ? month : null)
    if (preset.mode !== 'range' && anchor) {
      setTempRange(clampRange(expandToUnit(anchor, preset.mode)))
    }
  }

  const handleMonthChange = (newMonth) => {
    setMonth(newMonth)
    if (selectionMode === 'month' || selectionMode === 'year') {
      setTempRange(clampRange(expandToUnit(newMonth, selectionMode)))
    }
  }

  const handleDaysUpToTodayChange = (e) => {
    const value = e.target.value
    setDaysUpToToday(value)
    setIsEnteringEnd(false)
    setActiveKey(null)

    if (!/^[1-9]\d*$/.test(value)) {
      setTempRange(EMPTY_RANGE)
      return
    }

    const days = parseInt(value, 10)
    const today = startOfDay(new Date())
    const newRange = clampRange({ from: subDays(today, days - 1), to: today })
    setTempRange(newRange)
    setMonth(newRange.from)
  }

  // Typing a date always makes it a custom range.
  const startCustomRange = () => {
    setDaysUpToToday('')
    setActiveKey(CUSTOM_RANGE_PRESET)
  }

  const handleFromInput = (from) => {
    startCustomRange()
    if (!from) {
      setTempRange(EMPTY_RANGE)
      return
    }
    let to = from
    if (!isSingleDay) {
      to =
        tempRange.to && !isAfter(from, tempRange.to) ? tempRange.to : undefined
    }
    setTempRange({ from, to })
    setMonth(from)
  }

  const handleToInput = (to) => {
    startCustomRange()
    if (!to) {
      setTempRange({ from: tempRange.from, to: undefined })
      return
    }
    setTempRange({
      from:
        !tempRange.from || isBefore(to, tempRange.from) ? to : tempRange.from,
      to,
    })
    setMonth(to)
  }

  const handleCalendarSelect = (selected, triggerDate) => {
    setDaysUpToToday('')

    if (selectionMode !== 'range') {
      setTempRange(clampRange(expandToUnit(triggerDate, selectionMode)))
      return
    }

    setActiveKey(CUSTOM_RANGE_PRESET)
    const isSelectingEnd = tempRange.from && !tempRange.to
    // click outside an in-progress selection starts a new range.
    setTempRange(
      isSelectingEnd && selected
        ? selected
        : { from: triggerDate, to: undefined }
    )
  }

  const handleConfirm = () => {
    if (hasInvalidInput || !tempRange.from) return

    onConfirm({ from: tempRange.from, to: tempRange.to ?? tempRange.from })
  }

  const handleClear = () => {
    setTempRange(EMPTY_RANGE)
    setDaysUpToToday('')
    setIsEnteringEnd(false)
    setActiveKey(null)
    setInputsKey((key) => key + 1)
  }

  return (
    <div className="date-range-component">
      <DateRangePresets
        presets={presets}
        trailingPresets={trailingPresets}
        isPresetActive={isPresetActive}
        daysUpToToday={daysUpToToday}
        onPresetSelect={handlePresetSelect}
        onDaysUpToTodayChange={handleDaysUpToTodayChange}
      />
      <div className="date-range-main">
        <div className="date-range-inputs" key={inputsKey}>
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
              <span aria-hidden="true">{t('until')}</span>
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
            {format(
              t('Invalid date, please enter a date like "%s".'),
              formatDate(new Date(), dateFormat, { locale })
            )}
          </div>
        )}
        <DateRangeCalendar
          selected={tempRange}
          month={month}
          onMonthChange={handleMonthChange}
          onSelect={handleCalendarSelect}
          minDate={minDate}
          maxDate={maxDate}
        />
        <DateRangeActions
          onClear={handleClear}
          onCancel={onCancel}
          onConfirm={handleConfirm}
          confirmDisabled={hasInvalidInput || !tempRange.from}
        />
      </div>
    </div>
  )
}
