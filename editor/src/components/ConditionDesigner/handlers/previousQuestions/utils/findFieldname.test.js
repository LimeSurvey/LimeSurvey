import { STATES } from 'helpers'
import { queryClient } from 'queryClient'

import { findFieldname } from './findFieldname'

describe('findFieldname', () => {
  beforeEach(() => {
    queryClient.setQueryData([STATES.SURVEY_QUESTIONS_FIELDNAME], {
      5: [{ qid: 5, sqid: 6, aid: 'SQ001', fieldname: 'Q5_S6' }],
    })
  })

  test('uses the fieldname from the server', () => {
    expect(findFieldname({ qid: 5, sqid: 6, aid: 'SQ001' })).toBe('Q5_S6')
  })

  test('builds the fieldname of a question with a temp ID', () => {
    expect(findFieldname({ qid: 'temp__1' })).toBe('Qtemp__1')
  })

  test('builds the fieldname of a new subquestion of a saved question', () => {
    expect(findFieldname({ qid: 5, sqid: 'temp__2', aid: 'SQ002' })).toBe(
      'Q5_Stemp__2'
    )
  })

  test('builds dual scale and array (numbers) fieldnames', () => {
    expect(findFieldname({ qid: 'temp__1', sqid: 'temp__2', scaleId: 1 })).toBe(
      'Qtemp__1_Stemp__2#1'
    )
    expect(
      findFieldname({ qid: 'temp__1', sqid: 'temp__2', xSqid: 'temp__3' })
    ).toBe('Qtemp__1_Stemp__2_Stemp__3')
  })

  test('a saved question without such a field has no fieldname', () => {
    expect(findFieldname({ qid: 5, aid: 'SQ009' })).toBeNull()
  })

  test('builds the fieldname of a subquestion saved after the map was loaded', () => {
    expect(findFieldname({ qid: 5, sqid: 9, aid: 'SQ002' })).toBe('Q5_S9')
  })

  test('builds the fieldname of a question saved after the map was loaded', () => {
    expect(findFieldname({ qid: 8 })).toBe('Q8')
  })

  test('builds no question fieldname for an array, only subquestion ones', () => {
    expect(
      findFieldname({ qid: 'temp__1', hasQuestionField: false })
    ).toBeNull()
    expect(
      findFieldname({
        qid: 'temp__1',
        sqid: 'temp__2',
        hasQuestionField: false,
      })
    ).toBe('Qtemp__1_Stemp__2')
  })
})
