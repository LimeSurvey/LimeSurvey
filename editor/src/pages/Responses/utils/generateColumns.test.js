import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { getSiteUrl } from 'helpers'
import { generateColumns, getInitialColumnVisibility } from './generateColumns'

describe('generateColumns', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  test('adds columns from timing field metadata', () => {
    const timingFields = [
      {
        fieldname: 'interviewtime',
        type: 'interview_time',
        question: 'Total time (in s)',
      },
      {
        fieldname: 'G12time',
        type: 'page_time',
        question: 'Group time: Demographics',
      },
      {
        fieldname: 'Q34time',
        qid: 34,
        type: 'answer_time',
        question: 'Question time: AGE',
      },
    ]

    const columns = generateColumns(
      { responseField: { qid: 34 } },
      {
        sid: 123,
        language: 'en',
        languages: [],
        datestamp: true,
        questionGroups: [
          {
            questions: [
              {
                qid: 34,
                title: 'AGE',
                l10ns: { en: { question: 'How old are you?' } },
              },
            ],
          },
        ],
      },
      timingFields
    )

    expect(columns).toEqual(
      expect.arrayContaining([
        expect.objectContaining({
          id: 'interviewtime',
          header: 'Total time (in s)',
          enableSorting: false,
          meta: expect.objectContaining({
            columnCategory: 'timing',
            visibleByDefault: false,
          }),
        }),
        expect.objectContaining({
          id: 'G12time',
          header: 'Group time: Demographics',
        }),
        expect.objectContaining({
          id: 'Q34time',
          header: 'How old are you?',
          meta: expect.objectContaining({
            qid: 34,
            title: 'Question time: AGE',
            questionLabel: {
              code: 'AGE',
              text: 'How old are you?',
            },
          }),
        }),
        expect.objectContaining({
          id: '34',
          header: 'How old are you?',
          meta: expect.objectContaining({
            questionLabel: {
              code: 'AGE',
              text: 'How old are you?',
            },
          }),
        }),
      ])
    )

    expect(getInitialColumnVisibility(columns)).toEqual({
      interviewtime: false,
      G12time: false,
      Q34time: false,
    })
  })

  test('ignores timing metadata without a field name', () => {
    const columns = generateColumns(
      {},
      { sid: 123, languages: [], questionGroups: [] },
      [{ type: 'interview_time', question: 'Total time' }]
    )

    expect(columns.some((column) => column.meta?.timingType)).toBe(false)
  })

  test('uses the question code when the question text is empty', () => {
    const columns = generateColumns(
      { responseField: { qid: 34 } },
      {
        sid: 123,
        language: 'en',
        languages: [],
        questionGroups: [
          {
            questions: [
              {
                qid: 34,
                title: 'Q00',
                l10ns: { en: { question: '' } },
              },
            ],
          },
        ],
      }
    )

    expect(columns).toEqual(
      expect.arrayContaining([
        expect.objectContaining({
          id: '34',
          header: 'Q00',
          meta: expect.objectContaining({
            questionLabel: { code: 'Q00', text: '' },
          }),
        }),
      ])
    )
  })

  test('links the quota exit ID and shows its name on hover', async () => {
    const quotaColumn = generateColumns(
      {},
      { sid: 123, saveQuotaExit: true }
    ).find(({ id }) => id === 'quotaExit')
    const QuotaCell = quotaColumn.cell

    render(
      <QuotaCell
        getValue={() => 42}
        row={{ original: { quotaExitName: 'Young respondents' } }}
      />
    )

    const link = screen.getByRole('link', { name: '42' })
    expect(link).toHaveAttribute(
      'href',
      getSiteUrl('/quotas/editQuota/surveyid/123?quota_id=42')
    )
    expect(link).toHaveAttribute('target', '_blank')

    fireEvent.mouseOver(link)
    expect(await screen.findByRole('tooltip')).toHaveTextContent(
      'Young respondents'
    )
  })
})
