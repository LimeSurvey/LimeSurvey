import { RestClient } from './restClient.service'

// An answer belongs to a question when its field-map `title` is the question
// code (every column of a question shares that title).
const belongsToQuestion = (answer, questionCode) =>
  String(answer?.title) === String(questionCode)

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
   * POST the survey-response-answers endpoint scoped to a single question and
   * return its flat answer list plus the pagination envelope. Shared prologue
   * of getQuestionComments and getQuestionResponses.
   */
  fetchQuestionAnswers = async (
    sid,
    questionCode,
    currentPage,
    pageSize,
    language,
    answerValue
  ) => {
    // Scope to this question's columns so the endpoint doesn't return (and
    // transfer) every other question's answers.
    const body = { page: { currentPage, pageSize }, questionCode }
    if (language) {
      body.language = language
    }
    if (answerValue) {
      body.answerValue = answerValue
    }

    const data = await this.restClient.post(
      `survey-response-answers/${sid}`,
      body
    )

    return {
      answers: data?.answers || [],
      pagination: data?._meta?.pagination || null,
    }
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
    selectedAnswer = ''
  ) => {
    // When an answer is selected, let the endpoint page through only that
    // answer's responses (server-side filter via answerValue).
    const { answers, pagination } = await this.fetchQuestionAnswers(
      sid,
      questionCode,
      currentPage,
      pageSize,
      language,
      selectedAnswer
    )

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
        belongsToQuestion(answer, questionCode) &&
        !String(answer?.aid || '').endsWith('comment') &&
        hasValue(answer)
      ) {
        selectedByResponse[answer.responseId] = answer.value
      }
    })

    const comments = answers
      .filter(
        (answer) =>
          belongsToQuestion(answer, questionCode) &&
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

    return { comments, pagination }
  }

  /**
   * Load the raw per-response answers of an Array (Texts) question and pivot
   * them into a table: one row per participant (response), one column per
   * subquestion cell. Each cell answer carries its `responseId`, the question
   * `title` (code) and the `subquestion1`/`subquestion2` scale labels, so the
   * columns and rows can be reconstructed from the flat answer list.
   */
  getQuestionResponses = async (
    sid,
    questionCode,
    currentPage = 0,
    pageSize = 15,
    language
  ) => {
    const { answers, pagination } = await this.fetchQuestionAnswers(
      sid,
      questionCode,
      currentPage,
      pageSize,
      language
    )

    // Columns in first-seen (field map) order; rows grouped by response.
    const columnByKey = {}
    const columnOrder = []
    const rowByResponse = {}
    const rowOrder = []

    answers
      .filter((answer) => belongsToQuestion(answer, questionCode))
      .forEach((answer) => {
        const columnKey = answer.key
        if (columnKey && !columnByKey[columnKey]) {
          const primary = answer.subquestion1 || answer.subquestion || ''
          const secondary = answer.subquestion1 ? answer.subquestion2 || '' : ''
          columnByKey[columnKey] = { key: columnKey, primary, secondary }
          columnOrder.push(columnKey)
        }

        const responseId = answer.responseId
        if (responseId != null && !rowByResponse[responseId]) {
          rowByResponse[responseId] = { responseId, cells: {} }
          rowOrder.push(responseId)
        }
        if (responseId != null && columnKey) {
          rowByResponse[responseId].cells[columnKey] = answer.value ?? ''
        }
      })

    return {
      columns: columnOrder.map((key) => columnByKey[key]),
      rows: rowOrder.map((id) => rowByResponse[id]),
      pagination,
    }
  }
}
