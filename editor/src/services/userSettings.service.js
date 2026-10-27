import { RestClient } from './restClient.service'

export class UserSettingsService {
  constructor(auth, baseUrl) {
    this.auth = auth
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getUserSettingByName = async (settingName) => {
    return await this.restClient.get(
      `${this.baseurl}/user-setting/${settingName}`
    )
  }

  /**
   * Resolves { found: false } when the setting does not exist (404) and
   * { found: true, value } otherwise (value may be an empty string).
   * Any other failure is thrown so callers do not mistake it for "not set".
   */
  getUserSettingValue = async (settingName) => {
    try {
      const value = await this.restClient.get(
        `${this.baseurl}/user-setting/${settingName}`,
        {},
        undefined,
        true
      )
      return { found: true, value }
    } catch (error) {
      if (error?.httpStatus === 404) {
        return { found: false }
      }
      throw error
    }
  }

  setUserSettingByName = async (settingName, settingValue) => {
    return await this.restClient.post(`${this.baseurl}/user-setting`, {
      settingName,
      settingValue,
    })
  }
}
