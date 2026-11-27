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
    let isCancelled = false
    // Only flip isTranslationLoaded once the translations for the target
    // language have actually finished loading. Components that read
    // translations via the global st()/t() helpers don't re-render when the
    // translations arrive later, so they would keep untranslated text.
    const onReady = () => {
      if (!isCancelled) {
        setIsTranslationLoaded(true)
      }
    }

    if (!auth.isLoggedIn || auth.userId == 0 || process.env.STORYBOOK_DEV) {
      setIsTranslationLoaded(false)
      setUserDetail({ lang: 'en' })
      setI18nInstance(
        i18n('en', auth, setLanguages, languages, undefined, onReady)
      )
      loadDayjsLocale('en')
      return () => {
        isCancelled = true
      }
    }

    // An explicit language (e.g. the survey language) only sets up i18n.
    // The shared dayjs locale is owned by the UI-language provider below.
    if (language) {
      setIsTranslationLoaded(false)
      setI18nInstance(
        i18n(language, auth, setLanguages, languages, undefined, onReady)
      )
      return () => {
        isCancelled = true
      }
    }

    // Drop the role until it is fetched again, so nothing relies on a stale value
    setUserDetail((userDetail) => ({ ...userDetail, isSuperAdmin: undefined }))

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
      // the dayjs locale loads, and switch once both are ready, so the editor
      // never shows the new language with missing translations
      let instance
      const translationsReady = new Promise((resolve) => {
        instance = i18n(lang, auth, setLanguages, languages, undefined, resolve)
      })
      await Promise.all([translationsReady, loadDayjsLocale(lang, { signal })])
      if (!signal.aborted) {
        setI18nInstance(instance)
      }
    }

    // Render right away: The user's language is applied once it is loaded, so
    // a slow user detail request never blocks the editor
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
      {/* Remount when the language changes, so texts read via st()/t() update too */}
      <Suspense key={i18nInstance?.language} fallback={loadingTranslations}>
        {children}
      </Suspense>
    </I18nextProvider>
  )
}
