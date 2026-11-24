import dayjs from 'dayjs'

import {
  DATE_QUESTION_DEFAULT_MAX,
  DATE_QUESTION_DEFAULT_MIN,
  convertSurveyDateFormat,
  getDateFormatParts,
  getDatePickerViews,
  getDateQuestionLimit,
  getDateQuestionSettings,
  getDateQuestionYears,
  getSurveyDateFormatsByLanguage,
  isDateInLimits,
  normalizeDateToViews,
} from './dateQuestionUtils'

const dateFormats = {
  1: { dateformat: 'dd.mm.yyyy', jsdate: 'DD.MM.YYYY' },
  6: { dateformat: 'yyyy-mm-dd', jsdate: 'YYYY-MM-DD' },
}

describe('getDateQuestionLimit', () => {
  it('falls back to the survey defaults when no limit is set', () => {
    expect(getDateQuestionLimit('', false)).toBe(DATE_QUESTION_DEFAULT_MIN)
    expect(getDateQuestionLimit(undefined, true)).toBe(
      DATE_QUESTION_DEFAULT_MAX
    )
  })

  it('treats a 4-digit year as the start or end of that year', () => {
    expect(getDateQuestionLimit('1920', false)).toBe('1920-01-01')
    expect(getDateQuestionLimit(2080, true)).toBe('2080-12-31 23:59:59')
  })

  it('uses a full date as is and extends a maximum date to the end of the day', () => {
    expect(getDateQuestionLimit('1950-05-17', false)).toBe('1950-05-17')
    expect(getDateQuestionLimit('2060-05-17', true)).toBe('2060-05-17 23:59:59')
    expect(getDateQuestionLimit('2060-05-17T10:30', true)).toBe(
      '2060-05-17 10:30'
    )
  })

  it('falls back to the defaults for expressions it cannot evaluate', () => {
    expect(getDateQuestionLimit('{TODAY}', false)).toBe(
      DATE_QUESTION_DEFAULT_MIN
    )
    expect(getDateQuestionLimit('Q1', true)).toBe(DATE_QUESTION_DEFAULT_MAX)
  })
})

describe('convertSurveyDateFormat', () => {
  it('converts LimeSurvey tokens to dayjs tokens', () => {
    expect(convertSurveyDateFormat('dd.mm.yyyy HH:MM')).toBe('DD.MM.YYYY HH:mm')
    expect(convertSurveyDateFormat('mm/yyyy')).toBe('MM/YYYY')
    expect(convertSurveyDateFormat('d/m/yy H:M')).toBe('D/M/YY H:m')
  })
})

describe('getDateQuestionSettings', () => {
  const settingsFor = (attributes, surveyDateFormat = 1) =>
    getDateQuestionSettings({ attributes }, 'en', surveyDateFormat, dateFormats)

  it('prefers the question format over the survey format', () => {
    const settings = settingsFor({ date_format: 'mm/yyyy' })
    expect(settings.format).toBe('MM/YYYY')
    expect(settings.displayFormat).toBe('mm/yyyy')
    expect(settings.views).toEqual(['year', 'month'])
  })

  it('reads language keyed question formats', () => {
    expect(settingsFor({ date_format: { en: 'yyyy-mm-dd' } }).format).toBe(
      'YYYY-MM-DD'
    )
  })

  it('uses the survey language format when the question has none', () => {
    expect(settingsFor({}).format).toBe('DD.MM.YYYY')
    expect(settingsFor({}).displayFormat).toBe('dd.mm.yyyy')
    expect(settingsFor({ date_format: null }, '6').format).toBe('YYYY-MM-DD')
  })

  it('leaves the format to the picker when none is known', () => {
    const settings = getDateQuestionSettings({}, 'en', undefined, undefined)
    expect(settings.format).toBeUndefined()
    expect(settings.views).toBeUndefined()
  })

  it('reads limits, dropdown mode, minute step, month style, order and tip', () => {
    expect(
      settingsFor({
        date_min: '1900',
        date_max: '2030-06-30',
        dropdown_dates: '1',
        dropdown_dates_minute_step: '15',
        dropdown_dates_month_style: '1',
        reverse: '1',
        hide_tip: '1',
      })
    ).toEqual({
      minDate: '1900-01-01',
      maxDate: '2030-06-30 23:59:59',
      format: 'DD.MM.YYYY',
      displayFormat: 'dd.mm.yyyy',
      views: ['year', 'month', 'day'],
      dropdown: true,
      minuteStep: 15,
      monthStyle: 1,
      reverse: true,
      showTip: false,
    })
  })

  it('uses the survey defaults for missing or broken settings', () => {
    const settings = settingsFor({
      dropdown_dates_minute_step: '0',
      dropdown_dates_month_style: '7',
    })
    expect(settings.minDate).toBe(DATE_QUESTION_DEFAULT_MIN)
    expect(settings.maxDate).toBe(DATE_QUESTION_DEFAULT_MAX)
    expect(settings.dropdown).toBe(false)
    expect(settings.minuteStep).toBe(1)
    expect(settings.monthStyle).toBe(0)
    expect(settings.reverse).toBe(false)
    expect(settings.showTip).toBe(true)
  })
})

describe('getDateFormatParts', () => {
  it('splits a format into date parts and separators', () => {
    expect(getDateFormatParts('DD.MM.YYYY HH:mm')).toEqual([
      { type: 'day', token: 'DD' },
      { type: 'separator', token: '.' },
      { type: 'month', token: 'MM' },
      { type: 'separator', token: '.' },
      { type: 'year', token: 'YYYY' },
      { type: 'separator', token: ' ' },
      { type: 'hour', token: 'HH' },
      { type: 'separator', token: ':' },
      { type: 'minute', token: 'mm' },
    ])
  })

  it('returns nothing without a format', () => {
    expect(getDateFormatParts(undefined)).toEqual([])
  })
})

describe('getDateQuestionYears', () => {
  it('lists the years between the limits', () => {
    expect(getDateQuestionYears('2020-01-01', '2023-12-31 23:59:59')).toEqual([
      2020, 2021, 2022, 2023,
    ])
  })

  it('lists them from the latest with reverse set', () => {
    expect(getDateQuestionYears('2020-01-01', '2022-12-31', true)).toEqual([
      2022, 2021, 2020,
    ])
  })

  it('uses the full default range when the limits are crossed', () => {
    const years = getDateQuestionYears('2030-01-01', '2020-12-31')
    expect(years[0]).toBe(1900)
    expect(years[years.length - 1]).toBe(2187)
  })
})

describe('isDateInLimits', () => {
  it('accepts dates on and between the limits only', () => {
    const min = '1950-01-01'
    const max = '1950-12-31 23:59:59'
    expect(isDateInLimits(dayjs('1950-01-01 00:00'), min, max)).toBe(true)
    expect(isDateInLimits(dayjs('1950-12-31 23:59'), min, max)).toBe(true)
    expect(isDateInLimits(dayjs('1949-12-31 23:59'), min, max)).toBe(false)
    expect(isDateInLimits(dayjs('1951-01-01 00:00'), min, max)).toBe(false)
  })
})

describe('getSurveyDateFormatsByLanguage', () => {
  it('maps every survey language to its date format', () => {
    expect(
      getSurveyDateFormatsByLanguage({
        languageSettings: { en: { dateFormat: 6 }, de: { dateFormat: 1 } },
      })
    ).toEqual({ en: 6, de: 1 })
    expect(getSurveyDateFormatsByLanguage({})).toEqual({})
  })
})

describe('getDatePickerViews', () => {
  it('returns the views a format contains', () => {
    expect(getDatePickerViews('DD.MM.YYYY')).toEqual(['year', 'month', 'day'])
    expect(getDatePickerViews('MM/YYYY')).toEqual(['year', 'month'])
    expect(getDatePickerViews('YYYY-MM-DD HH:mm')).toEqual([
      'year',
      'month',
      'day',
      'hours',
      'minutes',
    ])
    expect(getDatePickerViews('HH:mm')).toEqual(['hours', 'minutes'])
  })

  it('keeps the picker default without a format', () => {
    expect(getDatePickerViews(null)).toBeUndefined()
  })
})

describe('normalizeDateToViews', () => {
  const date = dayjs('1985-04-17 15:42:33')

  it('resets the time for date-only formats', () => {
    expect(
      normalizeDateToViews(date, ['year', 'month', 'day']).format(
        'YYYY-MM-DD HH:mm:ss'
      )
    ).toBe('1985-04-17 00:00:00')
  })

  it('resets the day and time for month/year formats', () => {
    expect(
      normalizeDateToViews(date, ['year', 'month']).format(
        'YYYY-MM-DD HH:mm:ss'
      )
    ).toBe('1985-04-01 00:00:00')
  })

  it('resets the date to 1970-01-01 for time-only formats, like the survey', () => {
    expect(
      normalizeDateToViews(date, ['hours', 'minutes']).format(
        'YYYY-MM-DD HH:mm:ss'
      )
    ).toBe('1970-01-01 15:42:00')
  })

  it('leaves the date alone without views', () => {
    expect(normalizeDateToViews(date, undefined)).toBe(date)
  })
})
