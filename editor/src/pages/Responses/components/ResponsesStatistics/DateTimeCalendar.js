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

import { DateTimeDayModal } from './DateTimeDayModal.js'
import {
  formatDay,
  formatResponseCount,
  toDate,
  toDayKey,
} from './dateTimeUtils'

/**
 * Calendar view of a Date/time question: the range of entered dates above a
 * month grid whose days show how many answers entered that date. A day with
 * answers of a question that records times opens its time breakdown.
 */
export const DateTimeCalendar = ({
  surveyId,
  question = {},
  filters,
  stats,
  calendarState = {},
  onCalendarStateChange,
}) => {
  const [breakdownDay, setBreakdownDay] = useState(null)
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
      setBreakdownDay(toDayKey(date))
    }
  }

  // Picking another day in the breakdown moves the calendar along with it.
  const changeBreakdownDay = (dayKey) => {
    setBreakdownDay(dayKey)
    goTo(dayKey)
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

      <DateTimeDayModal
        surveyId={surveyId}
        question={question}
        filters={filters}
        day={breakdownDay}
        onDayChange={changeBreakdownDay}
        onHide={() => setBreakdownDay(null)}
      />
    </div>
  )
}
