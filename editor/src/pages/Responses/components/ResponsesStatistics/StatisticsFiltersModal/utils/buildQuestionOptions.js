// Turn the survey object into a clean list the dropdowns can display.
import { RemoveHTMLTagsInString, getAttributeValue } from 'helpers'

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

// Array types → row × column (+ optional value):
//   arrayScale (F/H) → row (subquestion) × column (answer scale) (point choice/column)
//   arrayDual  (1)   → row × two answer scales
//   arrayGrid  (:/;) → row × column (both subquestions) + value (numbers/text)
const ARRAY_KINDS = {
  'F': 'arrayScale',
  'H': 'arrayScale',
  '1': 'arrayDual',
  ':': 'arrayGrid',
  ';': 'arrayGrid',
}

// How a question should be filtered, derived from its type code:
//   'number' → min/max range   'date' → start/end   'text' → contains
//   'sub*'   → pick a sub-question, then a value (see SUBQUESTION_KINDS)
//   'array*' → pick a row + column (+ value) (see ARRAY_KINDS + `array`)
//   'ranking' (R) → pick a rank position + an item (see `ranking`)
//   'answers' → pick answer option(s)  (the default)
const getQuestionKind = (type) => {
  if (type === 'N') return 'number'
  if (type === 'D') return 'date'
  if (FREE_TEXT_TYPES.includes(type)) return 'text'
  if (SUBQUESTION_KINDS[type]) return SUBQUESTION_KINDS[type]
  if (ARRAY_KINDS[type]) return ARRAY_KINDS[type]
  if (type === 'R') return 'ranking'
  return 'answers'
}

// Sub-question options for a given axis: scaleId 0 = rows (default), 1 = grid columns.
const buildSubquestions = (question, language, scaleId = 0) =>
  (question.subquestions || [])
    .filter((sq) => sq.scaleId === scaleId)
    .map((sq) => {
      const sqText = RemoveHTMLTagsInString(
        localized(sq.l10ns, language, 'question')
      )
      return {
        value: sq.qid,
        label: sqText || sq.title,
      }
    })

// Map answer objects → Select options. Show just the answer text; fall back to
// the code only when an answer has no text (so an option is never blank).
const mapAnswers = (answers, language) =>
  (answers || []).map((answer) => {
    const answerText = RemoveHTMLTagsInString(
      localized(answer.l10ns, language, 'answer')
    )
    return {
      value: answer.code,
      label: answerText || answer.code,
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

// Build the `array` descriptor (rows / columns / value) for an array question,
// per its kind. `rows` are always the scaleId-0 subquestions.
const buildArray = (question, language, kind) => {
  const rows = buildSubquestions(question, language, 0)

  if (kind === 'arrayGrid') {
    // Columns are the scaleId-1 subquestions; each cell holds a value.
    return {
      rows,
      columns: buildSubquestions(question, language, 1),
      valueKind: question.type === ':' ? 'number' : 'text',
    }
  }

  if (kind === 'arrayDual') {
    // Two answer scales split by scaleId; labels from the dual-scale headers.
    const scale = (scaleId) =>
      mapAnswers(
        (question.answers || []).filter((a) => a.scaleId === scaleId),
        language
      )
    // The dual-scale header attribute comes in two shapes depending on the
    // source: the live editor stores `{ [lang]: 'Custom header' }` (a plain
    // string, as the condition designer reads it), while fixtures store
    // `{ [lang]: { value } }`. Handle both, falling back to "Scale 1/2".
    const header = (attrKey, fallback) => {
      const raw = getAttributeValue(question.attributes?.[attrKey], language)
      const value = typeof raw === 'object' ? raw?.value : raw
      return value?.trim() ? RemoveHTMLTagsInString(value) : fallback
    }
    return {
      rows,
      columns: scale(0),
      columns2: scale(1),
      columnLabel: header('dualscale_headerA', t('Scale 1')),
      columnLabel2: header('dualscale_headerB', t('Scale 2')),
    }
  }

  // arrayScale (F/H): columns are the question's answer scale.
  return {
    rows,
    columns: mapAnswers(question.answers, language),
  }
}

// Build the `ranking` descriptor (rank positions × items) for a ranking question
// (R, covers Ranking and Ranking advanced). Items are the subquestions (with a
// fallback to answers for the legacy shape); the number of rank positions is
// capped by the `max_answers` attribute when set.
const buildRanking = (question, language) => {
  const subItems = buildSubquestions(question, language)
  const options = subItems.length
    ? subItems
    : mapAnswers(question.answers, language)

  // `max_answers` is non-localized: { '': { value } } — same two-shape handling
  // as the dual-scale header. An empty/absent value means "rank all items".
  const rawMax = getAttributeValue(question.attributes?.max_answers, language)
  const maxValue = typeof rawMax === 'object' ? rawMax?.value : rawMax
  const maxAnswers = parseInt(maxValue, 10)
  const rankCount =
    Number.isInteger(maxAnswers) && maxAnswers > 0
      ? Math.min(maxAnswers, options.length)
      : options.length

  return {
    ranks: Array.from({ length: rankCount }, (_, i) => ({
      value: String(i + 1),
      label: `${t('Rank')} ${i + 1}`,
    })),
    items: options,
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
        synthesized ?? mapAnswers(question.answers, language)

      // Real answer-based questions with an "other" option expose an extra
      // pseudo-answer.
      if (!synthesized && question.other === 'Y') {
        answerOptions.push({ value: '-oth-', label: t('Other') })
      }

      const kind = getQuestionKind(question.type)
      const subquestions = SUBQUESTION_KINDS[question.type]
        ? buildSubquestions(question, language)
        : []
      const array = ARRAY_KINDS[question.type]
        ? buildArray(question, language, kind)
        : undefined
      const ranking =
        question.type === 'R' ? buildRanking(question, language) : undefined

      options.push({
        value: question.qid,
        label,
        kind,
        answerOptions,
        subquestions,
        array,
        ranking,
      })
    })
  })

  return options
}
