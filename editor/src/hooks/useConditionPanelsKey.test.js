import { renderHook } from '@testing-library/react'

import { clearResolvedTempIds, rememberResolvedTempIds } from 'helpers'

import { useConditionPanelsKey } from './useConditionPanelsKey'

describe('useConditionPanelsKey', () => {
  afterEach(() => clearResolvedTempIds())

  test('keeps the key when the temp ID is replaced with its real ID', () => {
    const onQidResolved = jest.fn()
    const { result, rerender } = renderHook(
      ({ qid }) => useConditionPanelsKey(qid, onQidResolved),
      { initialProps: { qid: 'temp__12' } }
    )

    rememberResolvedTempIds([{ tempId: 'temp__12', id: 345 }])
    rerender({ qid: 345 })

    expect(result.current).toBe('temp__12')
    expect(onQidResolved).toHaveBeenCalledTimes(1)
  })

  test('changes the key when the temp ID was resolved earlier, for another question', () => {
    // temp__12 was already resolved to 345 before this unsaved question drew
    // the same temp ID: Focusing 345 is a switch to that other question.
    rememberResolvedTempIds([{ tempId: 'temp__12', id: 345 }])
    const onQidResolved = jest.fn()
    const { result, rerender } = renderHook(
      ({ qid }) => useConditionPanelsKey(qid, onQidResolved),
      { initialProps: { qid: 'temp__12' } }
    )

    rerender({ qid: 345 })

    expect(result.current).toBe(345)
    expect(onQidResolved).not.toHaveBeenCalled()
  })

  test('changes the key when another question is focused', () => {
    const onQidResolved = jest.fn()
    const { result, rerender } = renderHook(
      ({ qid }) => useConditionPanelsKey(qid, onQidResolved),
      { initialProps: { qid: 'temp__12' } }
    )

    rerender({ qid: 400 })

    expect(result.current).toBe(400)
    expect(onQidResolved).not.toHaveBeenCalled()
  })
})
