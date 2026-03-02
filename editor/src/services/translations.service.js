import { RestClient } from './restClient.service'

export class TranslationsService {
  constructor(auth, baseUrl) {
    this.auth = auth
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
    this.sentMissingKeys = new Set()
    this.pendingMissingKeys = new Set()
    this.debounceTimer = null
  }

  /**
   * Fetches translations for a given language code.
   *
   * @param {string} id - The language code of the translations to fetch.
   * @returns {Promise<Object|undefined>} A promise that resolves to the translations object,
   *                                      or undefined if the user is not authenticated.
   * @throws {Error} If the API request fails.
   */
  getTranslations = async (id) => {
    if (this.auth.userId) {
      return await this.restClient.get(`/i18n/${id}`, {}, undefined, true)
    }
  }

  /**
   * Fetches an object containing all available languages with the language name
   * being translated to the language of the passed language code.
   *
   * @param {string} id - The language code of the  translations to fetch.
   * @returns {Promise<Object|undefined>} A promise that resolves to the translations object,
   *                                      or undefined if the user is not authenticated.
   * @throws {Error} If the API request fails.
   */
  getAllLanguages = async (id) => {
    if (this.auth.userId) {
      return await this.restClient.get(
        `/i18n-refresh-languages/${id}`,
        {},
        undefined,
        true
      )
    }
  }

  /**
   * Collects missing translation keys and sends them to the API immediately.
   * This is only done if we are in development environment.
   * The backend will add the missing keys to the file
   * application/helpers/editorTranslations.php which will be scanned
   * automatically when a release is done.
   * In this way the missing strings will be added to the translatable content on
   * translate.limesurvey.org
   *
   * @param {string} keyObjs - The missing translation key objects to be saved.
   * @returns {Promise<void>}
   * @throws {Error} If the API request to save the missing key fails.
   */
  saveMissingKeys = async (keyObjs) => {
    if (process.env.NODE_ENV === 'development' && this.auth.userId) {
      const newKeys = keyObjs.filter(
        (keyObj) =>
          !this.sentMissingKeys.has(keyObj.key) &&
          !this.pendingMissingKeys.has(keyObj.key)
      )

      newKeys.forEach((keyObj) => {
        this.pendingMissingKeys.add(keyObj.key)
      })
      await this.sendPendingKeys()
    }
  }

  /**
   * Sends the collected pending keys to the API.
   *
   * @returns {Promise<void>}
   */
  sendPendingKeys = async () => {
    if (this.pendingMissingKeys.size > 0) {
      try {
        const keysToSend = Array.from(this.pendingMissingKeys)
        await this.restClient.post('/i18n-missing', { keys: keysToSend })

        // Add sent keys to sentMissingKeys and clear pendingMissingKeys
        keysToSend.forEach((key) => {
          this.sentMissingKeys.add(key)
          this.pendingMissingKeys.delete(key)
        })
      } catch (error) {
        throw new Error(error)
      }
    }
  }
}
