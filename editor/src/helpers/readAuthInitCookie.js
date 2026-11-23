const AUTH_INIT_COOKIE_NAME = 'LS_AUTH_INIT'

/**
 * Reads the data of the short-lived cookie the server sets when it
 * redirects to the editor (see EditorLinkController).
 *
 * @returns {Object|null} null when the cookie is missing or cannot be read
 */
export const readAuthInitCookie = () => {
  const prefix = AUTH_INIT_COOKIE_NAME + '='
  const cookie = document.cookie
    .split('; ')
    .find((row) => row.startsWith(prefix))

  if (!cookie) {
    return null
  }

  try {
    // PHP encodes spaces as '+', which decodeURIComponent() leaves alone.
    const data = JSON.parse(
      decodeURIComponent(cookie.slice(prefix.length).replace(/\+/g, ' '))
    )
    return data && typeof data === 'object' ? data : null
  } catch (e) {
    return null
  }
}

export default readAuthInitCookie
