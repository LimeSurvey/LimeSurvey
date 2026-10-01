import Backend from 'i18next-chained-backend'
import LocalStorageBackend from 'i18next-localstorage-backend'
import CustomI18nBackend from 'i18n/CustomI18nBackend'
import { getApiUrl } from 'helpers'
import { TranslationsService } from 'services'
import i18next from 'i18next'

export const i18nInstance = (
  lang = '',
  auth = {},
  setLanguages = () => {},
  languages = [],
  isSurveyTranslation = false,
  onReady = () => {}
) => {
  const translationsService = new TranslationsService(auth, getApiUrl())

  const i18nInstance = i18next.createInstance()

  if (process.env.STORYBOOK_DEV || process.env.REACT_APP_DEMO_MODE === 'true') {
    global.st = i18nInstance.t.bind(i18nInstance)
    global.t = i18nInstance.t.bind(i18nInstance)

    i18nInstance.init(
      {
        lng: lang,
        fallbackLng: 'en', // Default language
        debug: false,
        resources: {
          en: {},
        },
        interpolation: { escapeValue: false },
      },
      onReady
    )

    return i18nInstance
  } else {
    if (isSurveyTranslation) {
      // Export the st function globally (translating the survey itself based on the chosen survey language)
      global.st = i18nInstance.t.bind(i18nInstance)
    } else {
      // Export the t function globally
      global.t = i18nInstance.t.bind(i18nInstance)
    }
  }

  i18nInstance.use(Backend).init(
    {
      lng: lang,
      fallbackLng: 'en', // Default language
      debug: false,
      backend: {
        backends: [
          LocalStorageBackend,
          new CustomI18nBackend(setLanguages, languages),
        ],
        backendOptions: [
          {
            prefix: 'i18next_res_',
            expirationTime: 7 * 24 * 60 * 60 * 1000, // Cache expiration in 7 days
          },
          {
            translationsService, // Pass the initialized TranslationsService
          },
        ],
      },
      interpolation: { escapeValue: false },
    },
    // Only signal "ready" once the backend has actually finished loading the
    // translations for this language. Previously callers marked the
    // translations as loaded as soon as init() was called (synchronously),
    // before the async backend fetch resolved. Components that read
    // translations via the global st()/t() helpers (instead of the
    // useTranslation() hook) don't automatically re-render when the
    // translations arrive later, so they kept showing stale/untranslated
    // text until something else happened to trigger a re-render (e.g.
    // toggling the welcome screen off and on again).
    onReady
  )

  return i18nInstance
}
