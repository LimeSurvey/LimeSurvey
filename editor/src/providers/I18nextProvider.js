import { useAppState, useAuth, useUserService } from 'hooks'
import { I18nextProvider } from 'react-i18next'
import { STATES, loadDayjsLocale } from '../helpers'
import { Suspense, useEffect, useState } from 'react'

// Resolves the user's language setting, falling back to the browser language for 'auto'.
const resolveUserLanguage = (lang) => {
  if (lang !== 'auto') {
    return lang
  }
  const browserLanguage = navigator.language || navigator.userLanguage || ''
  return browserLanguage.substring(0, 2) || 'en'
}

export const I18Provider = ({ i18n, children, language }) => {
  const auth = useAuth()
  const [, setUserDetail] = useAppState(STATES.USER_DETAIL)
  const [languages, setLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const userService = useUserService()
  const [i18nInstance, setI18nInstance] = useState(null)
  const [isTranslationLoaded, setIsTranslationLoaded] = useState(false)

  useEffect(() => {
    if (!auth.isLoggedIn || auth.userId == 0 || process.env.STORYBOOK_DEV) {
      setUserDetail({ lang: 'en' })
      setI18nInstance(i18n('en', auth, setLanguages, languages))
      setIsTranslationLoaded(true)
      loadDayjsLocale('en')
      return
    }

    // An explicit language (e.g. the survey language) only sets up i18n.
    // The shared dayjs locale is owned by the UI-language provider below.
    if (language) {
      setI18nInstance(i18n(language, auth, setLanguages, languages))
      setIsTranslationLoaded(true)
      return
    }

    const controller = new AbortController()
    const { signal } = controller

    const loadUserLanguage = async () => {
      let userDetail = null
      try {
        userDetail = await userService.getUserDetail(auth.userId, signal)
      } catch {
        // Fall back to English below
      }
      if (signal.aborted) {
        return
      }
      // Failed requests resolve to an error object or undefined, not the user
      let lang = 'en'
      if (userDetail?.lang) {
        setUserDetail(userDetail)
        lang = resolveUserLanguage(userDetail.lang)
      }
      // Create the i18n instance (which starts fetching the translations) while
      // the dayjs locale loads, so both switch to the new language together
      const instance = i18n(lang, auth, setLanguages, languages)
      await loadDayjsLocale(lang, { signal })
      if (!signal.aborted) {
        setI18nInstance(instance)
      }
    }

    loadUserLanguage()
    setIsTranslationLoaded(true)

    return () => controller.abort()
  }, [auth.token, language])

  const loadingTranslations = (
    <div
      data-testid="editor"
      style={{ height: '100vh' }}
      className="d-flex flex-column justify-content-center align-items-center"
    >
      <span style={{ width: 48, height: 48 }} className="loader mb-4"></span>
      <h1>Loading translations...</h1>
    </div>
  )

  if (!isTranslationLoaded) {
    return loadingTranslations
  }

  // useTranslation() suspends until the translations are loaded
  return (
    <I18nextProvider i18n={i18nInstance}>
      <Suspense fallback={loadingTranslations}>{children}</Suspense>
    </I18nextProvider>
  )
}
