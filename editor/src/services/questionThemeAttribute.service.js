import { RestClient } from './restClient.service'

export class QuestionThemeAttributeService {
  constructor(auth, baseUrl) {
    this.auth = auth
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getAttributes = async () => {
    return await this.restClient.get('questionThemeAttribute/attributes')
  }
}
