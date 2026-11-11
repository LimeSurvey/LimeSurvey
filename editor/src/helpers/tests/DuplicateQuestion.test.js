import { DuplicateQuestion, hasCopiedConditions } from '../DuplicateQuestion'
import { OperationsBuffer } from '../Buffer/index.js'
import { createBufferOperation } from '../Buffer/index.js'

describe('DuplicateQuestion conditions', () => {
  const original = {
    qid: 10,
    gid: 1,
    title: 'Q10',
    type: 'T',
    relevance: '((Q3.NAOK == "A1"))',
    l10ns: { en: { question: 'Question', help: '' } },
    scenarios: [{ scid: 1, conditions: [{ cid: 77, qid: 10, cqid: 3 }] }],
  }

  test('flags the conditions copied from the original', () => {
    const duplicate = DuplicateQuestion(original)

    expect(hasCopiedConditions(duplicate)).toBe(true)
    // They keep the cid the server copies them by.
    expect(duplicate.scenarios[0].conditions[0].cid).toBe(77)
    expect(hasCopiedConditions(original)).toBe(false)
    expect(hasCopiedConditions({ qid: 11 })).toBe(false)
  })

  test('the flag is kept in the buffered create of the duplicate', () => {
    const duplicate = DuplicateQuestion(original)
    const buffer = new OperationsBuffer()
    buffer.addOperation(
      createBufferOperation(duplicate.qid)
        .question()
        .create({ question: duplicate, questionL10n: duplicate.l10ns })
    )

    const [operation] = buffer.getOperations()
    expect(hasCopiedConditions(operation.props.question)).toBe(true)
  })
})
