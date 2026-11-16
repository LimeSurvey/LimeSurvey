export const makeExpressionReadable = (content) => {
  // Step 1: Normalize whitespace
  let cleaned = content.replace(/\s+/g, ' ').trim()

  // Step 2: Protect function calls (regexMatch, is_empty, etc.)
  const functionRegex = /\b(?:regexMatch|is_empty)\s*\([^()]*\)/g
  const protectedFns = []
  let protectedContent = cleaned.replace(functionRegex, (match) => {
    const token = `__FN_${protectedFns.length}__`
    protectedFns.push(match)
    return token
  })

  // Step 3: Tokenize by splitting on (, ), and/or
  let tokens = protectedContent
    .replace(/(\()|(\))|(?<!["'])\b(?:and|or)\b(?!["'])/g, (match, p1, p2) => {
      if (p1) return '\n(\n'
      if (p2) return '\n)\n'
      // match is either "and" or "or" here
      return `\n${match}\n`
    })
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)

  // Step 4: Merge short inner expressions inside ( ... )
  let merged = []
  for (let i = 0; i < tokens.length; i++) {
    if (tokens[i] === '(') {
      let j = i + 1
      let inner = ''
      let parenDepth = 1
      while (j < tokens.length && parenDepth > 0) {
        if (tokens[j] === '(') parenDepth++
        else if (tokens[j] === ')') parenDepth--

        if (parenDepth > 0) {
          inner += tokens[j] + ' '
        }
        j++
      }

      if (parenDepth === 0 && inner.trim().length < 80) {
        merged.push(`(${inner.trim()})`)
        i = j - 1
      } else {
        merged.push(tokens[i])
      }
    } else {
      merged.push(tokens[i])
    }
  }

  // Step 5: Indentation
  let indent = 0
  for (let i = 0; i < merged.length; i++) {
    const line = merged[i]
    if (line === ')') indent--

    if (line !== '') {
      merged[i] = '  '.repeat(Math.max(indent, 0)) + line
    }

    if (line === '(') indent++
  }

  // Step 6: Restore protected function
  let result = merged.join('\n')
  protectedFns.forEach((fn, idx) => {
    result = result.replace(`__FN_${idx}__`, fn)
  })

  return result
}

// clean the readable expression script to back to the normal format
export const clean = (str) => {
  return (
    str
      // 1) remove newlines (from pretty formatting)
      .replace(/\s*\n+\s*/g, ' ')
      // 2) collapse multi-spaces
      .replace(/\s+/g, ' ')
      // 3) normalize spaces around and|or when NOT inside quotes
      .replace(/(?<!["'])\s*\b(and|or)\b\s*(?!["'])/g, ' $1 ')
      // 4) strip spaces touching parentheses
      .replace(/\(\s+\(/g, '((') // ((
      .replace(/\)\s+\)/g, '))') // ))
      .replace(/\(\s+/g, '(') // after "("
      .replace(/\s+\)/g, ')') // before ")"
      .replace(/\)\s+\(/g, ')(') // ") ("
      // 5) final tidy
      .replace(/\s+/g, ' ')
      .trim()
  )
}
