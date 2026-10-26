import { useMemo } from 'react'
import { DayPicker } from '@daypicker/react'
import { useTranslation } from 'react-i18next'

import { WEEK_STARTS_ON, getDayPickerLocale } from 'helpers/dateRangeUtils'

const formatters = {
  formatWeekdayName: (weekday, options, dateLib) =>
    dateLib.format(weekday, 'ccc'),
}

/**
 * DayPicker setup shared by the app's calendars
 */
export const useDayPickerSetup = () => {
  const { i18n } = useTranslation()
  const locale = useMemo(
    () => getDayPickerLocale(i18n?.language),
    [i18n?.language]
  )

  const components = useMemo(() => {
    const monthLabelFormat = new Intl.DateTimeFormat(locale.code, {
      month: 'short',
      year: 'numeric',
    })

    return {
      // eslint-disable-next-line no-unused-vars
      MonthCaption: ({ calendarMonth, displayIndex, ...props }) => (
        <>
          <div {...props} />
          <div className="date-range-month-label" aria-hidden="true">
            {monthLabelFormat.format(calendarMonth.date)}
          </div>
        </>
      ),
    }
  }, [locale.code])

  return { locale, formatters, components, weekStartsOn: WEEK_STARTS_ON }
}

export const DateRangeCalendar = ({
  mode = 'range',
  selected,
  month,
  onMonthChange,
  onSelect,
  minDate,
  maxDate,
}) => {
  const { locale, components, weekStartsOn } = useDayPickerSetup()

  return (
    <div className="date-range-calendar">
      <DayPicker
        mode={mode}
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
