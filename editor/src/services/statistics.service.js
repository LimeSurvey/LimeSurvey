import { RestClient } from './restClient.service'

// An answer belongs to a question when its field-map `title` is the question
// code (every column of a question shares that title).
const belongsToQuestion = (answer, questionCode) =>
  String(answer?.title) === String(questionCode)

// Flatten the per-response answers into a single list, tagging each answer with
// the response it belongs to and a single date (mirrors the server's flattening
// of the old survey-response-answers endpoint).
const flattenAnswers = (responses) =>
  (responses || []).flatMap((response) => {
    const date =
      response.submitDate ??
      response.dateLastAction ??
      response.startDate ??
      null
    return Object.values(response.answers || {}).map((answer) => ({
      ...answer,
      responseId: response.id ?? null,
      date,
    }))
  })

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
   * POST the survey-responses endpoint and flatten the per-response answers
   * into a single list plus the pagination envelope. Shared prologue of
   * getQuestionComments and getQuestionResponses. `fields` restricts the query
   * to the question's own columns so other questions' answers are not returned
   * or transferred; the answers are still scoped client-side via
   * `belongsToQuestion` as a safety net.
   */
  fetchQuestionAnswers = async (
    sid,
    questionCode,
    currentPage,
    pageSize,
    language,
    fields,
    sort
  ) => {
    const body = { page: { currentPage, pageSize } }
    if (language) {
      body.language = language
    }
    if (Array.isArray(fields) && fields.length) {
      body.fields = fields
    }
    if (sort) {
      body.sort = sort
    }

    const data = await this.restClient.post(`survey-responses/${sid}`, body)

    return {
      answers: flattenAnswers(data?.responses),
      pagination: data?._meta?.pagination || null,
    }
  }

  /**
   * Load every comment of a question.
   *
   * Comments live in the response table, so we use the survey-responses
   * endpoint and flatten it into a list of answers (each carrying its `title`,
   * `aid`, `subquestion` and the `responseId`). We keep the comment answers
   * (`aid` ending in "comment") belonging to the requested question.
   */
  getQuestionComments = async (
    sid,
    questionCode,
    currentPage = 0,
    pageSize = 15,
    language,
    selectedAnswer = '',
    fields
  ) => {
    const { answers, pagination } = await this.fetchQuestionAnswers(
      sid,
      questionCode,
      currentPage,
      pageSize,
      language,
      fields,
      // Page through responses newest-first by submit date so comments load
      // chronologically; response id order doesn't track date (imported/resumed
      // responses), which is why pages otherwise mix recent and old comments.
      { submitDate: 'desc' }
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

    // Answer-option filter: keep only comments whose chosen option (the
    // comment's sub-question, which for question-wide types is the response's
    // selected answer code) matches the selection. Done client-side because the
    // flat list is paged over responses, not narrowed to a column server-side.
    const filtered = selectedAnswer
      ? comments.filter(
          (comment) => String(comment.subQuestion) === String(selectedAnswer)
        )
      : comments

    return { comments: filtered, pagination }
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
    language,
    fields
  ) => {
    const { answers, pagination } = await this.fetchQuestionAnswers(
      sid,
      questionCode,
      currentPage,
      pageSize,
      language,
      fields
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
