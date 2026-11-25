import { RestClient } from './restClient.service'

export class QuestionTypeService {
  constructor(auth, baseUrl) {
    this.auth = auth
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getQuestionTypes = async () => {
    // Throw on failure so react-query doesn't cache the error object as data
    return await this.restClient.get('questionTypes', {}, undefined, true)
  }
}
