import { readAuthInitCookie } from './readAuthInitCookie'

const STORAGE_KEY_PREFIX = 'ls-editor-script-name:'
const SCRIPT_NAME_PATTERN = /^[\w.-]+\.php$/

// null: Not resolved yet
let cachedScriptName = null

/**
 * Returns the path of the LimeSurvey installation below the host,
 * e.g. '' or '/limesurvey'. Static files are located below this path.
 *
 * @returns {string}
 */
export const getBasePath = () => window.location.pathname.split('/editor')[0]

/**
 * Only a plain PHP file name is accepted, because the value comes from a
 * cookie or the local storage and ends up in every URL the editor builds.
 *
 * @param {*} scriptName
 * @returns {string} the script name, or '' when it is not a valid one
 */
const sanitizeScriptName = (scriptName) =>
  typeof scriptName === 'string' && SCRIPT_NAME_PATTERN.test(scriptName)
    ? scriptName
    : ''

/**
 * Reads the script name from the short-lived cookie the server sets when it
 * redirects to the editor.
 *
 * @returns {string|null} null when the cookie is missing or has no script name
 */
const readScriptNameFromCookie = () => {
  const data = readAuthInitCookie()
  return typeof data?.scriptName === 'string' ? data.scriptName : null
}

// Several installations can share one origin, so the key is scoped by path.
const getStorageKey = () => STORAGE_KEY_PREFIX + getBasePath()

const readScriptNameFromStorage = () => {
  try {
    return window.localStorage.getItem(getStorageKey())
  } catch (e) {
    return null
  }
}

const writeScriptNameToStorage = (scriptName) => {
  try {
    window.localStorage.setItem(getStorageKey(), scriptName)
  } catch (e) {
    // The storage can be unavailable (e.g. private mode): the value is
    // still kept in memory until the next page load.
  }
}

/**
 * Returns the entry script name (e.g. 'index.php') that has to be part of
 * the application URLs, or '' when the URL manager hides the script name.
 *
 * The editor is a static page, so the server passes its 'showScriptName'
 * setting in the cookie it sets when redirecting to the editor. The cookie
 * only lives for a few seconds, so the value is remembered for page reloads.
 *
 * @returns {string}
 */
export const getScriptName = () => {
  if (cachedScriptName === null) {
    const cookieScriptName = readScriptNameFromCookie()

    if (cookieScriptName !== null) {
      cachedScriptName = sanitizeScriptName(cookieScriptName)
      writeScriptNameToStorage(cachedScriptName)
    } else {
      cachedScriptName = sanitizeScriptName(readScriptNameFromStorage())
    }
  }

  return cachedScriptName
}

/**
 * Returns the path all application URLs (admin pages, REST API, survey links)
 * start with: The base path plus the entry script when it must be shown,
 * e.g. '/limesurvey/index.php' or '/limesurvey'.
 *
 * @returns {string}
 */
export const getScriptPath = () => {
  const scriptName = getScriptName()

  return getBasePath() + (scriptName ? '/' + scriptName : '')
}

export default getScriptPath
