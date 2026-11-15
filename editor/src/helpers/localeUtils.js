// Language codes carry variants no locale library knows about
// (e.g. "de-informal", "es-AR-informal", "de-easy").
const LANGUAGE_VARIANT_SUFFIX = /-(informal|easy)$/i

/**
 * Resolves a language code to the closest locale a library supports.
 *
 * Tries, in order: an alias for the full code, the full code, an alias for the
 * base language, the base language (e.g. "pt-BR" -> "pt"), and finally the
 * first of `locales` that is a regional variant of the base language
 * (e.g. "hy" -> "hy-am").
 *
 * @param {string} language LimeSurvey language code (e.g. "de-informal", "pt-BR", "zh-Hans")
 * @param {object} options
 * @param {(name: string) => boolean} options.isAvailable whether the library provides that locale
 * @param {Object<string, string>} [options.aliases] LimeSurvey code -> library locale name, for codes that don't map by name
 * @param {(code: string) => string} [options.normalize] turns a LimeSurvey code into the library's naming (e.g. lowercase)
 * @param {Iterable<string>} [options.locales] all locale names the library provides, for the regional variant fallback
 * @returns {string|undefined} the library locale name, or undefined when none matches
 */
export const resolveLocale = (
  language,
  { isAvailable, aliases = {}, normalize = (code) => code, locales = [] }
) => {
  const code = String(language || '').replace(LANGUAGE_VARIANT_SUFFIX, '')
  if (!code) return undefined
  const base = code.split('-')[0]

  const match = [
    aliases[code],
    normalize(code),
    aliases[base],
    normalize(base),
  ].find((name) => name && isAvailable(name))
  if (match) return match

  const regionalPrefix = `${normalize(base)}-`
  return [...locales].find(
    (name) => name.startsWith(regionalPrefix) && isAvailable(name)
  )
}
