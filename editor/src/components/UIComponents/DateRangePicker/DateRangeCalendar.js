import { useMemo } from 'react'
import { DayPicker } from '@daypicker/react'
import { addMonths } from 'date-fns'
import { useTranslation } from 'react-i18next'

import { WEEK_STARTS_ON, getFormatters } from 'helpers/dateRangeUtils'

export const DateRangeCalendar = ({
  selected,
  month,
  onMonthChange,
  onSelect,
  minDate,
  maxDate,
}) => {
  const { i18n } = useTranslation()
  const language = i18n?.language || 'en'
  const formatters = getFormatters(language)

  const monthLabelFormat = new Intl.DateTimeFormat(language, {
    month: 'short',
    year: 'numeric',
  })
  const monthLabel = monthLabelFormat.format(month)
  const nextMonthLabel = monthLabelFormat.format(addMonths(month, 1))

  const calendarComponents = useMemo(
    () => ({
      Weeks: ({ children, ...props }) => (
        <tbody {...props}>
          <tr className="date-range-month-label">
            <td colSpan={7}>{monthLabel}</td>
          </tr>
          {children}
          <tr className="date-range-month-label">
            <td colSpan={7}>{nextMonthLabel}</td>
          </tr>
        </tbody>
      ),
    }),
    [monthLabel, nextMonthLabel]
  )

  return (
    <div className="date-range-calendar">
      <DayPicker
        mode="range"
        captionLayout="dropdown"
        navLayout="around"
        showOutsideDays
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
