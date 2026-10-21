import 'tests/mocks'

import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { Toast } from 'helpers'
import { ResponsesTable } from './ResponsesTable'

jest.mock('helpers', () => ({
  ...jest.requireActual('helpers'),
  Toast: jest.fn(),
}))

// ResponsesTable pulls in ResponsesStatistics (via the components barrel),
// which imports recharts. recharts' d3-* dependencies ship ESM-only builds
// that Jest can't parse, so stub it out - this test never renders charts.
jest.mock('recharts', () => new Proxy({}, { get: () => () => null }))

const survey = {
  sid: 999,
  language: 'en',
  languages: ['en'],
  datestamp: false,
  questionGroups: [
    {
      questions: [
        {
          qid: 1,
          title: 'FU',
          questionThemeName: 'file_upload',
          l10ns: { en: { question: 'Upload a file' } },
        },
      ],
    },
  ],
}

const fileUploadAnswer = (files) => ({
  G01Q01: {
    key: 'G01Q01',
    qid: 1,
    sqid: null,
    actual_aid: null,
    value: JSON.stringify(files),
  },
})

const buildResponsesData = () => ({
  responses: [
    {
      id: 1,
      answers: fileUploadAnswer([
        {
          name: 'photo.png',
          size: 1200,
          title: '',
          comment: '',
          isDeleted: false,
        },
      ]),
    },
    {
      id: 2,
      answers: fileUploadAnswer([]),
    },
  ],
  surveyQuestions: { G01Q01: { qid: 1 } },
  timingFields: [],
  _meta: { pagination: { totalItems: 2 } },
})

const noop = () => {}
// Stable references: ResponsesTable resets the whole selection whenever
// `sorting`/`columnsFilters` change identity (as it would with real `useState`
// values in the parent), so tests that rerender must reuse the same arrays.
const stableSorting = []
const stableColumnsFilters = []

const renderResponsesTableElement = (responsesData) => (
  <ResponsesTable
    survey={survey}
    showFilters={false}
    sortedColumnId={null}
    sorting={stableSorting}
    setSorting={noop}
    responsesData={responsesData}
    pagination={{ pageIndex: 0, pageSize: 10 }}
    setPagination={noop}
    columnsFilters={stableColumnsFilters}
    setColumnsFilters={noop}
    hideActions={true}
  />
)

const renderResponsesTable = (responsesData = buildResponsesData()) =>
  render(renderResponsesTableElement(responsesData))

describe('ResponsesTable - bulk "Download files" action', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  beforeEach(() => {
    Toast.mockClear()
    window.open = jest.fn()
  })

  test('downloads every selected response after using the header "select all" checkbox', async () => {
    const user = userEvent.setup()
    renderResponsesTable()

    // checkboxes[0] is the header "select all" checkbox; it toggles every
    // row without ever clicking an individual row cell.
    const checkboxes = screen.getAllByRole('checkbox')
    await user.click(checkboxes[0])

    await user.click(screen.getByRole('button', { name: 'Download files' }))

    expect(Toast).not.toHaveBeenCalled()
    expect(window.open).toHaveBeenCalledWith(
      expect.stringContaining('responseIds=1,2')
    )
  })

  test('shows a message when none of the selected responses have files', async () => {
    const user = userEvent.setup()
    renderResponsesTable()

    // checkboxes[2] is the row checkbox for response id 2, which has no files.
    const checkboxes = screen.getAllByRole('checkbox')
    await user.click(checkboxes[2])

    await user.click(screen.getByRole('button', { name: 'Download files' }))

    expect(window.open).not.toHaveBeenCalled()
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({ message: 'No files to download!' })
    )
  })

  test('reconciles a selected response\'s hasFiles status when responsesData changes without reselecting it', async () => {
    const user = userEvent.setup()
    const { rerender } = renderResponsesTable()

    // Select response id 1, which currently has a file.
    const checkboxes = screen.getAllByRole('checkbox')
    await user.click(checkboxes[1])

    // Its file gets removed elsewhere (e.g. an attachment delete) and the
    // table receives fresh data for the still-selected response, without any
    // selection change happening.
    const updatedResponsesData = buildResponsesData()
    updatedResponsesData.responses[0].answers = fileUploadAnswer([])
    rerender(renderResponsesTableElement(updatedResponsesData))

    await user.click(screen.getByRole('button', { name: 'Download files' }))

    expect(window.open).not.toHaveBeenCalled()
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({ message: 'No files to download!' })
    )
  })
})
