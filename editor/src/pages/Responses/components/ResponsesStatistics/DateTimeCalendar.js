import { useMemo, useState } from 'react'
import { DayButton, DayPicker } from '@daypicker/react'
import {
  addMonths,
  endOfMonth,
  endOfYear,
  startOfMonth,
  startOfYear,
} from 'date-fns'
import classNames from 'classnames'

import { useDayPickerSetup } from 'components/UIComponents/DateRangePicker/DateRangeCalendar'
import { DateRangePanelModal } from 'components/UIComponents/DateRangePicker/DateRangePanelModal'
import { getStatisticsPresets } from 'helpers/dateRangeUtils'

import { DateTimeRangeModal } from './DateTimeRangeModal.js'
import {
  formatDay,
  formatResponseCount,
  toDate,
  toDayKey,
} from './dateTimeUtils'

/**
 * Calendar view of a Date/time question: the range of entered dates above a
 * month grid whose days show how many answers entered that date. A day with
 * answers of a question that records times opens its time breakdown, and
 * "View responses by date range" opens the breakdown of a picked range.
 */
export const DateTimeCalendar = ({
  surveyId,
  question = {},
  filters,
  stats,
  calendarState = {},
  onCalendarStateChange,
}) => {
  const [breakdownRange, setBreakdownRange] = useState(null)
  // The range picker; `returnTo` is the breakdown range it was opened from,
  // shown again on Cancel. Only one of the two modals is open at a time.
  const [rangePicker, setRangePicker] = useState(null)
  const { locale, formatters, components, weekStartsOn } = useDayPickerSetup()
  const days = useMemo(() => stats?.days ?? {}, [stats])
  const earliest = stats?.earliest ?? null
  const latest = stats?.latest ?? null

  const month =
    calendarState.month ?? startOfMonth(toDate(latest) ?? new Date())
  const selected = toDate(calendarState.selectedDate)
  const startMonth = startOfYear(
    toDate(earliest) && toDate(earliest) < month ? toDate(earliest) : month
  )
  const endMonth = endOfYear(
    toDate(latest) && toDate(latest) > month ? toDate(latest) : month
  )

  const update = (changes) =>
    onCalendarStateChange?.({ ...calendarState, month, ...changes })

  const goTo = (dayKey) =>
    update({ month: startOfMonth(toDate(dayKey)), selectedDate: dayKey })

  const handleDayClick = (date, modifiers) => {
    if (question.hasTime && !modifiers.outside && days[toDayKey(date)]) {
      const dayKey = toDayKey(date)
      setBreakdownRange({ from: dayKey, to: dayKey })
    }
  }

  // The last picked range is offered again the next time the picker opens.
  const confirmRange = ({ from, to }) => {
    const range = { from: toDayKey(from), to: toDayKey(to) }
    setRangePicker(null)
    setBreakdownRange(range)
    update({ range })
  }

  const openPickerFromBreakdown = () => {
    setRangePicker({ returnTo: breakdownRange })
    setBreakdownRange(null)
  }

  const cancelRangePicker = () => {
    setBreakdownRange(rangePicker?.returnTo ?? null)
    setRangePicker(null)
  }

  // The picker starts from the breakdown it was opened from, or the last range.
  const pickerRange = rangePicker?.returnTo ?? calendarState.range

  // Picking another single day in the breakdown moves the calendar with it.
  const changeBreakdownRange = (range) => {
    setBreakdownRange(range)
    update(
      range.from === range.to
        ? {
            month: startOfMonth(toDate(range.from)),
            selectedDate: range.from,
            range,
          }
        : { range }
    )
  }

  const endsMidWeek = (endOfMonth(month).getDay() + 7 - weekStartsOn) % 7 !== 6
  const nextMonthLabel = endsMidWeek
    ? new Intl.DateTimeFormat(locale.code, {
        month: 'short',
        year: 'numeric',
      }).format(addMonths(month, 1))
    : null

  const calendarComponents = useMemo(
    () => ({
      ...components,
      DayButton: ({ children, ...props }) => {
        const count = props.modifiers.outside
          ? 0
          : days[toDayKey(props.day.date)]
        return (
          <DayButton {...props}>
            <span className="responses-statistics-calendar-day-number">
              {children}
            </span>
            {count > 0 && (
              <span className="responses-statistics-calendar-day-count">
                {formatResponseCount(count)}
              </span>
            )}
          </DayButton>
        )
      },
      MonthGrid: (props) => (
        <>
          <table {...props} />
          {nextMonthLabel && (
            <div
              className="date-range-month-label responses-statistics-calendar-next-month"
              aria-hidden="true"
            >
              {nextMonthLabel}
            </div>
          )}
        </>
      ),
    }),
    [components, days, nextMonthLabel]
  )

  const labels = useMemo(() => {
    const dayLabelFormat = new Intl.DateTimeFormat(locale.code, {
      dateStyle: 'full',
    })
    return {
      labelDayButton: (date) => {
        const count = days[toDayKey(date)]
        const label = dayLabelFormat.format(date)
        return count ? `${label}, ${formatResponseCount(count)}` : label
      },
    }
  }, [days, locale.code])

  return (
    <>
      <div className="responses-statistics-calendar">
        <div className="responses-statistics-calendar-range">
          <span className="responses-statistics-calendar-range-label">
            {t('Range of responses:')}
          </span>
          <div className="responses-statistics-calendar-range-fields">
            <div className="responses-statistics-calendar-range-field">
              <input
                type="text"
                readOnly
                className="form-control"
                value={formatDay(earliest, locale.code)}
                aria-label={t('Earliest date')}
              />
              <button
                type="button"
                className="responses-statistics-calendar-range-link"
                disabled={!earliest}
                onClick={() => goTo(earliest)}
              >
                <i className="ri-arrow-left-line" aria-hidden="true"></i>
                {t('Go to earliest date')}
              </button>
            </div>
            <span className="responses-statistics-calendar-range-until">
              {t('until')}
            </span>
            <div className="responses-statistics-calendar-range-field">
              <input
                type="text"
                readOnly
                className="form-control"
                value={formatDay(latest, locale.code)}
                aria-label={t('Latest date')}
              />
              <button
                type="button"
                className={classNames(
                  'responses-statistics-calendar-range-link',
                  'responses-statistics-calendar-range-link--end'
                )}
                disabled={!latest}
                onClick={() => goTo(latest)}
              >
                {t('Go to latest date')}
                <i className="ri-arrow-right-line" aria-hidden="true"></i>
              </button>
            </div>
            <button
              type="button"
              className="responses-statistics-calendar-range-picker-link"
              aria-haspopup="dialog"
              onClick={() => setRangePicker({ returnTo: null })}
            >
              <i className="ri-calendar-line" aria-hidden="true"></i>
              {t('View responses by date range')}
            </button>
          </div>
        </div>

        <DayPicker
          mode="single"
          required
          captionLayout="dropdown"
          navLayout="around"
          showOutsideDays
          locale={locale}
          weekStartsOn={weekStartsOn}
          formatters={formatters}
          components={calendarComponents}
          labels={labels}
          selected={selected}
          onSelect={(date) => date && update({ selectedDate: toDayKey(date) })}
          onDayClick={handleDayClick}
          month={month}
          onMonthChange={(newMonth) => update({ month: newMonth })}
          startMonth={startMonth}
          endMonth={endMonth}
          modifiers={{ hasResponses: (date) => !!days[toDayKey(date)] }}
          modifiersClassNames={{
            hasResponses: 'responses-statistics-calendar-day--has-responses',
          }}
        />

        <DateTimeRangeModal
          surveyId={surveyId}
          question={question}
          filters={filters}
          range={breakdownRange}
          onRangeChange={changeBreakdownRange}
          onOpenPicker={openPickerFromBreakdown}
          onHide={() => setBreakdownRange(null)}
        />
      </div>
      <DateRangePanelModal
        show={!!rangePicker}
        onHide={cancelRangePicker}
        label={t('View responses by date range')}
        {...getStatisticsPresets()}
        initialRange={{
          from: toDate(pickerRange?.from),
          to: toDate(pickerRange?.to),
        }}
        initialMonth={toDate(pickerRange?.from) ?? month}
        dateFormat="MMM d, yyyy"
        locale={locale}
        separator={t('until')}
        collapseSingleDay
        confirmRequiresDate
        onConfirm={confirmRange}
      />
    </>
  )
}
