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
    const controller = new AbortController()
    const { signal } = controller

    const applyLanguage = (lang) => {
      setI18nInstance(i18n(lang, auth, setLanguages, languages))
      setIsTranslationLoaded(true)
    }

    // Creates the i18n instance (which starts fetching the translations) while
    // the dayjs locale loads, so children first render with the correct one.
    const applyUiLanguage = async (lang) => {
      const instance = i18n(lang, auth, setLanguages, languages)
      await loadDayjsLocale(lang, { signal })
      if (signal.aborted) {
        return
      }
      setI18nInstance(instance)
      setIsTranslationLoaded(true)
    }

    if (!auth.isLoggedIn || auth.userId == 0 || process.env.STORYBOOK_DEV) {
      setUserDetail({ lang: 'en' })
      applyUiLanguage('en')
      return () => controller.abort()
    }

    // An explicit language (e.g. the survey language) only sets up i18n.
    // The shared dayjs locale is owned by the UI-language provider below.
    if (language) {
      applyLanguage(language)
      return
    }

    const loadUserLanguage = async () => {
      let userDetail = null
      try {
        userDetail = await userService.getUserDetail(auth.userId)
      } catch {
        // Fall back to English below rather than blocking the editor
      }
      if (signal.aborted) {
        return
      }
      if (!userDetail) {
        applyUiLanguage('en')
        return
      }
      setUserDetail(userDetail)
      applyUiLanguage(resolveUserLanguage(userDetail.lang))
    }

    loadUserLanguage()

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
