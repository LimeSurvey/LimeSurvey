import { applyAdminThemeTokens } from '../adminThemeTokens'

const TOKENS_URL = '/themes/admin/Sea_Green_WCAG_AA/css/theme_tokens.css?v=1'

const setAuthInitCookie = (data) => {
  document.cookie =
    'LS_AUTH_INIT=' + encodeURIComponent(JSON.stringify(data)) + '; path=/'
}

const removeAuthInitCookie = () => {
  document.cookie =
    'LS_AUTH_INIT=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'
}

const getTokensLink = () => document.getElementById('ls-admin-theme-tokens')

describe('applyAdminThemeTokens', () => {
  beforeEach(() => {
    removeAuthInitCookie()
    window.localStorage.clear()
    getTokensLink()?.remove()
  })

  test('loads the theme tokens from the cookie and remembers them', () => {
    setAuthInitCookie({ token: 'x', adminThemeTokens: TOKENS_URL })

    applyAdminThemeTokens()

    expect(getTokensLink().getAttribute('href')).toBe(TOKENS_URL)

    // Page reload after the cookie expired
    removeAuthInitCookie()
    getTokensLink().remove()
    applyAdminThemeTokens()

    expect(getTokensLink().getAttribute('href')).toBe(TOKENS_URL)
  })

  test('a theme without theme tokens keeps the editor colours', () => {
    setAuthInitCookie({ token: 'x', adminThemeTokens: TOKENS_URL })
    applyAdminThemeTokens()
    getTokensLink().remove()

    setAuthInitCookie({ token: 'x', adminThemeTokens: null })
    applyAdminThemeTokens()

    expect(getTokensLink()).toBeNull()

    // The previous theme is forgotten
    removeAuthInitCookie()
    applyAdminThemeTokens()

    expect(getTokensLink()).toBeNull()
  })

  test('only loads a theme tokens file', () => {
    setAuthInitCookie({ token: 'x', adminThemeTokens: '/upload/other.css' })

    applyAdminThemeTokens()

    expect(getTokensLink()).toBeNull()
  })

  test('only loads a file of the same site', () => {
    setAuthInitCookie({
      token: 'x',
      adminThemeTokens: 'https://other.example/css/theme_tokens.css',
    })

    applyAdminThemeTokens()

    expect(getTokensLink()).toBeNull()
  })

  test('only loads http(s) URLs', () => {
    setAuthInitCookie({
      token: 'x',
      adminThemeTokens: 'data:text/css,/css/theme_tokens.css',
    })

    applyAdminThemeTokens()

    expect(getTokensLink()).toBeNull()
  })
})
