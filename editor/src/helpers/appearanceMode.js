import { readAuthInitCookie } from './readAuthInitCookie'
import { getBasePath } from './getScriptPath'

const STORAGE_KEY_PREFIX = 'ls-editor-appearance-mode:'
const APPEARANCE_MODES = ['light', 'dark', 'system']
const DARK_MODE_QUERY = '(prefers-color-scheme: dark)'

/**
 * Only a known appearance mode is accepted, because the value comes from a
 * cookie or the local storage.
 *
 * @param {*} appearanceMode
 * @returns {string|null} the appearance mode, or null when it is not a valid one
 */
const sanitizeAppearanceMode = (appearanceMode) =>
  APPEARANCE_MODES.includes(appearanceMode) ? appearanceMode : null

// Several installations can share one origin, so the key is scoped by path.
const getStorageKey = () => STORAGE_KEY_PREFIX + getBasePath()

/**
 * Returns the appearance mode of the admin interface (the user's personal
 * setting, or the global default). The server passes it in the cookie it sets
 * when redirecting to the editor. The cookie only lives for a few seconds, so
 * the value is remembered for page reloads.
 *
 * @returns {string} 'light', 'dark' or 'system'
 */
export const getAppearanceMode = () => {
  const cookieData = readAuthInitCookie()
  const cookieMode =
    cookieData !== null
      ? sanitizeAppearanceMode(cookieData.appearanceMode)
      : null

  try {
    if (cookieData !== null) {
      if (cookieMode) {
        window.localStorage.setItem(getStorageKey(), cookieMode)
      } else {
        window.localStorage.removeItem(getStorageKey())
      }
      return cookieMode ?? 'light'
    }
    return (
      sanitizeAppearanceMode(window.localStorage.getItem(getStorageKey())) ??
      'light'
    )
  } catch (e) {
    // The storage can be unavailable (e.g. private mode): The cookie value is
    // still used for this page load.
    return cookieMode ?? 'light'
  }
}

/**
 * Sets data-bs-theme="light" or data-bs-theme="dark" on the html element,
 * which switches the colours (see themes/dark-mode.scss). In the 'system'
 * mode the editor follows the light or dark mode of the device. Printing
 * always uses the light colours, like the admin pages.
 */
export const applyAppearanceMode = () => {
  const appearanceMode = getAppearanceMode()
  const darkModeQuery =
    appearanceMode === 'system' && typeof window.matchMedia === 'function'
      ? window.matchMedia(DARK_MODE_QUERY)
      : null

  let isPrinting = false

  const setTheme = () => {
    const isDark =
      !isPrinting &&
      (appearanceMode === 'dark' || darkModeQuery?.matches === true)
    document.documentElement.setAttribute(
      'data-bs-theme',
      isDark ? 'dark' : 'light'
    )
  }

  setTheme()
  darkModeQuery?.addEventListener?.('change', setTheme)

  if (appearanceMode !== 'light') {
    window.addEventListener('beforeprint', () => {
      isPrinting = true
      setTheme()
    })
    window.addEventListener('afterprint', () => {
      isPrinting = false
      setTheme()
    })
  }
}

export default applyAppearanceMode
