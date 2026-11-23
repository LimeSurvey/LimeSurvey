import { applyAppearanceMode, getAppearanceMode } from '../appearanceMode'

const setAuthInitCookie = (data) => {
  document.cookie =
    'LS_AUTH_INIT=' + encodeURIComponent(JSON.stringify(data)) + '; path=/'
}

const removeAuthInitCookie = () => {
  document.cookie =
    'LS_AUTH_INIT=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'
}

const getTheme = () => document.documentElement.getAttribute('data-bs-theme')

/**
 * Replaces window.matchMedia with a device that is in dark or light mode.
 *
 * @param {boolean} deviceIsDark
 * @returns {{setDark: function(boolean): void}} lets a test change the mode of the device
 */
const mockDevice = (deviceIsDark) => {
  const listeners = []
  const query = {
    matches: deviceIsDark,
    addEventListener: (event, listener) => listeners.push(listener),
  }
  window.matchMedia = jest.fn(() => query)

  return {
    setDark: (isDark) => {
      query.matches = isDark
      listeners.forEach((listener) => listener())
    },
  }
}

describe('applyAppearanceMode', () => {
  beforeEach(() => {
    removeAuthInitCookie()
    window.localStorage.clear()
    document.documentElement.removeAttribute('data-bs-theme')
    mockDevice(false)
  })

  test('uses the dark mode from the cookie and remembers it', () => {
    setAuthInitCookie({ token: 'x', appearanceMode: 'dark' })

    applyAppearanceMode()

    expect(getTheme()).toBe('dark')

    // The cookie only lives for a few seconds: A page reload has no cookie
    removeAuthInitCookie()
    document.documentElement.removeAttribute('data-bs-theme')
    applyAppearanceMode()

    expect(getTheme()).toBe('dark')
  })

  test('is light without a cookie and without a remembered mode', () => {
    applyAppearanceMode()

    expect(getTheme()).toBe('light')
  })

  test('the dark mode ignores the mode of the device', () => {
    mockDevice(false)
    setAuthInitCookie({ token: 'x', appearanceMode: 'dark' })

    applyAppearanceMode()

    expect(getTheme()).toBe('dark')
  })

  test('the light mode ignores the mode of the device', () => {
    mockDevice(true)
    setAuthInitCookie({ token: 'x', appearanceMode: 'light' })

    applyAppearanceMode()

    expect(getTheme()).toBe('light')
  })

  test('the system mode follows the device, also when the device changes', () => {
    const device = mockDevice(true)
    setAuthInitCookie({ token: 'x', appearanceMode: 'system' })

    applyAppearanceMode()
    expect(getTheme()).toBe('dark')

    device.setDark(false)
    expect(getTheme()).toBe('light')
  })

  test('printing uses the light mode, the dark mode comes back afterwards', () => {
    setAuthInitCookie({ appearanceMode: 'dark' })

    applyAppearanceMode()
    window.dispatchEvent(new Event('beforeprint'))
    expect(getTheme()).toBe('light')

    window.dispatchEvent(new Event('afterprint'))
    expect(getTheme()).toBe('dark')
  })

  test('a cookie without a valid mode resets the remembered mode', () => {
    setAuthInitCookie({ token: 'x', appearanceMode: 'dark' })
    applyAppearanceMode()

    setAuthInitCookie({ token: 'x', appearanceMode: 'sepia' })

    expect(getAppearanceMode()).toBe('light')

    removeAuthInitCookie()

    expect(getAppearanceMode()).toBe('light')
  })

  test('ignores an unknown remembered mode', () => {
    window.localStorage.setItem(
      'ls-editor-appearance-mode:' +
        window.location.pathname.split('/editor')[0],
      'sepia'
    )

    applyAppearanceMode()

    expect(getTheme()).toBe('light')
  })
})
