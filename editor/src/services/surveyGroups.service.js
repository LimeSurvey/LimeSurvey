import { RestClient } from './restClient.service'

export class SurveyGroupsService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getSurveyGroups = async () => {
    return await this.restClient.get(`${this.baseurl}/survey-groups`)
  }
}
