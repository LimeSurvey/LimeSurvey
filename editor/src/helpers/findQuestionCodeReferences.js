const collectStrings = (value, strings = []) => {
  if (typeof value === 'string') {
    strings.push(value)
  } else if (value && typeof value === 'object') {
    Object.values(value).forEach((child) => collectStrings(child, strings))
  }
  return strings
}

const escapeRegExp = (text) => text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

/**
 * Lists the other questions and groups whose relevance, texts or attributes
 * mention the given question code (e.g. Q1 == "A002", Q1.NAOK, {Q1_SQ001}).
 * Only what the editor has loaded is checked: conditions, default answers
 * and quotas live on the server and are not covered.
 *
 * @returns {string[]} question codes / group names that reference the code
 */
export const findQuestionCodeReferences = (survey, question) => {
  const code = question?.title
  if (!survey || !code) {
    return []
  }
  // The code followed by anything but more code characters: Q1, Q1.NAOK, Q1_SQ001
  const pattern = new RegExp(
    `(^|[^A-Za-z0-9_])${escapeRegExp(code)}(?![A-Za-z0-9])`
  )
  const mentionsCode = (...sources) =>
    collectStrings(sources).some((text) => pattern.test(text))

  const references = []
  survey.questionGroups?.forEach((group) => {
    if (mentionsCode(group.gRelevance, group.l10ns)) {
      references.push(
        group.l10ns?.[survey.language]?.groupName || `#${group.gid}`
      )
    }
    group.questions?.forEach((other) => {
      if (other.qid === question.qid) {
        return
      }
      const parts = [other, ...(other.subquestions ?? [])]
      const isReferenced = parts.some((part) =>
        mentionsCode(part.relevance, part.l10ns, part.attributes)
      )
      if (isReferenced) {
        references.push(other.title)
      }
    })
  })
  return references
}
