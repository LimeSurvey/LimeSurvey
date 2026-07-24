import {
  QT_O_LIST_WITH_COMMENT,
  QT_P_MULTIPLE_CHOICE_WITH_COMMENTS,
} from 'helpers'

import { RestClient } from './restClient.service'

// An answer belongs to a question when its field-map `title` is the question
// code (every column of a question shares that title).
const belongsToQuestion = (answer, questionCode) =>
  String(answer?.title) === String(questionCode)

const buildResponseFilters = ({ completed, minId, maxId } = {}) => {
  const filters = []
  if (typeof completed === 'boolean') {
    filters.push({
      key: 'completed',
      filterMethod: 'equal',
      value: String(completed),
    })
  }

  let min = typeof minId === 'number' ? minId : ''
  const max = typeof maxId === 'number' ? maxId : ''
  if (min !== '' && max !== '' && min > max) {
    min = max
  }
  if (min !== '' || max !== '') {
    filters.push({ key: 'id', filterMethod: 'range', value: [min, max] })
  }

  return filters
}

const buildAnswerFilters = (
  selectedAnswer,
  fields,
  questionType,
  selectedField
) => {
  if (!selectedAnswer || !fields?.length) {
    return []
  }

  if (questionType === QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
    if (selectedField && fields.includes(selectedField)) {
      return [{ key: selectedField, filterMethod: 'equal', value: 'Y' }]
    }
    return []
  }

  if (questionType === QT_O_LIST_WITH_COMMENT) {
    const baseField = fields.find((field) => !field.endsWith('comment'))
    if (baseField) {
      return [{ key: baseField, filterMethod: 'equal', value: selectedAnswer }]
    }
  }

  return []
}

const buildSearchFilters = (search, fields) => {
  if (!Array.isArray(search) || !search.length || !fields?.length) {
    return []
  }
  return search.map((term) => ({
    key: fields,
    filterMethod: 'contain',
    value: term,
  }))
}

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

  getSurveyStatistics = async (
    sid,
    filters,
    page = 0,
    pageSize = 15,
    language
  ) => {
    let { completed, minId, maxId, search } = filters

    // Ensure minId is not greater than maxId, if provided.
    if (Number(minId) > Number(maxId) && maxId) {
      minId = maxId
    }

    const minIdIsNumber = typeof minId === 'number'
    const maxIdIsNumber = typeof maxId === 'number'

    const searchParams = (Array.isArray(search) ? search : [])
      .map((term) => `&search[]=${encodeURIComponent(term)}`)
      .join('')

    const languageParam = language
      ? `&language=${encodeURIComponent(language)}`
      : ''

    const queryFilters = `${typeof completed === 'boolean' ? `completed=${completed}` : ''}${minIdIsNumber ? `&minId=${minId}` : ''}${maxIdIsNumber ? `&maxId=${maxId}` : ''}${searchParams}${languageParam}&page=${page}&pageSize=${pageSize}`
    return await this.restClient.get(`statistics/${sid}?${queryFilters}`)
  }

  getSurveyStatisticsAtGlance = async (sid) => {
    return await this.restClient.get(`statistics-glance/${sid}`)
  }

  fetchQuestionAnswers = async (
    sid,
    questionCode,
    currentPage,
    pageSize,
    language,
    fields,
    sort,
    filters
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
    if (Array.isArray(filters) && filters.length) {
      body.filters = filters
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
    fields,
    questionType,
    selectedField = ''
  ) => {
    const { answers, pagination } = await this.fetchQuestionAnswers(
      sid,
      questionCode,
      currentPage,
      pageSize,
      language,
      fields,
      { submitDate: 'desc' },
      buildAnswerFilters(selectedAnswer, fields, questionType, selectedField)
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
        const isQuestionWide = String(answer?.aid || '') === 'comment'
        const subQuestionCode = String(answer?.aid || '').replace(
          /comment$/,
          ''
        )
        return {
          responseId: answer.responseId,
          comment: answer.value,
          subQuestion: isQuestionWide
            ? (selectedByResponse[answer.responseId] ?? null)
            : subQuestionCode ||
              answer.subquestion ||
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
    fields,
    statisticsFilters,
    search
  ) => {
    const { answers, pagination } = await this.fetchQuestionAnswers(
      sid,
      questionCode,
      currentPage,
      pageSize,
      language,
      fields,
      { submitDate: 'desc' },
      [
        ...buildResponseFilters(statisticsFilters),
        ...buildSearchFilters(
          [
            ...new Set([
              ...(statisticsFilters?.search ?? []),
              ...(search ?? []),
            ]),
          ],
          fields
        ),
      ]
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
          rowByResponse[responseId] = {
            responseId,
            date: answer.date ?? null,
            cells: {},
          }
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
