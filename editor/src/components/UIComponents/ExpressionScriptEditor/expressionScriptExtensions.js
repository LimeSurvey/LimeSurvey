import {
  Decoration,
  EditorView,
  hoverTooltip,
  ViewPlugin,
} from '@codemirror/view'

const KEYWORDS = new Set(['and', 'or', 'not', 'true', 'false', 'null'])
const OPENING_BRACKETS = new Set(['(', '[', '{'])
const CLOSING_BRACKETS = new Map([
  [')', '('],
  [']', '['],
  ['}', '{'],
])

const isIdentifierStart = (character) => /[A-Za-z_$]/.test(character)
const isIdentifierPart = (character) => /[A-Za-z0-9_$.:]/.test(character)

export const tokenizeExpressionScript = (content) => {
  const tokens = []
  const brackets = []
  let index = 0

  const addToken = (from, to, type, message = '') => {
    const token = { from, to, type, message }
    tokens.push(token)
    return token
  }

  while (index < content.length) {
    const character = content[index]

    if (/\s/.test(character)) {
      index += 1
      continue
    }

    if (character === '"' || character === "'") {
      const from = index
      const quote = character
      let escaped = false
      let isClosed = false
      index += 1

      while (index < content.length) {
        const current = content[index]
        index += 1
        if (escaped) {
          escaped = false
        } else if (current === '\\') {
          escaped = true
        } else if (current === quote) {
          isClosed = true
          break
        }
      }

      addToken(
        from,
        index,
        isClosed ? 'string' : 'error',
        isClosed ? '' : 'Unterminated string'
      )
      continue
    }

    const sgqaIdentifier = content
      .slice(index)
      .match(/^\d+X\d+X[A-Za-z0-9_$.:]+/)?.[0]
    if (sgqaIdentifier) {
      addToken(index, index + sgqaIdentifier.length, 'variable')
      index += sgqaIdentifier.length
      continue
    }

    if (/\d/.test(character)) {
      const from = index
      while (index < content.length && /[\d.]/.test(content[index])) index += 1
      addToken(from, index, 'number')
      continue
    }

    const operator = content
      .slice(index)
      .match(/^(===|!==|==|!=|<=|>=|&&|\|\||\+|-|\*|\/|%|<|>|=|!)/)?.[0]
    if (operator) {
      addToken(index, index + operator.length, 'operator')
      index += operator.length
      continue
    }

    if (OPENING_BRACKETS.has(character)) {
      const token = addToken(index, index + 1, 'bracket')
      brackets.push({ character, token })
      index += 1
      continue
    }

    if (CLOSING_BRACKETS.has(character)) {
      const opening = brackets.pop()
      if (!opening || opening.character !== CLOSING_BRACKETS.get(character)) {
        if (opening) brackets.push(opening)
        addToken(index, index + 1, 'error', 'Unmatched closing bracket')
      } else {
        addToken(index, index + 1, 'bracket')
      }
      index += 1
      continue
    }

    if (isIdentifierStart(character)) {
      const from = index
      while (index < content.length && isIdentifierPart(content[index]))
        index += 1

      const value = content.slice(from, index)
      let nextIndex = index
      while (nextIndex < content.length && /\s/.test(content[nextIndex])) {
        nextIndex += 1
      }

      const normalizedValue = value.toLowerCase()
      const type = KEYWORDS.has(normalizedValue)
        ? 'keyword'
        : content[nextIndex] === '('
          ? 'function'
          : 'variable'
      addToken(from, index, type)
      continue
    }

    addToken(index, index + 1, 'punctuation')
    index += 1
  }

  brackets.forEach(({ token }) => {
    token.type = 'error'
    token.message = 'Unmatched opening bracket'
  })

  return tokens
}

const tokenClassNames = {
  bracket: 'cm-expression-bracket',
  error: 'cm-expression-error',
  function: 'cm-expression-function',
  keyword: 'cm-expression-keyword',
  number: 'cm-expression-number',
  operator: 'cm-expression-operator',
  punctuation: 'cm-expression-punctuation',
  string: 'cm-expression-string',
  variable: 'cm-expression-variable',
}

const buildDecorations = (view) => {
  const decorations = []
  const content = view.state.doc.toString()

  tokenizeExpressionScript(content).forEach(({ from, to, type }) => {
    decorations.push(
      Decoration.mark({
        class: tokenClassNames[type],
      }).range(from, to)
    )
  })

  return Decoration.set(decorations, true)
}

export const expressionScriptHighlighting = ViewPlugin.fromClass(
  class {
    constructor(view) {
      this.decorations = buildDecorations(view)
    }

    update(update) {
      if (update.docChanged || update.viewportChanged) {
        this.decorations = buildDecorations(update.view)
      }
    }
  },
  { decorations: (value) => value.decorations }
)

export const expressionScriptTheme = EditorView.theme({
  '.cm-expression-function': { color: '#0000ff', fontWeight: '600' },
  '.cm-expression-string': { color: '#6e748c' },
  '.cm-expression-variable': { color: '#4169e1' },
  '.cm-expression-keyword': { color: '#7b2cbf', fontWeight: '600' },
  '.cm-expression-number': { color: '#a0522d' },
  '.cm-expression-operator': { color: '#1e1e1e', fontWeight: '600' },
  '.cm-expression-bracket, .cm-expression-punctuation': { color: '#1e1e1e' },
  '.cm-expression-error': {
    color: '#dc3545',
    fontWeight: '700',
    textDecoration: 'underline wavy #dc3545',
    textUnderlineOffset: '3px',
  },
  '.cm-expression-semantic-error': {
    textDecoration: 'underline wavy #dc3545',
    textDecorationThickness: '1.5px',
    textUnderlineOffset: '3px',
  },
  '.cm-expression-semantic-warning': {
    textDecoration: 'underline wavy #b58105',
    textDecorationThickness: '1.5px',
    textUnderlineOffset: '3px',
  },
  '.cm-tooltip.cm-tooltip-hover': {
    backgroundColor: '#1e1e1e',
    border: 'none',
    borderRadius: '3px',
    color: '#ffffff',
  },
  '.cm-tooltip-hover .cm-expression-tooltip': {
    fontSize: '12px',
    maxWidth: '260px',
    padding: '8px 12px',
  },
})

const normalizeDiagnostic = ({ from, to, severity, message }, docLength) => {
  const safeFrom = Math.max(0, Math.min(Number(from) || 0, docLength - 1))
  const safeTo = Math.max(
    safeFrom + 1,
    Math.min(Number(to) || safeFrom + 1, docLength)
  )

  return { from: safeFrom, to: safeTo, severity, message }
}

export const expressionScriptDiagnostics = (
  diagnostics = [],
  docLength = 0
) => {
  if (docLength === 0 || diagnostics.length === 0) return []

  const decorations = diagnostics.map((diagnostic) => {
    const { from, to, severity } = normalizeDiagnostic(diagnostic, docLength)
    return Decoration.mark({
      class:
        severity === 'warning'
          ? 'cm-expression-semantic-warning'
          : 'cm-expression-semantic-error',
    }).range(from, to)
  })

  return [EditorView.decorations.of(Decoration.set(decorations, true))]
}

export const findExpressionScriptIssue = (
  content,
  diagnostics,
  position,
  side
) => {
  if (!content) return null

  const issues = [
    ...tokenizeExpressionScript(content).filter(({ message }) => message),
    ...diagnostics
      .filter(({ message }) => message)
      .map((diagnostic) => normalizeDiagnostic(diagnostic, content.length)),
  ]

  return (
    issues.find(
      ({ from, to }) =>
        (from < position || (from === position && side > 0)) &&
        (to > position || (to === position && side < 0))
    ) ?? null
  )
}

export const expressionScriptTooltips = (diagnostics = []) =>
  hoverTooltip(
    (view, position, side) => {
      const issue = findExpressionScriptIssue(
        view.state.doc.toString(),
        diagnostics,
        position,
        side
      )

      if (!issue) return null

      return {
        pos: issue.from,
        end: issue.to,
        above: true,
        create: () => {
          const dom = document.createElement('div')
          dom.className = 'cm-expression-tooltip'
          dom.setAttribute('role', 'tooltip')
          dom.textContent = issue.message
          return { dom }
        },
      }
    },
    { hideOnChange: true }
  )

export const expressionScriptExtensions = [
  expressionScriptHighlighting,
  expressionScriptTheme,
]
