import { RestClient } from './restClient.service'

export class UserService {
  constructor(auth, userId, baseUrl) {
    this.auth = auth
    this.userId = userId
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getUserDetail = async (id) => {
    return await this.restClient.get(`user-detail/${id}`)
  }

  getUserPermissions = async () => {
    return await this.restClient.get('user-permissions')
  }
}
