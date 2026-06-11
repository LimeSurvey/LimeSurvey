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

  /**
   * Load every comment of a question.
   *
   * Comments live in the response table, so we use the survey-response-answers
   * endpoint which returns a flat list of answers (each carrying its `title`,
   * `aid`, `subquestion` and the `responseId`). We keep the comment answers
   * (`aid` ending in "comment") belonging to the requested question.
   */
  getQuestionComments = async (
    sid,
    questionCode,
    currentPage = 0,
    pageSize = 15,
    language,
    answerFilter
  ) => {
    const body = { page: { currentPage, pageSize } }
    if (language) {
      body.language = language
    }
    if (answerFilter?.field && answerFilter?.value) {
      body.answerField = answerFilter.field
      body.answerValue = answerFilter.value
    }

    const data = await this.restClient.post(
      `survey-response-answers/${sid}`,
      body
    )

    const answers = data?.answers?.answers || []

    const comments = answers
      .filter(
        (answer) =>
          String(answer?.title) === String(questionCode) &&
          String(answer?.aid || '').endsWith('comment') &&
          answer?.value !== undefined &&
          answer?.value !== null &&
          answer?.value !== ''
      )
      .map((answer) => ({
        responseId: answer.responseId,
        comment: answer.value,
        subQuestion: answer.subquestion || null,
      }))

    return { comments, pagination: data?.answers?._meta?.pagination || null }
  }
}
