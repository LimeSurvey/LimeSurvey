import { RestClient } from './restClient.service'

export class FileService {
  constructor(auth, baseUrl, surveyId) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  uploadSurveyImage = async (data) => {
    return await this.restClient.post(
      `${this.baseurl}/file-upload-survey-image/${this.surveyId}`,
      data,
      true
    )
  }
}
