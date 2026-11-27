import { RestClient } from './restClient.service'

export class UserService {
  constructor(auth, userId, baseUrl) {
    this.auth = auth
    this.userId = userId
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getUserDetail = async (id, signal) => {
    return await this.restClient.get(`user-detail/${id}`, {}, signal)
  }

  getUserPermissions = async (surveyId, signal) => {
    return await this.restClient.get('user-permissions', {}, signal, false, {
      surveyId,
    })
  }
}
