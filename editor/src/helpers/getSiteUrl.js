import { getScriptPath } from './getScriptPath'

/**
 * Returns the absolute URL of an application route (admin page, survey link).
 *
 * @param {string} path route starting with a slash, without the entry script
 * @param {boolean} needPrefix false when the path is already complete, e.g.
 *   a link created by the server
 * @returns {string}
 */
export const getSiteUrl = (path = '', needPrefix = true) => {
  let baseUrl = process.env.REACT_APP_SITE_URL
  if (!baseUrl || baseUrl === '/') {
    const host = window.location.protocol + '//' + window.location.host
    baseUrl = needPrefix ? host + getScriptPath() : host
  }

  return baseUrl + path
}

export default getSiteUrl
