import * as dayPickerLocales from '@daypicker/react/locale'

import { resolveLocale } from '../localeUtils'
import { dayJsHelper, getDayjsLocale, loadDayjsLocale } from '../dayJsHelper'
import { getDayPickerLocale } from '../dateRangeUtils'

describe('resolveLocale', () => {
  const available = new Set(['de', 'pt-br', 'zh-cn', 'hy-am'])
  const options = {
    isAvailable: (name) => available.has(name),
    aliases: { 'zh-Hans': 'zh-cn' },
    normalize: (code) => code.toLowerCase(),
    locales: available,
  }

  test.each([
    ['de', 'de'],
    ['de-informal', 'de'],
    ['de-easy', 'de'],
    ['pt-BR', 'pt-br'],
    ['zh-Hans', 'zh-cn'],
    ['de-AT', 'de'],
    ['hy', 'hy-am'],
  ])('resolves %s to %s', (language, expected) => {
    expect(resolveLocale(language, options)).toBe(expected)
  })

  test('returns undefined when nothing matches', () => {
    expect(resolveLocale('xx', options)).toBeUndefined()
    expect(resolveLocale(undefined, options)).toBeUndefined()
  })
})

describe('getDayjsLocale', () => {
  test.each([
    ['de-informal', 'de'],
    ['pt-BR', 'pt-br'],
    ['es-AR-informal', 'es'],
    ['zh-Hant-TW', 'zh-tw'],
    ['sr', 'sr-cyrl'],
    ['sr-Latn', 'sr'],
    ['hy', 'hy-am'],
    ['pa', 'pa-in'],
    ['tl', 'tl-ph'],
    ['ug', 'ug-cn'],
    ['oc', 'oc-lnc'],
    ['cnr', 'me'],
    ['swh', 'sw'],
    ['unknown', 'en'],
  ])('maps %s to %s', (language, expected) => {
    expect(getDayjsLocale(language)).toBe(expected)
  })
})

describe('loadDayjsLocale', () => {
  afterEach(() => {
    dayJsHelper.locale('en')
  })

  test('translates relative time and month names', async () => {
    await expect(loadDayjsLocale('de-informal')).resolves.toBe('de')
    expect(dayJsHelper().subtract(2, 'hour').fromNow()).toBe('vor 2 Stunden')
    expect(dayJsHelper('2024-03-05').format('D MMM YYYY')).toBe('5 März 2024')
  })
})

describe('getDayPickerLocale', () => {
  test.each([
    ['en', dayPickerLocales.enUS],
    ['de-informal', dayPickerLocales.de],
    ['pt-BR', dayPickerLocales.ptBR],
    ['zh-Hans', dayPickerLocales.zhCN],
    ['unknown', dayPickerLocales.enUS],
  ])('maps %s', (language, expected) => {
    expect(getDayPickerLocale(language)).toBe(expected)
  })
})
