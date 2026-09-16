import { useAppState, useAuth, useUserService } from 'hooks'
import { I18nextProvider } from 'react-i18next'
import { STATES } from '../helpers'
import { useEffect, useState } from 'react'

export const I18Provider = ({ i18n, children, language }) => {
  const auth = useAuth()
  const [, setUserDetail] = useAppState(STATES.USER_DETAIL)
  const [languages, setLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const userService = useUserService()
  const [i18nInstance, setI18nInstance] = useState(null)
  const [isTranslationLoaded, setIsTranslationLoaded] = useState(false)

  useEffect(() => {
    // Only flip isTranslationLoaded once the translations for the target
    // language have actually finished loading (see onReady callback below).
    // Setting it eagerly caused components that read translations via the
    // global st()/t() helpers to render before the async backend fetch
    // resolved, leaving them stuck with stale/untranslated text until an
    // unrelated re-render happened to occur later.
    setIsTranslationLoaded(false)
    const onReady = () => setIsTranslationLoaded(true)

    if (!auth.isLoggedIn || auth.userId == 0 || process.env.STORYBOOK_DEV) {
      setUserDetail({ lang: 'en' })
      setI18nInstance(
        i18n('en', auth, setLanguages, languages, undefined, onReady)
      )
      return
    }

    if (!language) {
      userService.getUserDetail(auth.userId).then((result) => {
        if (!result) {
          return
        }
        setUserDetail(result)
        let lang = result.lang
        if (lang === 'auto') {
          // Use browser's language
          const browserLanguage = navigator.language || navigator.userLanguage
          lang = browserLanguage.substring(0, 2)
          if (!lang) {
            lang = 'en' // Default to English if browser's language is not supported'
          }
        }
        setI18nInstance(
          i18n(lang, auth, setLanguages, languages, undefined, onReady)
        )
      })
    } else {
      setI18nInstance(
        i18n(language, auth, setLanguages, languages, undefined, onReady)
      )
    }
  }, [auth.token, language])

  if (!isTranslationLoaded) {
    return (
      <>
        <div
          data-testid="editor"
          style={{ height: '100vh' }}
          className="d-flex flex-column justify-content-center align-items-center"
        >
          <span
            style={{ width: 48, height: 48 }}
            className="loader mb-4"
          ></span>
          <h1>Loading translations...</h1>
        </div>
      </>
    )
  }

  return <I18nextProvider i18n={i18nInstance}>{children}</I18nextProvider>
}
