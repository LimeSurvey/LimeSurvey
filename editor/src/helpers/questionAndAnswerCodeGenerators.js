const findMaxCode = (codes, defaultPrefix) => {
  return codes.reduce(
    (max, code) => {
      const match = code.match(/^([^\d]*)(\d+)$/)
      if (match) {
        const [, prefix, numericPart] = match
        const numericCode = parseInt(numericPart, 10)
        return numericCode > max.numeric
          ? { numeric: numericCode, prefix: prefix || defaultPrefix }
          : max
      }
      return max
    },
    { numeric: 0, prefix: defaultPrefix }
  )
}

export const getNextQuestionCode = (codeToQuestion) => {
  const maxCode = findMaxCode(Object.keys(codeToQuestion), 'Q')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}

export const ANSWER_CODE_MAX_LENGTH = 5
export const SUBQUESTION_CODE_MAX_LENGTH = 20
const LEGACY_PAD_WIDTH = 3

const sanitizePrefix = (prefix) =>
  typeof prefix === 'string' ? prefix.replace(/[^A-Za-z0-9]/g, '') : ''

/**
 * Finds the greatest numeric suffix among the codes. Unlike findMaxCode, an
 * empty prefix is kept as is, so purely numeric codes stay numeric.
 */
const findMaxChildCode = (codes, fallbackPrefix) => {
  let max = null
  codes.forEach((code) => {
    const match = String(code).match(/^([^\d]*)(\d+)$/)
    if (!match) {
      return
    }
    const numeric = parseInt(match[2], 10)
    if (max === null || numeric > max.numeric) {
      max = { numeric, prefix: match[1], digits: match[2] }
    }
  })
  return max ?? { numeric: 0, prefix: fallbackPrefix, digits: null }
}

const buildChildCode = (prefix, numeric, padWidth, maxLength) => {
  const digits = String(numeric)
  let width = Math.max(digits.length, padWidth)
  if (prefix.length + width > maxLength) {
    width = Math.max(digits.length, maxLength - prefix.length)
  }
  const truncatedPrefix = prefix.slice(
    0,
    Math.max(0, maxLength - digits.length)
  )
  return `${truncatedPrefix}${digits.padStart(width, '0')}`
}

/**
 * Generates the next code for an answer option or subquestion.
 * - `prefix` is the configured prefix; an empty or missing prefix produces
 *   purely numeric codes (there is no letter fallback).
 * - `seed` is an index (0 => first code) used when seeding new questions.
 * - Existing codes win: the next code continues the greatest one, keeping its
 *   prefix and, for purely numeric codes, its width (1 -> 2, 009 -> 010).
 */
const getNextChildCode = ({ existingCodes, seed, prefix, maxLength }) => {
  const configuredPrefix = sanitizePrefix(prefix)
  const usedCodes = new Set(existingCodes.map((c) => String(c).toLowerCase()))

  let max
  if (seed !== undefined) {
    max = { numeric: seed, prefix: configuredPrefix, digits: null }
  } else {
    max = findMaxChildCode(existingCodes, configuredPrefix)
  }

  const isNumeric = max.prefix === ''
  const padWidth = isNumeric ? (max.digits?.length ?? 1) : LEGACY_PAD_WIDTH

  let numeric = max.numeric + 1
  let code = buildChildCode(max.prefix, numeric, padWidth, maxLength)
  while (usedCodes.has(code.toLowerCase())) {
    numeric += 1
    code = buildChildCode(max.prefix, numeric, padWidth, maxLength)
  }
  return code
}

const resolveChildren = (entity, initialCode, listKey, codeKey) => {
  if (typeof initialCode === 'number') {
    return { seed: initialCode, codes: [] }
  }
  if (initialCode !== null && initialCode !== undefined) {
    return { seed: undefined, codes: [String(initialCode)] }
  }
  const hasQid = entity?.qid !== null && entity?.qid !== undefined
  const children = hasQid ? (entity[listKey] ?? []) : []
  return {
    seed: undefined,
    codes: children.map((c) => c[codeKey]).filter(Boolean),
  }
}

export const getNextSubQuestionCode = (
  question,
  initialCode = null,
  prefix
) => {
  const { seed, codes } = resolveChildren(
    question,
    initialCode,
    'subquestions',
    'title'
  )
  return getNextChildCode({
    existingCodes: codes,
    seed,
    prefix,
    maxLength: SUBQUESTION_CODE_MAX_LENGTH,
  })
}

export const getNextAnswerCode = (question, initialCode = null, prefix) => {
  const { seed, codes } = resolveChildren(
    question,
    initialCode,
    'answers',
    'code'
  )
  return getNextChildCode({
    existingCodes: codes,
    seed,
    prefix,
    maxLength: ANSWER_CODE_MAX_LENGTH,
  })
}

/**
 * Numbers answer options 1..n (no prefix) per scale, following sortOrder.
 * Idempotent: already numbered answers keep their code.
 */
export const getNumericAnswerCodes = (answers = []) => {
  const counters = {}
  const sorted = [...answers].sort((a, b) => a.sortOrder - b.sortOrder)
  const codes = new Map()
  sorted.forEach((answer) => {
    const scale = answer.scaleId ?? 0
    counters[scale] = (counters[scale] || 0) + 1
    codes.set(answer, String(counters[scale]))
  })
  return answers.map((answer) => ({ ...answer, code: codes.get(answer) }))
}

export const CODE_PREFIX_SETTING_NAMES = {
  answer: 'answeroptionprefix',
  subquestion: 'subquestionprefix',
}

/**
 * Turns the outcome of loading a prefix setting into a prefix.
 * - found with a string (including ''): the configured prefix
 * - anything else (no stored setting, null, load failure): '' so codes are
 *   purely numeric
 */
export const resolveCodePrefix = (setting) =>
  setting?.found && typeof setting.value === 'string' ? setting.value : ''
