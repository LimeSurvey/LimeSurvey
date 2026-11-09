import { renderHook } from '@testing-library/react'

import { useExpressionScriptValidation } from './useExpressionScriptValidation'

const mockValidate = jest.fn()

jest.mock('./useAuth', () => ({
  __esModule: true,
  default: () => ({ restHeaders: {} }),
}))

jest.mock('services', () => ({
  ExpressionScriptValidationService: jest.fn().mockImplementation(() => ({
    validate: mockValidate,
  })),
}))

describe('useExpressionScriptValidation', () => {
  beforeEach(() => mockValidate.mockReset())

  test('validates the script of a saved question on the server', async () => {
    mockValidate.mockResolvedValue({ valid: false, diagnostics: ['error'] })
    const { result } = renderHook(() => useExpressionScriptValidation(1, 345))

    await expect(result.current('Q1 == "A1"')).resolves.toEqual({
      valid: false,
      diagnostics: ['error'],
    })
    expect(mockValidate).toHaveBeenCalledWith('Q1 == "A1"', 345, undefined)
  })

  test('does not ask the server about an unsaved question', async () => {
    const { result } = renderHook(() =>
      useExpressionScriptValidation(1, 'temp__123')
    )

    await expect(result.current('Q1 == "A1"')).resolves.toEqual({
      valid: true,
      diagnostics: [],
    })
    expect(mockValidate).not.toHaveBeenCalled()
  })
})
