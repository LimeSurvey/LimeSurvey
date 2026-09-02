import { getQuestionById, RemoveHTMLTagsInString } from 'helpers'
import {
  containfilter,
  dateRangeFilter,
  multiSelectFilter,
  rangeFilter,
} from './filterTypes'

export const idColumnKey = 'id'
export const completedColumnKey = 'completed'

export const getInitialColumnVisibility = (columns) =>
  Object.fromEntries(
    columns
      .filter(({ meta }) => meta?.visibleByDefault === false)
      .map(({ id }) => [id, false])
  )

const createQuestionLabel = (question, language) => {
  const code = question?.title
  const text = RemoveHTMLTagsInString(question?.l10ns?.[language]?.question)

  return code && text ? { code, text } : undefined
}

const createTimingColumn = (
  { fieldname, question: header, qid, type },
  survey
) => {
  const question =
    type === 'answer_time' && Array.isArray(survey.questionGroups)
      ? getQuestionById(qid, survey)?.question
      : undefined
  const questionLabel = createQuestionLabel(question, survey.language)

  return {
    id: fieldname,
    accessorKey: fieldname,
    header: questionLabel?.text ?? header,
    enableSorting: false,
    meta: {
      survey,
      keys: [fieldname],
      columnCategory: 'timing',
      questionLabel,
      title: questionLabel
        ? `${t('Question time')}: ${questionLabel.code}`
        : undefined,
      qid,
      timingType: type,
      visibleByDefault: false,
    },
  }
}

export const generateColumns = (surveyQuestions, survey, timingFields = []) => {
  const columns = []

  if (!survey.sid) {
    return []
  }

  const datestamp = survey.datestamp

  columns.push({
    id: idColumnKey,
    accessorKey: idColumnKey,
    header: t('ID'),
    meta: {
      survey,
      filterType: rangeFilter,
      keys: [idColumnKey],
    },
    filterFn: rangeFilter,
    props: {
      min: 0,
      type: 'number',
    },
  })

  columns.push({
    id: completedColumnKey,
    accessorKey: completedColumnKey,
    header: t('Completed'),
    meta: {
      survey,
      filterType: multiSelectFilter,
      answerOptions: [false, true],
      keys: [completedColumnKey],
    },
    filterFn: multiSelectFilter,
  })

  if (survey?.hasTokens) {
    columns.push({
      id: 'token',
      accessorKey: 'token',
      header: t('Token'),
      meta: {
        keys: ['token'],
        filterType: containfilter,
      },
    })

    columns.push({
      id: 'firstName',
      accessorKey: 'firstName',
      header: t('First name'),
      meta: {
        keys: ['firstName'],
      },
    })

    columns.push({
      id: 'lastName',
      accessorKey: 'lastName',
      header: t('Last name'),
      meta: {
        keys: ['lastName'],
      },
    })

    columns.push({
      id: 'email',
      accessorKey: 'email',
      header: t('Email'),
      meta: {
        keys: ['email'],
      },
    })
  }

  columns.push({
    id: 'dateLastAction',
    accessorKey: 'dateLastAction',
    header: t('Date of last action'),
    meta: {
      survey,
      filterType: dateRangeFilter,
      keys: ['dateLastAction'],
    },
    filterFn: dateRangeFilter,
  })

  columns.push({
    id: 'seed',
    accessorKey: 'seed',
    header: t('Seed'),
    meta: {
      survey,
      filterType: rangeFilter,
      keys: ['seed'],
    },
    filterFn: rangeFilter,
  })

  columns.push({
    id: 'language',
    accessorKey: 'language',
    header: t('Language'),
    meta: {
      survey,
      filterType: multiSelectFilter,
      keys: ['language'],
      answerOptions: survey.languages,
    },
  })

  if (datestamp) {
    columns.push({
      id: 'submitDate',
      accessorKey: 'submitDate',
      header: t('Submit date'),
      meta: {
        survey,
        filterType: dateRangeFilter,
        keys: ['submitDate'],
      },
      filterFn: dateRangeFilter,
    })
  }

  columns.push(
    ...timingFields
      .filter(({ fieldname }) => fieldname)
      .map((field) => createTimingColumn(field, survey))
  )

  let questionsInfo = {}

  Object.entries(surveyQuestions).map(([key, value]) => {
    const { qid, sqid, actual_aid: aid } = value

    if (!questionsInfo[qid]) {
      const questionInfo = getQuestionById(qid, survey)

      questionsInfo[qid] = {
        question: questionInfo?.question,
        questionNumber: questionInfo?.questionNumber,
        keys: [key],
      }
    } else {
      questionsInfo[qid].keys.push(key)
      return
    }

    if (!questionsInfo[qid]) {
      // todo: handle this scenario properly.
      return
    }

    const question = questionsInfo[qid].question
    const questionNumber = questionsInfo[qid].questionNumber
    const questionLabel = createQuestionLabel(question, survey.language)

    columns.push({
      accessorKey: qid.toString(),
      id: qid.toString(),
      header: questionLabel?.text ?? '',
      meta: {
        question,
        questionNumber,
        language: survey.language,
        filterType: null,
        qid,
        sqid,
        aid,
        title: question?.title,
        questionLabel,
        keys: questionsInfo[qid].keys,
      },
    })
  })

  return columns
}
