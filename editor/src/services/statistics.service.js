import { RestClient } from './restClient.service'

export class StatisticsService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  getSurveyStatistics = async (sid, filters, page = 0, pageSize = 15) => {
    let { completed, minId, maxId } = filters

    // Ensure minId is not greater than maxId, if provided.
    if (Number(minId) > Number(maxId) && maxId) {
      minId = maxId
    }

    const minIdIsNumber = typeof minId === 'number'
    const maxIdIsNumber = typeof maxId === 'number'

    const queryFilters = `${typeof completed === 'boolean' ? `completed=${completed}` : ''}${minIdIsNumber ? `&minId=${minId}` : ''}${maxIdIsNumber ? `&maxId=${maxId}` : ''}&page=${page}&pageSize=${pageSize}`
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

    const answers = data?.answers || []

    const belongsToQuestion = (answer) =>
      String(answer?.title) === String(questionCode)

    const hasValue = (answer) =>
      answer?.value !== undefined &&
      answer?.value !== null &&
      answer?.value !== ''

    // Question-wide comment types (e.g. 'O' list-with-comment) store a single
    // comment with no sub-question. Map each response to its selected answer so
    // those comments can still be grouped and filtered by the chosen option.
    const selectedByResponse = {}
    answers.forEach((answer) => {
      if (
        belongsToQuestion(answer) &&
        !String(answer?.aid || '').endsWith('comment') &&
        hasValue(answer)
      ) {
        selectedByResponse[answer.responseId] = answer.value
      }
    })

    const comments = answers
      .filter(
        (answer) =>
          belongsToQuestion(answer) &&
          String(answer?.aid || '').endsWith('comment') &&
          hasValue(answer)
      )
      .map((answer) => {
        // A bare "comment" aid is a question-wide comment (e.g. 'O'
        // list-with-comment); its sub-question is just a generic "Comment"
        // label, so group it by the response's selected option instead. Other
        // comment fields ("<subquestion>comment") keep their real sub-question.
        const isQuestionWide = String(answer?.aid || '') === 'comment'
        return {
          responseId: answer.responseId,
          comment: answer.value,
          subQuestion: isQuestionWide
            ? (selectedByResponse[answer.responseId] ?? null)
            : answer.subquestion ||
              selectedByResponse[answer.responseId] ||
              null,
          date: answer.date || null,
        }
      })

    return { comments, pagination: data?._meta?.pagination || null }
  }
}
