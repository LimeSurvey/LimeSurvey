import {
  startOfDay,
  startOfWeek,
  endOfWeek,
  startOfMonth,
  endOfMonth,
  startOfYear,
  endOfYear,
} from 'date-fns'
import * as dayPickerLocales from '@daypicker/react/locale'

export const WEEK_STARTS_ON = 1
export const DATE_FORMAT = 'MMM d, yyyy'
export const CUSTOM_RANGE_PRESET = 'custom'

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

// A preset with a `range` selects it; one with a `mode` changes what a calendar
// click selects. The trailing ones sit below the days input.
export const getPresets = () => {
  const today = startOfDay(new Date())

  return {
    presets: [
      { key: 'today', label: t('Today'), range: { from: today, to: today } },
      { key: 'week', label: t('Select week'), mode: 'week' },
      { key: 'month', label: t('Select month'), mode: 'month' },
      { key: 'year', label: t('Select year'), mode: 'year' },
    ],
    trailingPresets: [
      { key: CUSTOM_RANGE_PRESET, label: t('Custom range'), mode: 'range' },
    ],
  }
}

// The week (Mon–Sun), month or year a date falls in.
export const expandToUnit = (date, mode) => {
  switch (mode) {
    case 'week':
      return {
        from: startOfWeek(date, { weekStartsOn: WEEK_STARTS_ON }),
        to: endOfWeek(date, { weekStartsOn: WEEK_STARTS_ON }),
      }
    case 'month':
      return { from: startOfMonth(date), to: endOfMonth(date) }
    case 'year':
      return { from: startOfYear(date), to: endOfYear(date) }
    default:
      return { from: date, to: date }
  }
}
