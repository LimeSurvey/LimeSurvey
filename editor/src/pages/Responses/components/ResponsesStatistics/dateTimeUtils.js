import { format } from 'util'

import { getDayPickerLocale } from 'helpers/dateRangeUtils'

const ANSWER_PATTERN =
  /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/

export const parseAnswerDateTime = (value) => {
  const match = ANSWER_PATTERN.exec(String(value ?? '').trim())
  if (!match) {
    return null
  }
  const [, year, month, day, hours, minutes, seconds = '00'] = match
  if (!toDate(`${year}-${month}-${day}`)) {
    return null
  }

  return {
    date: `${year}-${month}-${day}`,
    time: hours === undefined ? null : `${hours}:${minutes}:${seconds}`,
  }
}

export const toDate = (dayKey) => {
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(dayKey ?? '')
  if (!match) {
    return undefined
  }
  const [year, month, day] = match.slice(1).map(Number)
  const date = new Date(year, month - 1, day)

  return date.getMonth() === month - 1 && date.getDate() === day
    ? date
    : undefined
}

export const toDayKey = (date) =>
  [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0'),
  ].join('-')

export const getLocaleCode = (language) => getDayPickerLocale(language).code

export const formatDay = (dayKey, localeCode) => {
  const date = toDate(dayKey)
  if (!date) {
    return ''
  }

  return new Intl.DateTimeFormat(localeCode, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date)
}

export const formatTime = (time, localeCode) => {
  const match = /^(\d{2}):(\d{2})/.exec(time ?? '')
  if (!match) {
    return ''
  }

  return new Intl.DateTimeFormat(localeCode, {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(2000, 0, 1, Number(match[1]), Number(match[2])))
}

export const formatResponseCount = (count) =>
  count === 1 ? t('1 response') : format(t('%s responses'), count)
