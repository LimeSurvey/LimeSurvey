import { RestClient } from './restClient.service'

export class QuestionThemeAttributeService {
  constructor(auth, baseUrl) {
    this.auth = auth
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getAttributes = async () => {
    // Throw on failure so react-query doesn't cache the error object as data
    return await this.restClient.get(
      'questionThemeAttribute',
      {},
      undefined,
      true
    )
  }
}
