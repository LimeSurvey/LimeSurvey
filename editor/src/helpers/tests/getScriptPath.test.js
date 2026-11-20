const AUTH_INIT_COOKIE_NAME = 'LS_AUTH_INIT'
const BASE_PATH = '/limesurvey'
const STORAGE_KEY = 'ls-editor-script-name:' + BASE_PATH

/**
 * The script name is resolved once per page load, so every test needs a
 * fresh copy of the helpers.
 */
const loadHelpers = () => {
  let helpers
  jest.isolateModules(() => {
    helpers = require('helpers')
  })
  return helpers
}

const setAuthInitCookie = (data) => {
  document.cookie =
    AUTH_INIT_COOKIE_NAME + '=' + encodeURIComponent(JSON.stringify(data))
}

const clearAuthInitCookie = () => {
  document.cookie =
    AUTH_INIT_COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT'
}

describe('getScriptPath', () => {
  const originalUrl = window.location.href

  beforeEach(() => {
    window.history.pushState({}, '', BASE_PATH + '/editor/')
    clearAuthInitCookie()
    window.localStorage.clear()
  })

  afterAll(() => {
    window.history.pushState({}, '', originalUrl)
    clearAuthInitCookie()
    window.localStorage.clear()
  })

  test('should not add a script name when the server did not send one', () => {
    const { getScriptPath, getSiteUrl, getApiUrl, URLS } = loadHelpers()

    expect(getScriptPath()).toBe('/limesurvey')
    expect(getSiteUrl('/admin')).toBe('http://localhost/limesurvey/admin')
    expect(getApiUrl()).toBe('http://localhost/limesurvey/rest/v1')
    expect(URLS.ADMIN).toBe('/limesurvey/admin')
  })

  test('should add the script name sent by the server to all application URLs', () => {
    setAuthInitCookie({ token: 'abc', scriptName: 'index.php' })
    const { getScriptPath, getSiteUrl, getApiUrl, URLS } = loadHelpers()

    expect(getScriptPath()).toBe('/limesurvey/index.php')
    expect(getSiteUrl('/admin')).toBe(
      'http://localhost/limesurvey/index.php/admin'
    )
    expect(getApiUrl()).toBe('http://localhost/limesurvey/index.php/rest/v1')
    expect(URLS.ADMIN).toBe('/limesurvey/index.php/admin')
    expect(URLS.SURVEY_OVERVIEW).toBe(
      '/limesurvey/index.php/surveyAdministration/view/'
    )
  })

  test('should not add the script name to a path that is already complete', () => {
    setAuthInitCookie({ token: 'abc', scriptName: 'index.php' })
    const { getSiteUrl } = loadHelpers()

    expect(getSiteUrl('/limesurvey/index.php/123456', false)).toBe(
      'http://localhost/limesurvey/index.php/123456'
    )
  })

  test('should remember the script name after the cookie has expired', () => {
    setAuthInitCookie({ token: 'abc', scriptName: 'index.php' })
    loadHelpers().getScriptPath()
    clearAuthInitCookie()

    expect(loadHelpers().getScriptPath()).toBe('/limesurvey/index.php')
  })

  test('should forget the script name when the server stops showing it', () => {
    window.localStorage.setItem(STORAGE_KEY, 'index.php')
    setAuthInitCookie({ token: 'abc', scriptName: '' })

    expect(loadHelpers().getScriptPath()).toBe('/limesurvey')
    expect(window.localStorage.getItem(STORAGE_KEY)).toBe('')
  })

  test('should keep the remembered script name when the cookie has none', () => {
    window.localStorage.setItem(STORAGE_KEY, 'index.php')
    setAuthInitCookie({ token: 'abc' })

    expect(loadHelpers().getScriptPath()).toBe('/limesurvey/index.php')
  })

  test.each([
    '../admin',
    'index.php/evil',
    '//example.com/x.php',
    'index.html',
  ])('should ignore the invalid script name %s', (scriptName) => {
    setAuthInitCookie({ token: 'abc', scriptName })

    expect(loadHelpers().getScriptPath()).toBe('/limesurvey')
  })

  test('should ignore an invalid remembered script name', () => {
    window.localStorage.setItem(STORAGE_KEY, '//example.com/x.php')

    expect(loadHelpers().getScriptPath()).toBe('/limesurvey')
  })
})
