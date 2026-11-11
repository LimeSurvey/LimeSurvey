import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'
import relativeTime from 'dayjs/plugin/relativeTime'
import dayjsLocales from 'dayjs/locale.json'

import { resolveLocale } from './localeUtils'

dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.extend(relativeTime)

export const dayJsHelper = dayjs

const DAYJS_LOCALES = new Set(dayjsLocales.map(({ key }) => key))

// language codes that don't map to a dayjs locale by name.
const DAYJS_LOCALE_ALIASES = {
  'zh-Hans': 'zh-cn',
  'zh-Hant-HK': 'zh-hk',
  'zh-Hant-TW': 'zh-tw',
  'sr': 'sr-cyrl',
  'sr-Latn': 'sr',
  'ckb': 'ku',
  'fil': 'tl-ph',
  'mya': 'my',
}

export const getDayjsLocale = (language) =>
  resolveLocale(language, {
    isAvailable: (name) => DAYJS_LOCALES.has(name),
    aliases: DAYJS_LOCALE_ALIASES,
    normalize: (code) => code.toLowerCase(),
  }) ?? 'en'

// Loads the dayjs locale matching the UI language
export const loadDayjsLocale = async (language) => {
  const locale = getDayjsLocale(language)
  try {
    if (locale !== 'en') {
      await import(`dayjs/locale/${locale}.js`)
    }
    dayjs.locale(locale)
    return locale
  } catch {
    dayjs.locale('en')
    return 'en'
  }
}
