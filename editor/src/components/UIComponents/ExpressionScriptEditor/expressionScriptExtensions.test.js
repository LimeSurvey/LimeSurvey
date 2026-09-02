import {
  expressionScriptDiagnostics,
  tokenizeExpressionScript,
} from './expressionScriptExtensions'

describe('Expression Script highlighting', () => {
  it('recognises the main Expression Script token types', () => {
    const tokens = tokenizeExpressionScript(
      'if(Q01.NAOK >= 2 and is_empty("answer"), true, false)'
    )

    expect(tokens.map(({ type }) => type)).toEqual(
      expect.arrayContaining([
        'function',
        'variable',
        'operator',
        'number',
        'keyword',
        'string',
        'bracket',
        'punctuation',
      ])
    )
  })

  it('marks unmatched brackets as errors', () => {
    const tokens = tokenizeExpressionScript('(Q01 == 1')

    expect(tokens[0]).toMatchObject({
      type: 'error',
      message: 'Unmatched opening bracket',
    })
  })

  it('marks unterminated strings as errors', () => {
    const tokens = tokenizeExpressionScript("Q01 == 'answer")

    expect(tokens.at(-1)).toMatchObject({
      type: 'error',
      message: 'Unterminated string',
    })
  })

  it('treats legacy SGQA references as one variable token', () => {
    const tokens = tokenizeExpressionScript('114397X103X1138SQ002.NAOK == "Y"')

    expect(tokens[0]).toMatchObject({
      type: 'variable',
      from: 0,
      to: 25,
    })
  })

  it('creates a semantic error decoration from server diagnostics', () => {
    const extensions = expressionScriptDiagnostics(
      [
        {
          from: 0,
          to: 25,
          severity: 'error',
          message: 'Undefined function',
        },
      ],
      28
    )

    expect(extensions).toHaveLength(1)
  })
})
