// Turn the survey object into a clean list the dropdowns can display.
import { RemoveHTMLTagsInString } from 'helpers'

// Pick the localized text for the active language, falling back to the first
// available language so a question/answer never renders blank.
const localized = (l10ns, language, key) => {
  if (!l10ns) return ''
  const entry = l10ns[language] || l10ns[Object.keys(l10ns)[0]]
  return entry?.[key] || ''
}

// Free-text question types (short / long / huge text).
const FREE_TEXT_TYPES = ['S', 'T', 'U']

// Subquestion-based types → how each sub-question is filtered:
//   M/P (multiple choice) → Checked / Not checked
//   Q   (multiple short text) → contains
//   K   (multiple numerical) → min/max
const SUBQUESTION_KINDS = {
  M: 'subCheckbox',
  P: 'subCheckbox',
  Q: 'subText',
  K: 'subNumber',
}

// How a question should be filtered, derived from its type code:
//   'number' → min/max range   'date' → start/end   'text' → contains
//   'sub*'   → pick a sub-question, then a value (see SUBQUESTION_KINDS)
//   'answers' → pick answer option(s)  (the default)
// Remaining subquestion types (arrays, dual-scale, grids, ranking) fall through
// to 'answers' with an empty option list for now — see the note below.
const getQuestionKind = (type) => {
  if (type === 'N') return 'number'
  if (type === 'D') return 'date'
  if (FREE_TEXT_TYPES.includes(type)) return 'text'
  if (SUBQUESTION_KINDS[type]) return SUBQUESTION_KINDS[type]
  return 'answers'
}

// Sub-question options for the subquestion-based types. These types use a single
// axis, so keep only scaleId 0.
const buildSubquestions = (question, language) =>
  (question.subquestions || [])
    .filter((sq) => sq.scaleId === 0)
    .map((sq) => {
      const sqText = RemoveHTMLTagsInString(
        localized(sq.l10ns, language, 'question')
      )
      return {
        value: sq.qid,
        label: sqText ? `${sq.title} - ${sqText}` : sq.title,
      }
    })

// Some flat answer types keep their options built-in rather than stored in
// `question.answers`, so we generate them here (matching the editor's
// condition-designer handlers). Returns null for types that use real answers.
const synthesizeAnswerOptions = (type) => {
  switch (type) {
    case '5': // 5 point choice → 1..5
      return [1, 2, 3, 4, 5].map((n) => ({
        value: String(n),
        label: String(n),
      }))
    case 'G': // Gender
      return [
        { value: 'F', label: t('Female (F)') },
        { value: 'M', label: t('Male (M)') },
      ]
    case 'Y': // Yes/No
      return [
        { value: 'Y', label: t('Yes') },
        { value: 'N', label: t('No') },
      ]
    default:
      return null
  }
}

// Flatten the survey structure into Select-ready options for the filter builder:
//   [{ value: qid, label: 'Q01 (question text)',
//      answerOptions: [{ value: code, label: 'A1 (answer text)' }] }]
// Uses the survey object (complete + already loaded), not the chart-aggregated
// statistics array.
export const buildQuestionOptions = (survey, language) => {
  const groups = survey?.questionGroups || []
  const options = []

  groups.forEach((group) => {
    const questions = group.questions || []
    questions.forEach((question) => {
      const text = RemoveHTMLTagsInString(
        localized(question.l10ns, language, 'question')
      )
      const label = text ? `${question.title} - ${text}` : question.title
      const synthesized = synthesizeAnswerOptions(question.type)
      const answerOptions =
        synthesized ??
        (question.answers || []).map((answer) => {
          const answerText = RemoveHTMLTagsInString(
            localized(answer.l10ns, language, 'answer')
          )
          return {
            value: answer.code,
            label: answerText ? `${answer.code} (${answerText})` : answer.code,
          }
        })

      // Real answer-based questions with an "other" option expose an extra
      // pseudo-answer.
      if (!synthesized && question.other === 'Y') {
        answerOptions.push({ value: '-oth-', label: t('Other') })
      }

      const kind = getQuestionKind(question.type)
      const subquestions = SUBQUESTION_KINDS[question.type]
        ? buildSubquestions(question, language)
        : []

      options.push({
        value: question.qid,
        label,
        kind,
        answerOptions,
        subquestions,
      })
    })
  })

  return options
}
