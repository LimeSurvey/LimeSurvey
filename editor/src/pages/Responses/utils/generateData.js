import {
  getQuestionTypeInfo,
  isQuestionWithAnswers,
  isQuestionWithSubquestions,
} from 'components'
import {
  dayJsHelper,
  getAnswerById,
  getAnswerByProperty,
  getSubquestionById,
  getSubquestionByProperty,
  RemoveHTMLTagsInString,
} from 'helpers'
import { cloneDeep } from 'lodash'

export const generateData = (responses, language, generatedColumns) => {
  const data = []
  const questions = {}

  responses.map((response, index) => {
    data.push({})
    data[index].language = response.language
    data[index].id = response.id
    data[index].seed = response.seed
    data[index].submitDate = response.submitDate
    data[index].token = response.token
    data[index].firstName = response.firstName
    data[index].lastName = response.lastName
    data[index].email = response.email

    data[index].dateLastAction = dayJsHelper(response.dateLastAction).format(
      'MM-DD-YYYY HH:mm:ss'
    )
    data[index].startDate = dayJsHelper(response.startDate).format(
      'MM-DD-YYYY HH:mm:ss'
    )
    data[index].ipAddr = response.ipAddr
    data[index].refUrl = response.refUrl
    data[index].completed = response.completed
      ? 'ri-check-line text-success'
      : 'ri-close-large-line text-danger'

    data[index].answer = {}
    data[index].meta = {}

    Object.entries(response.answers).forEach(([, _answer]) => {
      const answer = cloneDeep(_answer)
      let { value, qid, sqid, actual_aid } = answer

      let question =
        questions[qid] ||
        generatedColumns?.find(
          (col) => col.accessorKey?.toString() === qid?.toString()
        )?.meta?.question

      if (!question) {
        data[index][qid] = []
        return
      }

      questions[qid] = question
      data[index][qid] ??= []
      data[index].answer[qid] ??= []

      if (
        question.questionThemeName === getQuestionTypeInfo().FILE_UPLOAD.theme
      ) {
        handleFileUploadQuestionType(
          answer.key,
          data,
          value,
          qid,
          index,
          response
        )
        return
      }

      const cell = data[index][qid]
      const questionAnswer =
        getAnswerById(actual_aid, question).answer ??
        getAnswerByProperty(answer.value, 'code', question).answer // answer.value is the answer code
      const questionSubquestion =
        getSubquestionById(sqid, question).subquestion ??
        getSubquestionByProperty(answer.aid, 'title', question).subquestion
      const hasAnswersOrSubquestions =
        isQuestionWithAnswers(question.questionThemeName) ||
        isQuestionWithSubquestions(question.questionThemeName)
      const maybeComment =
        !answer.actual_aid && !answer.sqid && answer.key?.includes('comment')

      answer.aid = actual_aid
      const idName = isQuestionWithAnswers(question.questionThemeName)
        ? 'aid'
        : 'sqid'

      if (
        !questionAnswer &&
        !questionSubquestion &&
        !hasAnswersOrSubquestions
      ) {
        cell.push({
          value: RemoveHTMLTagsInString(value),
          key: answer.key,
          aid: answer.actual_aid,
          [idName]: answer[idName],
          responseId: response.id,
        })
      } else {
        if (maybeComment) {
          value = !questionAnswer
            ? RemoveHTMLTagsInString(value)
            : RemoveHTMLTagsInString(questionAnswer?.l10ns[language]?.answer)

          if (!cell.length) {
            cell.push({
              value: `✖ ${t('No answer')}`,
              key: answer.key,
              aid: answer.actual_aid,
              [idName]: answer[idName],
            })
          }

          cell[cell.length - 1].comment = {
            value,
            key: answer.key,
            // comments does not include aid or sqid, so we should keep the main answer's aid or sqid (which is [cell.length - 1]).
            // note: it might not be used at all anyways and might be removed later.
            [idName]: cell[cell.length - 1][idName],
          }
        } else {
          cell.push({
            value: value,
            key: answer.key,
            aid: answer.actual_aid,
            [idName]: answer[idName],
            qid: answer[idName],
            checked: value ? true : false,
            responseId: response.id,
            questionThemeName: question.questionThemeName,
            subquestionTitle:
              RemoveHTMLTagsInString(
                questionSubquestion?.l10ns[language]?.question
              ) || value,
            answerTitle:
              RemoveHTMLTagsInString(questionAnswer?.l10ns[language]?.answer) ||
              value,
          })
        }
      }

      answer.aid = actual_aid
      delete answer.question
      data[index].answer[qid].push(answer)
    })
  })

  return data
}

const handleFileUploadQuestionType = (
  key,
  data,
  value,
  qid,
  index,
  response
) => {
  if (key?.endsWith('_filecount') || !value) {
    return
  }

  try {
    const filesInfo = JSON.parse(value) || []
    data[index][qid] = []
    data[index].meta[qid] = filesInfo

    let hasFiles = false
    filesInfo.forEach(({ name, size, title, comment, isDeleted }) => {
      const approxFileSizeInMB = (size / 1000).toFixed(1)
      hasFiles = hasFiles || !isDeleted

      data[index][qid].push({
        name,
        key,
        size,
        title,
        comment,
        approxFileSizeInMB,
        responseId: response.id,
        isDeleted,
      })
    })

    data[index].hasFiles = hasFiles
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error('Error parsing file upload value:', error)
  }
}
