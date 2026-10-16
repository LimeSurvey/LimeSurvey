import {
  parseISO,
  isValid,
  isSameDay,
  startOfDay,
  subDays,
  startOfWeek,
  endOfWeek,
  subWeeks,
  startOfMonth,
  endOfMonth,
  subMonths,
} from 'date-fns'

export const WEEK_STARTS_ON = 1
export const EMPTY_DAY_COUNTS = { upTo: '', from: '' }

export const getFormatters = (language) => {
  const dateFormat = (options) => (date) =>
    new Intl.DateTimeFormat(language, options).format(date)

  return {
    formatCaption: dateFormat({ month: 'long', year: 'numeric' }),
    formatWeekdayName: dateFormat({ weekday: 'short' }),
    formatMonthDropdown: dateFormat({ month: 'long' }),
    formatYearDropdown: dateFormat({ year: 'numeric' }),
  }
}

export const getPresets = () => {
  const weekOptions = { weekStartsOn: WEEK_STARTS_ON }
  const today = startOfDay(new Date())
  const lastWeek = subWeeks(today, 1)
  const lastMonth = subMonths(today, 1)

  return [
    { label: t('Today'), range: { from: today, to: today } },
    {
      label: t('Yesterday'),
      range: { from: subDays(today, 1), to: subDays(today, 1) },
    },
    {
      label: t('This week'),
      range: {
        from: startOfWeek(today, weekOptions),
        to: endOfWeek(today, weekOptions),
      },
    },
    {
      label: t('Last week'),
      range: {
        from: startOfWeek(lastWeek, weekOptions),
        to: endOfWeek(lastWeek, weekOptions),
      },
    },
    {
      label: t('This month'),
      range: { from: startOfMonth(today), to: endOfMonth(today) },
    },
    {
      label: t('Last month'),
      range: { from: startOfMonth(lastMonth), to: endOfMonth(lastMonth) },
    },
  ]
}

export const isSameRange = (a, b) =>
  !!a?.from &&
  !!a?.to &&
  !!b?.from &&
  !!b?.to &&
  isSameDay(a.from, b.from) &&
  isSameDay(a.to, b.to)

export const toDate = (value) => {
  if (!value) return undefined
  const date = value instanceof Date ? value : parseISO(value)
  return isValid(date) ? date : undefined
}
