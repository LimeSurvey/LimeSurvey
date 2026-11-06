import { STATES } from 'helpers'
import { queryClient } from 'queryClient'

import { findFieldname } from './findFieldname'

describe('findFieldname', () => {
  afterEach(() => {
    queryClient.clear()
  })

  test('returns fieldname from the API map when available', () => {
    queryClient.setQueryData([STATES.SURVEY_QUESTIONS_FIELDNAME], {
      123: [{ qid: 123, fieldname: 'Q123' }],
    })

    expect(findFieldname({ qid: 123 })).toBe('Q123')
  })

  test('builds a fallback fieldname for an unsaved question', () => {
    expect(findFieldname({ qid: 'temp__question' })).toBe('Qtemp__question')
  })

  test('builds a fallback fieldname for an unsaved subquestion', () => {
    expect(
      findFieldname({
        qid: 'temp__question',
        sqid: 'temp__subquestion',
      })
    ).toBe('Qtemp__question_Stemp__subquestion')
  })

  test('builds a fallback fieldname for an unsaved dual-scale array question', () => {
    expect(
      findFieldname({
        qid: 'temp__question',
        sqid: 'temp__subquestion',
        aid: 'SQ001',
        scaleId: 1,
      })
    ).toBe('Qtemp__question_Stemp__subquestion#1')
  })

  test('builds a fallback fieldname for an unsaved array numbers/texts cell', () => {
    queryClient.setQueryData([STATES.SURVEY], {
      survey: {
        questionGroups: [
          {
            questions: [
              {
                qid: 'temp__question',
                subquestions: [
                  {
                    qid: 'temp__row',
                    title: 'SQ001',
                    scaleId: 0,
                  },
                  {
                    qid: 'temp__column',
                    title: 'SQ002',
                    scaleId: 1,
                  },
                ],
              },
            ],
          },
        ],
      },
    })

    expect(
      findFieldname({
        qid: 'temp__question',
        sqid: 'temp__row',
        aid: 'SQ001_SQ002',
      })
    ).toBe('Qtemp__question_Stemp__row_Stemp__column')
  })
})
