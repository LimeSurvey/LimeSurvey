import { useMemo } from 'react'
import { DayPicker } from '@daypicker/react'
import { useTranslation } from 'react-i18next'

import { WEEK_STARTS_ON, getDayPickerLocale } from 'helpers/dateRangeUtils'

const formatters = {
  formatWeekdayName: (weekday, options, dateLib) =>
    dateLib.format(weekday, 'ccc'),
}

export const DateRangeCalendar = ({
  selected,
  month,
  onMonthChange,
  onSelect,
  minDate,
  maxDate,
}) => {
  const { i18n } = useTranslation()
  const locale = useMemo(
    () => getDayPickerLocale(i18n?.language),
    [i18n?.language]
  )

  const calendarComponents = useMemo(() => {
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

  return (
    <div className="date-range-calendar">
      <DayPicker
        mode="range"
        captionLayout="dropdown"
        navLayout="around"
        showOutsideDays
        locale={locale}
        weekStartsOn={WEEK_STARTS_ON}
        formatters={formatters}
        components={calendarComponents}
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
