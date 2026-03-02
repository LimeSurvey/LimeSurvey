import { getQuestionById, RemoveHTMLTagsInString } from 'helpers'
import {
  containfilter,
  dateRangeFilter,
  multiSelectFilter,
  rangeFilter,
} from './filterTypes'

export const idColumnKey = 'id'
export const completedColumnKey = 'completed'

export const generateColumns = (surveyQuestions, survey) => {
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
    header: t('completed'),
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
    header: t('Date last action'),
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

    columns.push({
      accessorKey: qid.toString(),
      id: qid.toString(),
      header: `${RemoveHTMLTagsInString(question?.l10ns[survey.language]?.question)}`,
      meta: {
        question,
        questionNumber,
        language: survey.language,
        filterType: null,
        qid,
        sqid,
        aid,
        title: question?.title,
        keys: questionsInfo[qid].keys,
      },
    })
  })

  return columns
}
