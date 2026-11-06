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
