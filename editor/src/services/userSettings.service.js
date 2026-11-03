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

  setUserSettingByName = async (settingName, settingValue) => {
    return await this.restClient.post(`${this.baseurl}/user-setting`, {
      settingName,
      settingValue,
    })
  }
}
