import { useEffect } from 'react'

import { getApiUrl } from 'helpers'
import { UserSettingsService } from 'services'

import { useAuth } from './useAuth'

const STORAGE_KEY = 'LS_ADMIN_THEME_TOKENS'
const LINK_ID = 'ls-admin-theme-tokens'

/**
 * Loads the theme tokens of an admin theme (themes/admin/<theme>/css/theme_tokens.css),
 * the colours of the theme as the CSS variables used in themes/variables.scss.
 * Without a URL the editor keeps its own Sea Green colours.
 */
export const applyAdminThemeTokens = (url) => {
  let link = document.getElementById(LINK_ID)
  if (!url) {
    link?.remove()
    return
  }
  if (!link) {
    // Appended after the editor's own styles, so the theme rules win
    link = document.createElement('link')
    link.id = LINK_ID
    link.rel = 'stylesheet'
    document.head.appendChild(link)
  }
  if (link.getAttribute('href') !== url) {
    link.setAttribute('href', url)
  }
}

/**
 * Loads the theme tokens that were used last time, before the settings are loaded,
 * so the page doesn't change its colours after loading.
 */
export const applyStoredAdminThemeTokens = () => {
  try {
    applyAdminThemeTokens(localStorage.getItem(STORAGE_KEY))
  } catch {
    // Storage can be blocked, the theme is then applied once the settings are loaded
  }
}

/**
 * Loads the theme tokens of the current user's admin theme: their personal admin theme
 * setting, or the global admin theme if they did not choose one.
 *
 * @param {Object} siteSettings The site settings, with adminTheme (the global admin theme)
 *                              and adminThemeTokens (theme name => URL of its theme tokens)
 */
export const useAdminTheme = (siteSettings) => {
  const auth = useAuth()
  const globalAdminTheme = siteSettings?.adminTheme
  const tokensUrls = siteSettings?.adminThemeTokens

  useEffect(() => {
    if (!globalAdminTheme || !auth.isLoggedIn) {
      return
    }

    const userSettingsService = new UserSettingsService(auth, getApiUrl())
    userSettingsService
      .getUserSettingByName('admintheme')
      .then((userAdminTheme) => {
        const adminTheme =
          typeof userAdminTheme === 'string' && tokensUrls?.[userAdminTheme]
            ? userAdminTheme
            : globalAdminTheme
        const url = tokensUrls?.[adminTheme] ?? null
        applyAdminThemeTokens(url)
        try {
          if (url) {
            localStorage.setItem(STORAGE_KEY, url)
          } else {
            localStorage.removeItem(STORAGE_KEY)
          }
        } catch {
          // Storage can be blocked, the theme is then only applied after loading
        }
      })
    // auth is a new object on every render, the token identifies the session
  }, [globalAdminTheme, tokensUrls, auth.isLoggedIn, auth.token])
}
