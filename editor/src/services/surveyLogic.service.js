import { RestClient } from './restClient.service'

export class SurveyLogicService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  /**
   * Fetch the survey logic overview for a survey, group or question.
   *
   * @param {number|string} sid - Survey id
   * @param {{ gid?: number|string, qid?: number|string, language?: string }} options
   * @param {AbortSignal} [signal]
   * @returns {Promise<{ html: string, errors: number }>}
   */
  getSurveyLogic = async (sid, { gid, qid, language } = {}, signal) => {
    const params = {}
    if (gid != null) params.gid = gid
    if (qid != null) params.qid = qid
    if (language) params.lang = language

    return await this.restClient.get(
      `survey-logic/${sid}`,
      {},
      signal,
      true,
      params
    )
  }
}
