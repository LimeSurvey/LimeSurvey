import { dayJsHelper } from './dayJsHelper'
import { getAttributeValue } from './getAttributeValue'

// Same fallbacks the survey uses when a date question has no minimum or maximum date
export const DATE_QUESTION_DEFAULT_MIN = '1900-01-01'
export const DATE_QUESTION_DEFAULT_MAX = '2187-12-31 23:59:59'

// The format a date answer is stored in
export const DATE_QUESTION_STORAGE_FORMAT = 'YYYY-MM-DD HH:mm:ss'

// Mirrors QuestionAttribute::DATE_LIMIT_PATTERN: A YYYY-MM-DD date, optionally followed by a time
const DATE_LIMIT_PATTERN =
  /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])([ T]([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)?$/
const TIME_SUFFIX_PATTERN = /[ T][0-9]{2}:[0-9]{2}(:[0-9]{2})?$/
const FORMAT_TOKEN_PATTERN = /(YYYY|YY|MM|M|DD|D|HH|H|hh|h|mm|m)/

const TOKEN_TYPES = {
  YYYY: 'year',
  YY: 'year',
  MM: 'month',
  M: 'month',
  DD: 'day',
  D: 'day',
  HH: 'hour',
  H: 'hour',
  hh: 'hour',
  h: 'hour',
  mm: 'minute',
  m: 'minute',
}

/**
 * Returns the trimmed string value of a (possibly language keyed) question attribute.
 *
 * @param {object} question The question, with its attributes
 * @param {string} name The attribute name, e.g. "date_min"
 * @param {string} language The language code to read for language keyed attributes
 * @returns {string} The attribute value, or an empty string when it isn't set
 */
const getAttributeString = (question, name, language) => {
  const attribute = question?.attributes?.[name]
  if (attribute === undefined || attribute === null) {
    return ''
  }

  const value = getAttributeValue(attribute, language)

  return typeof value === 'string' || typeof value === 'number'
    ? String(value).trim()
    : ''
}

/**
 * Converts a date_min or date_max attribute into a date string the picker can use, like the survey does.
 * A 4-digit year means the start or end of that year, a YYYY-MM-DD date (with optional time) is used as is.
 * Expressions can't be evaluated here, so they fall back to the survey's default limit.
 *
 * @param {*} attributeValue The date_min or date_max value
 * @param {boolean} isMax True for date_max, false for date_min
 * @returns {string} The limit as a date string parseable by dayjs
 */
export const getDateQuestionLimit = (attributeValue, isMax) => {
  const limit =
    typeof attributeValue === 'string' || typeof attributeValue === 'number'
      ? String(attributeValue).trim()
      : ''

  if (/^[0-9]{4}$/.test(limit)) {
    return isMax ? `${limit}-12-31 23:59:59` : `${limit}-01-01`
  }

  if (DATE_LIMIT_PATTERN.test(limit)) {
    if (isMax && !TIME_SUFFIX_PATTERN.test(limit)) {
      return `${limit} 23:59:59`
    }

    return limit.replace('T', ' ')
  }

  return isMax ? DATE_QUESTION_DEFAULT_MAX : DATE_QUESTION_DEFAULT_MIN
}

/**
 * Converts a LimeSurvey date format (d, dd, m, mm, yy, yyyy, H, HH, M, MM) into a dayjs format,
 * like getJSDateFromDateFormat() in core: Lowercase m is the month, uppercase M the minutes.
 *
 * @param {string} dateFormat The LimeSurvey date format, e.g. "dd.mm.yyyy HH:MM"
 * @returns {string} The dayjs format, e.g. "DD.MM.YYYY HH:mm"
 */
export const convertSurveyDateFormat = (dateFormat = '') => {
  const map = { d: 'D', m: 'M', y: 'Y', M: 'm' }

  return dateFormat.replace(/[dmyM]/g, (character) => map[character])
}

/**
 * Converts a dayjs format back into a LimeSurvey date format, the reverse of
 * convertSurveyDateFormat().
 *
 * @param {string} format The dayjs format, e.g. "DD.MM.YYYY HH:mm"
 * @returns {string} The LimeSurvey date format, e.g. "dd.mm.yyyy HH:MM"
 */
export const convertToSurveyDateFormat = (format = '') => {
  const map = { D: 'd', M: 'm', Y: 'y', m: 'M' }

  return format.replace(/[DMYm]/g, (character) => map[character])
}

/**
 * Placeholder of an empty part of the picker's input: Its token in the LimeSurvey date format,
 * so the empty input reads like the format hint (dd.mm.yyyy, HH:MM) instead of MUI's DD.MM.YYYY
 * and hh:mm.
 *
 * @param {{format: string}} params The part's dayjs token from MUI, e.g. "DD"
 * @returns {string} The placeholder, e.g. "dd"
 */
const getSurveyFormatPlaceholder = ({ format }) =>
  convertToSurveyDateFormat(format)

/** MUI localeText for the picker's input placeholders in the LimeSurvey date format */
export const DATE_QUESTION_FIELD_PLACEHOLDERS = {
  fieldYearPlaceholder: getSurveyFormatPlaceholder,
  fieldMonthPlaceholder: getSurveyFormatPlaceholder,
  fieldDayPlaceholder: getSurveyFormatPlaceholder,
  fieldHoursPlaceholder: getSurveyFormatPlaceholder,
  fieldMinutesPlaceholder: getSurveyFormatPlaceholder,
}

/**
 * Returns the picker views (year, month, day, hours, minutes) a dayjs format contains.
 *
 * @param {string|undefined} format The dayjs format
 * @returns {string[]|undefined} The views in picker order, or undefined to keep the picker's default
 */
export const getDatePickerViews = (format) => {
  if (!format) {
    return undefined
  }

  const views = []
  if (format.includes('Y')) views.push('year')
  if (format.includes('M')) views.push('month')
  if (format.includes('D')) views.push('day')
  if (/[Hh]/.test(format)) views.push('hours')
  if (format.includes('m')) views.push('minutes')

  return views.length ? views : undefined
}

/**
 * Splits a dayjs format into its date parts and the separators between them, in display order,
 * like the survey does to order its dropdowns.
 *
 * @param {string|undefined} format The dayjs format, e.g. "DD.MM.YYYY HH:mm"
 * @returns {{type: string, token: string}[]} The parts; type is year, month, day, hour, minute or separator
 */
export const getDateFormatParts = (format) => {
  if (!format) {
    return []
  }

  return format
    .split(FORMAT_TOKEN_PATTERN)
    .filter((part) => part !== '')
    .map((part) => ({ type: TOKEN_TYPES[part] ?? 'separator', token: part }))
}

/**
 * Writes date part values in a dayjs format, also for dates dayjs can't format because they
 * don't exist (e.g. 31 February).
 *
 * @param {string|undefined} format The dayjs format, e.g. "DD/MM/YYYY"
 * @param {{year: (string|number), month: (string|number), day: (string|number), hour: (string|number), minute: (string|number)}} values The part values
 * @returns {string} The formatted date, e.g. "31/02/1989"
 */
export const formatDateParts = (format, values) =>
  getDateFormatParts(format)
    .map(({ type, token }) => {
      if (type === 'separator') {
        return token
      }

      const value = String(values?.[type] ?? '')
      if (token === 'YY') {
        return value.slice(-2)
      }

      return token.length === 2 ? value.padStart(2, '0') : value
    })
    .join('')

/**
 * Resets the parts of a date its format doesn't show, like the survey does when it saves a
 * date answer: A missing year becomes 1970, month January, day 1 and hours or minutes 0.
 * 29 February without a year doesn't exist in 1970, so the survey rejects it: It becomes an
 * invalid date instead of silently turning into 28 February.
 *
 * @param {import('dayjs').Dayjs} date The picked date
 * @param {string[]|undefined} views The picker views from getDatePickerViews()
 * @returns {import('dayjs').Dayjs} The normalized date, invalid when it doesn't exist in 1970
 */
export const normalizeDateToViews = (date, views) => {
  if (!date || !views) {
    return date
  }

  if (
    !views.includes('year') &&
    views.includes('month') &&
    views.includes('day') &&
    date.month() === 1 &&
    date.date() === 29
  ) {
    return dayJsHelper(NaN)
  }

  let normalized = date.second(0).millisecond(0)
  if (!views.includes('year')) normalized = normalized.year(1970)
  if (!views.includes('month')) normalized = normalized.month(0)
  if (!views.includes('day')) normalized = normalized.date(1)
  if (!views.includes('hours')) normalized = normalized.hour(0)
  if (!views.includes('minutes')) normalized = normalized.minute(0)

  return normalized
}

/**
 * Returns the years the survey offers in its year dropdown: From the year of the minimum date to
 * the year of the maximum date, the full default range when they're crossed, descending with
 * "reverse" set.
 *
 * @param {string} minDate The minimum date from getDateQuestionLimit()
 * @param {string} maxDate The maximum date from getDateQuestionLimit()
 * @param {boolean} reverse True to list the years from the latest to the earliest
 * @returns {number[]} The years
 */
export const getDateQuestionYears = (minDate, maxDate, reverse = false) => {
  let yearMin = parseInt(String(minDate).substring(0, 4), 10) || 1900
  let yearMax = parseInt(String(maxDate).substring(0, 4), 10) || 2187

  if (yearMin > yearMax) {
    yearMin = 1900
    yearMax = 2187
  }

  const years = []
  for (let year = yearMin; year <= yearMax; year++) {
    years.push(year)
  }

  return reverse ? years.reverse() : years
}

/**
 * Checks whether a date is between the minimum and maximum date.
 *
 * @param {import('dayjs').Dayjs} date The date to check
 * @param {string|import('dayjs').Dayjs} minDate The minimum date
 * @param {string|import('dayjs').Dayjs} maxDate The maximum date
 * @returns {boolean} True when the date is inside the limits
 */
export const isDateInLimits = (date, minDate, maxDate) =>
  !date.isBefore(dayJsHelper(minDate)) && !date.isAfter(dayJsHelper(maxDate))

/**
 * Returns the date format index of every survey language.
 *
 * @param {object} survey The survey, with its languageSettings
 * @returns {object} Language code => date format index
 */
export const getSurveyDateFormatsByLanguage = (survey) =>
  Object.fromEntries(
    Object.entries(survey?.languageSettings ?? {}).map(
      ([languageCode, languageSetting]) => [
        languageCode,
        languageSetting?.dateFormat,
      ]
    )
  )

/**
 * Collects everything the survey uses to show a date question: Limits, format, dropdown mode,
 * minute step, month style, year order and the format hint.
 *
 * @param {object} question The question, with its attributes
 * @param {string} language The current language code
 * @param {number|string|undefined} surveyDateFormat The survey language's date format index
 * @param {object|undefined} dateFormats The site's date formats (index => {dateformat, jsdate})
 * @returns {{minDate: string, maxDate: string, format: (string|undefined),
 *   displayFormat: string,
 *   views: (string[]|undefined), dropdown: boolean, minuteStep: number, monthStyle: number,
 *   reverse: boolean, showTip: boolean}} The date question settings
 */
export const getDateQuestionSettings = (
  question,
  language,
  surveyDateFormat,
  dateFormats
) => {
  const questionFormat = getAttributeString(question, 'date_format', language)
  const surveyFormat = dateFormats?.[surveyDateFormat]
  const format = questionFormat
    ? convertSurveyDateFormat(questionFormat)
    : surveyFormat?.jsdate || undefined
  const minuteStep = parseInt(
    getAttributeString(question, 'dropdown_dates_minute_step', language),
    10
  )
  const monthStyle = parseInt(
    getAttributeString(question, 'dropdown_dates_month_style', language),
    10
  )

  return {
    minDate: getDateQuestionLimit(
      getAttributeString(question, 'date_min', language),
      false
    ),
    maxDate: getDateQuestionLimit(
      getAttributeString(question, 'date_max', language),
      true
    ),
    format,
    displayFormat: questionFormat || surveyFormat?.dateformat || '',
    views: getDatePickerViews(format),
    dropdown: getAttributeString(question, 'dropdown_dates', language) === '1',
    minuteStep: minuteStep >= 1 && minuteStep <= 60 ? minuteStep : 1,
    monthStyle: [0, 1, 2].includes(monthStyle) ? monthStyle : 0,
    reverse: getAttributeString(question, 'reverse', language) === '1',
    showTip: getAttributeString(question, 'hide_tip', language) !== '1',
  }
}
