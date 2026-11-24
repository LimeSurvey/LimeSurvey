import { useId, useMemo, useState } from 'react'

import { dayJsHelper, getDateFormatParts, getDateQuestionYears } from 'helpers'

const PART_ORDER = ['year', 'month', 'day', 'hour', 'minute']

/**
 * Returns the month labels for a dropdown_dates_month_style value, like the survey shows them.
 *
 * @param {number} monthStyle 0 short names, 1 full names, 2 numbers
 * @returns {string[]} The twelve month labels
 */
const getMonthLabels = (monthStyle) => {
  if (monthStyle === 1) {
    return [
      t('January'),
      t('February'),
      t('March'),
      t('April'),
      t('May'),
      t('June'),
      t('July'),
      t('August'),
      t('September'),
      t('October'),
      t('November'),
      t('December'),
    ]
  }

  if (monthStyle === 2) {
    return Array.from({ length: 12 }, (_, index) =>
      String(index + 1).padStart(2, '0')
    )
  }

  return [
    t('Jan'),
    t('Feb'),
    t('Mar'),
    t('Apr'),
    t('May'),
    t('Jun'),
    t('Jul'),
    t('Aug'),
    t('Sep'),
    t('Oct'),
    t('Nov'),
    t('Dec'),
  ]
}

/**
 * Splits a date into the selected values of the dropdowns.
 *
 * @param {import('dayjs').Dayjs|null} date The current date
 * @returns {{year: string, month: string, day: string, hour: string, minute: string}} The selected values
 */
const getSelectedParts = (date) => {
  if (!date?.isValid?.()) {
    return { year: '', month: '', day: '', hour: '', minute: '' }
  }

  return {
    year: String(date.year()),
    month: String(date.month() + 1),
    day: String(date.date()),
    hour: String(date.hour()),
    minute: String(date.minute()),
  }
}

/**
 * Adds the current value to the options when the list doesn't contain it, so existing answers
 * outside the limits or the minute step are still shown.
 *
 * @param {number[]} numbers The offered numbers
 * @param {string} current The selected value
 * @returns {number[]} The numbers including the current value
 */
const withCurrentValue = (numbers, current) => {
  if (current === '' || numbers.includes(Number(current))) {
    return numbers
  }

  return [...numbers, Number(current)].sort((a, b) => a - b)
}

/**
 * Date question shown as dropdowns (dropdown_dates), like the survey: One select per date part in
 * the order of the date format, with the format's separators between them.
 *
 * @param {object} props The component props
 * @param {object} props.settings The settings from getDateQuestionSettings()
 * @param {import('dayjs').Dayjs|null} props.value The current date
 * @param {Function} props.onChange Called with the complete date, or null when all dropdowns are cleared
 * @param {Function} props.onPartialChange Called with "incomplete" or "invalid" while no complete date is selected
 * @param {boolean} [props.disabled] Disable all dropdowns
 * @param {string} [props.ariaLabel] Accessible name of the group
 * @param {string} [props.ariaDescribedBy] Id of the element describing the group
 * @returns {JSX.Element} The dropdowns
 */
export const DateQuestionDropdowns = ({
  settings,
  value,
  onChange,
  onPartialChange,
  disabled = false,
  ariaLabel,
  ariaDescribedBy,
}) => {
  const id = useId()
  const [selected, setSelected] = useState(() => getSelectedParts(value))
  const parts = useMemo(
    () => getDateFormatParts(settings.format || 'YYYY-MM-DD'),
    [settings.format]
  )
  const shownTypes = useMemo(
    () => PART_ORDER.filter((type) => parts.some((part) => part.type === type)),
    [parts]
  )
  const years = useMemo(
    () =>
      getDateQuestionYears(
        settings.minDate,
        settings.maxDate,
        settings.reverse
      ),
    [settings.minDate, settings.maxDate, settings.reverse]
  )
  const monthLabels = useMemo(
    () => getMonthLabels(settings.monthStyle),
    [settings.monthStyle]
  )

  /**
   * Builds the date from the selected parts and reports it, like the survey's dropdown script.
   *
   * @param {object} nextSelected The selected values after the change
   * @returns {void}
   */
  const reportChange = (nextSelected) => {
    const values = shownTypes.map((type) => nextSelected[type])

    if (values.every((partValue) => partValue === '')) {
      onChange(null)
      return
    }

    if (values.some((partValue) => partValue === '')) {
      onPartialChange('incomplete')
      return
    }

    // The parts the format doesn't show are reset, like the survey does when saving
    const pick = (type, fallback) =>
      shownTypes.includes(type) ? Number(nextSelected[type]) : fallback
    const pad = (number) => String(number).padStart(2, '0')
    const dateString = `${String(pick('year', 1970)).padStart(4, '0')}-${pad(
      pick('month', 1)
    )}-${pad(pick('day', 1))} ${pad(pick('hour', 0))}:${pad(pick('minute', 0))}`
    const date = dayJsHelper(dateString, 'YYYY-MM-DD HH:mm', true)

    if (!date.isValid()) {
      onPartialChange('invalid', dateString)
      return
    }

    onChange(date)
  }

  const handleSelect = (type) => (event) => {
    const nextSelected = { ...selected, [type]: event.target.value }
    setSelected(nextSelected)
    reportChange(nextSelected)
  }

  /**
   * Renders one dropdown.
   *
   * @param {string} type The date part: year, month, day, hour or minute
   * @param {string} token The format token, used for zero padding
   * @param {number} index The position of the part, for a unique key
   * @returns {JSX.Element} The labelled select
   */
  const renderSelect = (type, token, index) => {
    const selectId = `${id}-${type}`
    const labels = {
      year: t('Year'),
      month: t('Month'),
      day: t('Day'),
      hour: t('Hour'),
      minute: t('Minute'),
    }
    const padded = token.length === 2
    let options = []

    if (type === 'year') {
      options = withCurrentValue(years, selected.year).map((year) => ({
        value: String(year),
        label: String(year),
      }))
      if (settings.reverse) {
        options.sort((a, b) => Number(b.value) - Number(a.value))
      }
    } else if (type === 'month') {
      options = monthLabels.map((label, monthIndex) => ({
        value: String(monthIndex + 1),
        label,
      }))
    } else if (type === 'day') {
      options = Array.from({ length: 31 }, (_, dayIndex) => ({
        value: String(dayIndex + 1),
        label: String(dayIndex + 1).padStart(2, '0'),
      }))
    } else if (type === 'hour') {
      options = Array.from({ length: 24 }, (_, hour) => ({
        value: String(hour),
        label: padded ? String(hour).padStart(2, '0') : String(hour),
      }))
    } else {
      const minutes = []
      for (let minute = 0; minute < 60; minute += settings.minuteStep) {
        minutes.push(minute)
      }
      options = withCurrentValue(minutes, selected.minute).map((minute) => ({
        value: String(minute),
        label: padded ? String(minute).padStart(2, '0') : String(minute),
      }))
    }

    return (
      <div className="col-auto" key={`${type}-${index}`}>
        <label htmlFor={selectId} className="visually-hidden">
          {labels[type]}
        </label>
        <select
          id={selectId}
          className={`form-select ${type}`}
          value={selected[type]}
          onChange={handleSelect(type)}
          disabled={disabled}
        >
          <option value="">{labels[type]}</option>
          {options.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>
    )
  }

  return (
    <div
      className="row g-1 align-items-center"
      role="group"
      aria-label={ariaLabel}
      aria-describedby={ariaDescribedBy}
      data-testid="date-question-dropdowns"
    >
      {parts.map((part, index) =>
        part.type === 'separator' ? (
          <div
            className="col-auto"
            key={`separator-${index}`}
            aria-hidden="true"
          >
            <span>{part.token}</span>
          </div>
        ) : (
          renderSelect(part.type, part.token, index)
        )
      )}
    </div>
  )
}
