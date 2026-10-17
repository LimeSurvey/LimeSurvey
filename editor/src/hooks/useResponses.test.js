import React from 'react'
import { renderHook, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import { useResponses } from './useResponses'

const mockGetSurveyResponses = jest.fn()

jest.mock('./useAuth', () => ({
  __esModule: true,
  default: () => ({ restHeaders: {} }),
}))

jest.mock('services', () => ({
  ResponseService: class {
    getSurveyResponses = (...args) => mockGetSurveyResponses(...args)
    patchResponses = jest.fn()
  },
}))

jest.mock('queryClient', () => ({
  queryClient: { invalidateQueries: jest.fn() },
}))

const pagination = { pageIndex: 0, pageSize: 10 }

// A successful answer, in the shape the endpoint returns.
const rows = (id) => ({ responses: [{ id }], surveyQuestions: [], _meta: {} })

const wrapper = ({ children }) => {
  const client = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return <QueryClientProvider client={client}>{children}</QueryClientProvider>
}

const render = (sid) =>
  renderHook(
    ({ sid, filterSet }) => useResponses(sid, pagination, {}, [], filterSet),
    { wrapper, initialProps: { sid, filterSet: [] } }
  )

// The filter the user applies is part of the query key, so changing it is what
// sends the next request — the same path a rejected filter takes.
const badFilter = [{ source: 'question', qid: 999 }]

describe('useResponses', () => {
  beforeEach(() => {
    mockGetSurveyResponses.mockReset()
  })

  it('reads a normal answer as responses', async () => {
    mockGetSurveyResponses.mockResolvedValue(rows(1))

    const { result } = render('100')

    await waitFor(() => expect(result.current.responses).toEqual(rows(1)))
    expect(result.current.error).toBeNull()
  })

  // handleAxiosError resolves with the error rather than rejecting, and gives a
  // status of 0 when the request never reached the server. Read for truth that
  // zero is falsy, and the error object was handed to the table as data.
  it.each([
    ['a rejected filter', 400],
    ['a server error', 500],
    ['a request that never arrived', 0],
  ])('treats %s as an error, not as responses', async (unused, httpStatus) => {
    mockGetSurveyResponses.mockResolvedValue({
      httpStatus,
      code: 'ERROR',
      message: 'No good',
    })

    const { result } = render('100')

    await waitFor(() => expect(result.current.error).not.toBeNull())
    expect(result.current.error.httpStatus).toBe(httpStatus)
    expect(result.current.responses).toBeUndefined()
  })

  it('keeps the rows it already had when a later request fails', async () => {
    mockGetSurveyResponses.mockResolvedValue(rows(1))

    const { result, rerender } = render('100')
    await waitFor(() => expect(result.current.responses).toEqual(rows(1)))

    mockGetSurveyResponses.mockResolvedValue({
      httpStatus: 400,
      message: 'No good',
    })
    rerender({ sid: '100', filterSet: badFilter })

    await waitFor(() => expect(result.current.error).not.toBeNull())
    expect(result.current.responses).toEqual(rows(1))
  })

  // The hook stays mounted while the page moves between surveys, so the rows
  // held back for the error case have to belong to the survey being shown.
  it('does not fall back to another survey rows', async () => {
    mockGetSurveyResponses.mockResolvedValue(rows(1))

    const { result, rerender } = render('100')
    await waitFor(() => expect(result.current.responses).toEqual(rows(1)))

    mockGetSurveyResponses.mockResolvedValue({
      httpStatus: 400,
      message: 'No good',
    })
    rerender({ sid: '200', filterSet: badFilter })

    await waitFor(() => expect(result.current.error).not.toBeNull())
    expect(result.current.responses).toBeUndefined()
  })
})
