import { RestClient } from './restClient.service'

export class SurveyArchiveService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getSurveyArchives = async (sid, signal) => {
    return await this.restClient.get(`survey-archives/${sid}`, {}, signal)
  }

  getSurveyArchivesByBaseTable = async (sid, basetable, signal) => {
    let result = await this.restClient.get(`survey-archives/${sid}`, {}, signal)
    return result.filter((item) => item.types.indexOf(basetable) >= 0)
  }
}
