import axios from 'axios'

import { RestClient } from './restClient.service'

export class SurveyService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  patchSurvey = async (buffer) => {
    return await this.restClient.patch(
      `/survey-detail/${this.surveyId}`,
      {
        patch: buffer,
      },
      true
    )
  }

  getSurveyList = async (page = 1, pageSize = 10) => {
    return await this.restClient.get(`/survey`, {
      params: { page, pageSize },
    })
  }

  getSurveyDetail = async (id, signal, lastRequestedAt = null) => {
    const url = lastRequestedAt
      ? `survey-detail/${id}/ts/${lastRequestedAt}`
      : `survey-detail/${id}`

    return await this.restClient.get(url, {}, signal)
  }

  getSurveyQuestionsFieldname = async (sid, signal) => {
    return await this.restClient.get(
      `survey-questions-fieldname/${sid}`,
      {},
      signal
    )
  }

  getSurveyDetailDemo = async (id) => {
    const url = id
      ? './data/survey-detail-empty.json'
      : './data/survey-detail.json'
    const res = await axios.get(url)
    return res.data
  }
}
