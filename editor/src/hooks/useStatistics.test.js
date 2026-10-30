import React from 'react'
import { renderHook, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import { useStatistics } from './useStatistics'

const mockGetSurveyStatistics = jest.fn()

jest.mock('./useAuth', () => ({
  __esModule: true,
  default: () => ({ restHeaders: {} }),
}))

jest.mock('./useAppState', () => ({
  useAppState: () => ['en'],
}))

jest.mock('services', () => ({
  StatisticsService: class {
    getSurveyStatistics = (...args) => mockGetSurveyStatistics(...args)
  },
}))

// A successful answer, in the shape the endpoint returns.
const charts = (title) => ({
  statistics: [{ title }],
  pagination: { page: 0, hasMore: false },
})

const wrapper = ({ children }) => {
  const client = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return <QueryClientProvider client={client}>{children}</QueryClientProvider>
}

const render = (sid) =>
  renderHook(({ sid, filterSet }) => useStatistics(sid, {}, filterSet), {
    wrapper,
    initialProps: { sid, filterSet: [] },
  })

const badFilter = [{ source: 'question', qid: 999 }]

describe('useStatistics', () => {
  beforeEach(() => {
    mockGetSurveyStatistics.mockReset()
  })

  it('flattens the pages into charts', async () => {
    mockGetSurveyStatistics.mockResolvedValue(charts('Age'))

    const { result } = render('100')

    await waitFor(() => expect(result.current.statistics).toHaveLength(1))
    expect(result.current.statistics[0].title).toBe('Age')
    expect(result.current.error).toBeNull()
  })

  it('sends the filter to the endpoint', async () => {
    mockGetSurveyStatistics.mockResolvedValue(charts('Age'))

    const { rerender } = render('100')
    await waitFor(() => expect(mockGetSurveyStatistics).toHaveBeenCalled())

    rerender({ sid: '100', filterSet: badFilter })

    // The filter is part of the query key, so changing it asks again.
    await waitFor(() =>
      expect(mockGetSurveyStatistics).toHaveBeenCalledTimes(2)
    )
    expect(mockGetSurveyStatistics).toHaveBeenLastCalledWith(
      '100',
      {},
      0,
      undefined,
      'en',
      badFilter
    )
  })

  // handleAxiosError resolves with the error rather than rejecting, and gives a
  // status of 0 when the request never reached the server.
  it.each([
    ['a rejected filter', 400],
    ['a server error', 500],
    ['a request that never arrived', 0],
  ])('treats %s as an error, not as charts', async (unused, httpStatus) => {
    mockGetSurveyStatistics.mockResolvedValue({
      httpStatus,
      code: 'ERROR',
      message: 'No good',
    })

    const { result } = render('100')

    await waitFor(() => expect(result.current.error).not.toBeNull())
    expect(result.current.error.httpStatus).toBe(httpStatus)
    expect(result.current.statistics).toEqual([])
  })

  it('keeps the charts it already had when a later request fails', async () => {
    mockGetSurveyStatistics.mockResolvedValue(charts('Age'))

    const { result, rerender } = render('100')
    await waitFor(() => expect(result.current.statistics).toHaveLength(1))

    mockGetSurveyStatistics.mockResolvedValue({
      httpStatus: 400,
      message: 'No good',
    })
    rerender({ sid: '100', filterSet: badFilter })

    await waitFor(() => expect(result.current.error).not.toBeNull())
    expect(result.current.statistics[0].title).toBe('Age')
  })

  // The hook stays mounted while the page moves between surveys, so the charts
  // held back for the error case have to belong to the survey being shown.
  it('does not fall back to another survey charts', async () => {
    mockGetSurveyStatistics.mockResolvedValue(charts('Age'))

    const { result, rerender } = render('100')
    await waitFor(() => expect(result.current.statistics).toHaveLength(1))

    mockGetSurveyStatistics.mockResolvedValue({
      httpStatus: 400,
      message: 'No good',
    })
    rerender({ sid: '200', filterSet: badFilter })

    await waitFor(() => expect(result.current.error).not.toBeNull())
    expect(result.current.statistics).toEqual([])
  })
})
