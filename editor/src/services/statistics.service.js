import { RestClient } from './restClient.service'

export class StatisticsService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getSurveyStatistics = async (sid, filters) => {
    let { completed, minId, maxId } = filters

    // Ensure minId is not greater than maxId, if provided.
    if (Number(minId) > Number(maxId) && maxId) {
      minId = maxId
    }

    const minIdIsNumber = typeof minId === 'number'
    const maxIdIsNumber = typeof maxId === 'number'

    const queryFilters = `${typeof completed === 'boolean' ? `completed=${completed}` : ''}${minIdIsNumber ? `&minId=${minId}` : ''}${maxIdIsNumber ? `&maxId=${maxId}` : ''}`
    return await this.restClient.get(`statistics/${sid}?${queryFilters}`)
  }

  getSurveyStatisticsAtGlance = async (sid) => {
    return await this.restClient.get(`statistics-glance/${sid}`)
  }
}
