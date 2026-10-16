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
import * as dayPickerLocales from '@daypicker/react/locale'

export const WEEK_STARTS_ON = 1
export const EMPTY_DAY_COUNTS = { upTo: '', from: '' }

// LimeSurvey language codes that don't map directly to a DayPicker locale export.
const LOCALE_ALIASES = {
  'zh-Hans': 'zhCN',
  'zh-Hant-HK': 'zhHK',
  'zh-Hant-TW': 'zhTW',
  'en': 'enUS',
}

export const getDayPickerLocale = (language = 'en') => {
  const code = language.replace(/-(informal|easy)$/, '')
  const toExportName = (value) =>
    value.replace(/-([a-z])/gi, (_, char) => char.toUpperCase())
  const candidates = [
    LOCALE_ALIASES[code],
    toExportName(code),
    LOCALE_ALIASES[code.split('-')[0]],
    code.split('-')[0],
  ]

  const match = candidates.find((name) => name && dayPickerLocales[name])
  return dayPickerLocales[match] ?? dayPickerLocales.enUS
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
