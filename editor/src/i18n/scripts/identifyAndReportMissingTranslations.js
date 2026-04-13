/**
 * Pure function to compute missing translation keys.
 *
 * @param {string[]} translationStrings - List of all required translation keys
 * @param {Object} storedTranslations - Object containing existing translations (e.g., from localStorage)
 * @param {string} lang - Language code
 * @returns {Array<{ key: string, lang: string }>}
 */
export function getMissingTranslationKeys(
  translationStrings,
  storedTranslations,
  lang
) {
  return translationStrings
    .filter((key) => !(key in storedTranslations))
    .map((key) => ({ key, lang }))
}

/* eslint-disable no-console */
/**
 * Identify and Report Missing Translations
 *
 * This function compares the collected translation strings with those stored in the browser's
 * local storage for a specific language. It then reports any missing translations to the backend.
 *
 * @param {Object} translationsService - Service object for handling translation-related API calls
 * @param {string} userLang - The user's current language code for the app language
 *
 * Key steps:
 * 1. Fetches the collected translation strings from 'translationStrings.json'
 * 2. Retrieves stored translations from localStorage for the user's language
 * 3. Identifies keys present in translationStrings.json but missing in localStorage
 * 4. Reports these missing keys to the backend via translationsService
 *
 * Error handling:
 * - Logs an error if no user language is provided
 * - Catches and logs any errors during the process
 *
 * Console output:
 * - Logs the number of missing translations sent to the backend
 * - Logs a message if no missing translations are found
 *
 * Note: This function is typically called during application startup to ensure
 * the backend has the most up-to-date list of required translations.
 */
export async function identifyAndReportMissingTranslations(
  translationsService,
  userLang
) {
  if (!userLang) {
    console.error(
      'No language set in user details. Cannot load initial translations.'
    )
    return
  }

  try {
    const response = await fetch(
      `${process.env.PUBLIC_URL}/translationStrings.json`
    )
    if (!response.ok) {
      throw new Error('Failed to load translation strings')
    }

    const translationStrings = await response.json()

    const storageKey = `i18next_res_${userLang}-translation`
    const storedTranslations = JSON.parse(
      localStorage.getItem(storageKey) || '{}'
    )

    const missingKeys = getMissingTranslationKeys(
      translationStrings,
      storedTranslations,
      userLang
    )

    if (missingKeys.length > 0) {
      await translationsService.saveMissingKeys(missingKeys)
      console.log(
        `Sent ${missingKeys.length} missing translation strings for language ${userLang}.`
      )
    } else {
      console.log(
        `No missing translation strings found for language ${userLang}.`
      )
    }
  } catch (error) {
    console.error('Error loading initial translation strings:', error)
  }
}
