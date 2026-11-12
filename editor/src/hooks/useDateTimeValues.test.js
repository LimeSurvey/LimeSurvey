import React from 'react'
import { renderHook, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import { useDateTimeValues } from './useDateTimeValues'

const mockGet = jest.fn()

jest.mock('axios', () => ({
  __esModule: true,
  default: { create: () => ({ get: (...args) => mockGet(...args) }) },
}))

jest.mock('./useAuth', () => ({
  __esModule: true,
  default: () => ({ restHeaders: {} }),
}))

jest.mock('./useAppState', () => ({
  useAppState: () => ['de'],
}))

const wrapper = ({ children }) => (
  <QueryClientProvider client={new QueryClient()}>
    {children}
  </QueryClientProvider>
)

const render = () =>
  renderHook(() => useDateTimeValues('100', 5, {}), { wrapper })

describe('useDateTimeValues', () => {
  beforeEach(() => {
    mockGet.mockReset()
    jest.spyOn(console, 'error').mockImplementation(() => {})
  })

  afterEach(() => {
    jest.restoreAllMocks()
  })

  it('returns the values of a successful request', async () => {
    const values = { hasTime: false, total: 1, days: { '2026-11-05': {} } }
    mockGet.mockResolvedValue({ data: values })

    const { result } = render()

    await waitFor(() => expect(result.current.values).toEqual(values))
    expect(result.current.isError).toBe(false)
  })

  it('asks only for the days of a range', async () => {
    mockGet.mockResolvedValue({ data: { hasTime: false, total: 0, days: {} } })

    renderHook(
      () =>
        useDateTimeValues(
          '100',
          5,
          {},
          { from: '2026-01-14', to: '2026-01-16' }
        ),
      { wrapper }
    )

    await waitFor(() => expect(mockGet).toHaveBeenCalled())
    expect(mockGet.mock.calls[0][0]).toContain('&from=2026-01-14&to=2026-01-16')
  })

  it.each([
    ['no filters', {}, '?qid=5&language=de'],
    [
      'completed first',
      { completed: true, minId: 2 },
      '?qid=5&completed=true&minId=2&language=de',
    ],
    [
      'an ID range only',
      { minId: 2, maxId: 9 },
      '?qid=5&minId=2&maxId=9&language=de',
    ],
  ])(
    'joins the filters with single ampersands: %s',
    async (unused, filters, query) => {
      mockGet.mockResolvedValue({
        data: { hasTime: false, total: 0, days: {} },
      })

      renderHook(() => useDateTimeValues('100', 5, filters), { wrapper })

      await waitFor(() => expect(mockGet).toHaveBeenCalled())
      expect(mockGet.mock.calls[0][0]).toBe(
        `statistics-date-values/100${query}`
      )
    }
  )

  it('sends the active language, as the charts do', async () => {
    mockGet.mockResolvedValue({ data: { hasTime: false, total: 0, days: {} } })

    render()

    await waitFor(() => expect(mockGet).toHaveBeenCalled())
    expect(mockGet.mock.calls[0][0]).toContain('&language=de')
  })

  it.each([
    ['is forbidden', { response: { status: 403, data: {} } }],
    ['fails on the server', { response: { status: 500, data: {} } }],
    ['gets no response', { request: {} }],
  ])('reports an error when the request %s', async (unused, failure) => {
    mockGet.mockRejectedValue(Object.assign(new Error('failed'), failure))

    const { result } = render()

    await waitFor(() => expect(result.current.isError).toBe(true))
    expect(result.current.values).toBeUndefined()
    expect(mockGet).toHaveBeenCalledTimes(1)
  })
})
