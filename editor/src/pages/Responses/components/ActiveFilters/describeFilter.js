import { dayJsHelper, getSurveyParticipantAttributes } from 'helpers'

import {
  FIELD_KIND,
  FILE_UPLOADED,
  INCLUDED,
  SOURCE,
  getSurveyDataFieldOptions,
  getSurveyFieldKind,
} from '../ResponsesStatistics/StatisticsFiltersModal/utils'


const formatDate = (date) => dayJsHelper(date).format('D MMM YYYY')

const isSet = (value) => value !== '' && value !== null && value !== undefined

const formatRange = (min, max, formatValue = String) => {
  if (isSet(min) && isSet(max)) {
    return `${formatValue(min)} – ${formatValue(max)}`
  }
  if (isSet(min)) return `≥ ${formatValue(min)}`
  if (isSet(max)) return `≤ ${formatValue(max)}`
  return ''
}

const quote = (text) => `"${text}"`

const labelOf = (options = [], value) =>
  options.find((option) => String(option.value) === String(value))?.label ??
  String(value)

const includedLabels = () => ({
  [INCLUDED.ALL]: t('All'),
  [INCLUDED.COMPLETE]: t('Complete'),
  [INCLUDED.INCOMPLETE]: t('Incomplete'),
})

const questionValues = (filter, question) => {
  const { array = {}, ranking = {} } = question

  switch (filter.questionKind ?? question.kind) {
    case 'text':
      return [quote(filter.textValue)]
    case 'number':
      return [formatRange(filter.numberMin, filter.numberMax)]
    case 'date':
      return [formatRange(filter.dateFrom, filter.dateTo, formatDate)]
    case 'subText':
      return [
        `${labelOf(question.subquestions, filter.subquestion)}: ${quote(filter.textValue)}`,
      ]
    case 'subNumber':
      return [
        `${labelOf(question.subquestions, filter.subquestion)}: ${formatRange(filter.numberMin, filter.numberMax)}`,
      ]
    case 'arrayScale':
      return [
        `${labelOf(array.rows, filter.row)}: ${labelOf(array.columns, filter.column)}`,
      ]
    case 'arrayDual': {
      // One bullet per scale chosen, each named by its header.
      const row = labelOf(array.rows, filter.row)
      return [
        [array.columnLabel, array.columns, filter.column],
        [array.columnLabel2, array.columns2, filter.column2],
      ]
        .filter(([, , value]) => isSet(value))
        .map(
          ([header, columns, value]) =>
            `${row} (${header}): ${labelOf(columns, value)}`
        )
    }
    case 'arrayGrid': {
      const value =
        filter.textValue !== ''
          ? quote(filter.textValue)
          : formatRange(filter.numberMin, filter.numberMax)
      return [
        `${labelOf(array.rows, filter.row)} / ${labelOf(array.columns, filter.column)}: ${value}`,
      ]
    }
    case 'ranking':
      return [
        labelOf(ranking.ranks, filter.row),
        labelOf(ranking.items, filter.column),
      ]
    case 'fileUpload': {
      const uploaded = filter.fileUploaded === FILE_UPLOADED.YES
      const lines = [`${t('File uploaded')}: ${uploaded ? t('Yes') : t('No')}`]
      if (uploaded && filter.textValue !== '') {
        lines.push(quote(filter.textValue))
      }
      return lines
    }
    default:
      return filter.answerCodes.map((code) =>
        labelOf(question.answerOptions, code)
      )
  }
}

const describeQuestion = (filter, questionOptions) => {
  const question = questionOptions.find(
    (option) => option.value === filter.questionQid
  )
  if (!question) {
    return { label: t('Question'), heading: t('Question'), lines: [] }
  }

  const lines = questionValues(filter, question)
  return {
    label: `${question.code} (${lines.length})`,
    heading: question.code,
    text: question.text,
    lines,
  }
}

const describeSurveyData = (filter) => {
  const field = labelOf(getSurveyDataFieldOptions(), filter.surveyField)

  switch (getSurveyFieldKind(filter.surveyField)) {
    case FIELD_KIND.INCLUDED: {
      const label = `${field}: ${includedLabels()[filter.included]}`
      return { label, heading: label, lines: [] }
    }
    case FIELD_KIND.DATE_RANGE: {
      // The dates are the chip; which date they bound is in the tooltip.
      const range = formatRange(filter.dateFrom, filter.dateTo, formatDate)
      return {
        label: range || field,
        heading: field,
        lines: range ? [range] : [],
      }
    }
    case FIELD_KIND.NUMBER_RANGE: {
      const range = formatRange(filter.numberMin, filter.numberMax)
      const label = range ? `${field}: ${range}` : field
      return { label, heading: label, lines: [] }
    }
    case FIELD_KIND.LANGUAGE_MULTI:
      return {
        label: filter.languages.length
          ? `${field} (${filter.languages.length})`
          : field,
        heading: field,
        lines: filter.languages,
      }
    default:
      return { label: field, heading: field, lines: [] }
  }
}

const describeParticipant = (filter, survey) => {
  const attribute = labelOf(
    getSurveyParticipantAttributes(survey),
    filter.attribute
  )
  const label = isSet(filter.attributeValue)
    ? `${attribute}: ${filter.attributeValue}`
    : attribute
  return { label, heading: label, lines: [] }
}

/**
 * @param {Object} filter An applied, complete filter row.
 * @param {Object} context `questionOptions` from buildQuestionOptions and the
 *     `survey` (for the participant attribute names).
 * @returns {{label: string, heading: string, text?: string, lines: string[]}}
 */
export const describeFilter = (filter, { questionOptions = [], survey }) => {
  switch (filter.source) {
    case SOURCE.QUESTION:
      return describeQuestion(filter, questionOptions)
    case SOURCE.SURVEY_DATA:
      return describeSurveyData(filter)
    case SOURCE.PARTICIPANT:
      return describeParticipant(filter, survey)
    default:
      return { label: '', heading: '', lines: [] }
  }
}
