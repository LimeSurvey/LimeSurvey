import { readAuthInitCookie } from './readAuthInitCookie'
import { getBasePath } from './getScriptPath'

const STORAGE_KEY_PREFIX = 'ls-editor-admin-theme-tokens:'
const LINK_ID = 'ls-admin-theme-tokens'

/**
 * Only the theme tokens file of an admin theme is accepted, because the value
 * comes from a cookie or the local storage and is loaded as a stylesheet.
 *
 * @param {*} url
 * @returns {string|null} the URL, or null when it is not a valid one
 */
const sanitizeTokensUrl = (url) => {
  if (typeof url !== 'string' || url === '') {
    return null
  }
  try {
    const parsedUrl = new URL(url, window.location.href)
    return ['http:', 'https:'].includes(parsedUrl.protocol) &&
      parsedUrl.pathname.endsWith('/css/theme_tokens.css')
      ? url
      : null
  } catch (e) {
    return null
  }
}

// Several installations can share one origin, so the key is scoped by path.
const getStorageKey = () => STORAGE_KEY_PREFIX + getBasePath()

/**
 * Returns the URL of the theme tokens of the user's admin theme. The server
 * passes it in the cookie it sets when redirecting to the editor. The cookie
 * only lives for a few seconds, so the value is remembered for page reloads.
 *
 * @returns {string|null} null when the admin theme has no theme tokens
 */
const getAdminThemeTokensUrl = () => {
  const cookieData = readAuthInitCookie()

  try {
    if (cookieData !== null) {
      const url = sanitizeTokensUrl(cookieData.adminThemeTokens)
      if (url) {
        window.localStorage.setItem(getStorageKey(), url)
      } else {
        window.localStorage.removeItem(getStorageKey())
      }
      return url
    }
    return sanitizeTokensUrl(window.localStorage.getItem(getStorageKey()))
  } catch (e) {
    // The storage can be unavailable (e.g. private mode): the cookie value is
    // still used for this page load.
    return cookieData !== null
      ? sanitizeTokensUrl(cookieData.adminThemeTokens)
      : null
  }
}

/**
 * Loads the theme tokens of the user's admin theme (themes/admin/<theme>/css/theme_tokens.css),
 * the colours of the theme as the CSS variables used in themes/variables.scss.
 * Without theme tokens the editor keeps its own Sea Green colours.
 */
export const applyAdminThemeTokens = () => {
  const url = getAdminThemeTokensUrl()
  if (!url) {
    return
  }
  // Appended after the editor's own styles, so the theme rules win
  const link = document.createElement('link')
  link.id = LINK_ID
  link.rel = 'stylesheet'
  link.href = url
  document.head.appendChild(link)
}

export default applyAdminThemeTokens
