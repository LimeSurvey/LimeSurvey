import { useMemo } from 'react'
import { DayPicker } from '@daypicker/react'
import { addMonths, endOfMonth } from 'date-fns'
import { useTranslation } from 'react-i18next'

import { WEEK_STARTS_ON, getDayPickerLocale } from 'helpers/dateRangeUtils'

const formatters = {
  formatWeekdayName: (weekday, options, dateLib) =>
    dateLib.format(weekday, 'ccc'),
}

/**
 * DayPicker setup shared by the app's calendars. The displayed `month` is named
 * below the weekdays, and the next month's name follows a grid ending mid-week.
 */
export const useDayPickerSetup = (month) => {
  const { i18n } = useTranslation()
  const locale = useMemo(
    () => getDayPickerLocale(i18n?.language),
    [i18n?.language]
  )

  const endsMidWeek =
    month && (endOfMonth(month).getDay() + 7 - WEEK_STARTS_ON) % 7 !== 6
  const nextMonth = endsMidWeek ? addMonths(month, 1).getTime() : null
  const shownMonth = month ? month.getTime() : null

  const components = useMemo(() => {
    const monthLabelFormat = new Intl.DateTimeFormat(locale.code, {
      month: 'short',
      year: 'numeric',
    })

    return {
      Weekdays: (props) => (
        <thead aria-hidden>
          <tr {...props} />
          <tr>
            <th colSpan={7} className="date-range-month-label">
              {shownMonth !== null && monthLabelFormat.format(shownMonth)}
            </th>
          </tr>
        </thead>
      ),
      MonthGrid: (props) => (
        <>
          <table {...props} />
          {nextMonth !== null && (
            <div
              className="date-range-month-label date-range-next-month-label"
              aria-hidden="true"
            >
              {monthLabelFormat.format(nextMonth)}
            </div>
          )}
        </>
      ),
    }
  }, [locale.code, shownMonth, nextMonth])

  return { locale, formatters, components, weekStartsOn: WEEK_STARTS_ON }
}

export const DateRangeCalendar = ({
  selected,
  month,
  onMonthChange,
  onSelect,
  minDate,
  maxDate,
}) => {
  const { locale, components, weekStartsOn } = useDayPickerSetup(month)

  return (
    <div className="date-range-calendar">
      <DayPicker
        mode="range"
        captionLayout="dropdown"
        navLayout="around"
        showOutsideDays
        locale={locale}
        weekStartsOn={weekStartsOn}
        formatters={formatters}
        components={components}
        selected={selected}
        onSelect={onSelect}
        month={month}
        onMonthChange={onMonthChange}
        startMonth={minDate}
        endMonth={maxDate}
        disabled={[{ before: minDate }, { after: maxDate }]}
      />
    </div>
  )
}
