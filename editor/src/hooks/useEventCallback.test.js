import { renderHook } from '@testing-library/react'

import { useEventCallback } from './useEventCallback'

describe('useEventCallback', () => {
  test('keeps the same function across renders', () => {
    const { result, rerender } = renderHook(
      ({ callback }) => useEventCallback(callback),
      { initialProps: { callback: () => 1 } }
    )
    const first = result.current

    rerender({ callback: () => 2 })

    expect(result.current).toBe(first)
  })

  test('calls the latest callback with the given arguments', () => {
    const firstCallback = jest.fn()
    const latestCallback = jest.fn(() => 'latest')
    const { result, rerender } = renderHook(
      ({ callback }) => useEventCallback(callback),
      { initialProps: { callback: firstCallback } }
    )

    rerender({ callback: latestCallback })

    expect(result.current('a', 2)).toBe('latest')
    expect(latestCallback).toHaveBeenCalledWith('a', 2)
    expect(firstCallback).not.toHaveBeenCalled()
  })
})
