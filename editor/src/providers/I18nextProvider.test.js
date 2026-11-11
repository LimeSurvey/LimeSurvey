import { render, screen, waitFor } from '@testing-library/react'

import { I18Provider } from './I18nextProvider'

const mockGetUserDetail = jest.fn()
const mockSetUserDetail = jest.fn()

jest.mock('hooks', () => ({
  useAuth: () => ({ isLoggedIn: true, userId: 1, token: 'token' }),
  useAppState: (key) =>
    key === 'userDetail' ? [{}, mockSetUserDetail] : [[], jest.fn()],
  useUserService: () => ({ getUserDetail: mockGetUserDetail }),
}))

jest.mock('../helpers', () => ({
  STATES: {
    USER_DETAIL: 'userDetail',
    ALL_AVAILABLE_LANGUAGES: 'allLanguages',
  },
  loadDayjsLocale: jest.fn(async () => 'en'),
}))

// auth, setLanguages and languages, passed through to the i18n factory
const extraArgs = [expect.anything(), expect.anything(), expect.anything()]

const renderProvider = () => {
  const i18n = jest.fn(() => ({}))
  render(
    <I18Provider i18n={i18n}>
      <div>editor content</div>
    </I18Provider>
  )
  return i18n
}

describe('I18Provider', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  test('uses the language of the user', async () => {
    mockGetUserDetail.mockResolvedValue({ lang: 'de' })
    const i18n = renderProvider()

    await waitFor(() => expect(i18n).toHaveBeenCalledWith('de', ...extraArgs))
    expect(mockSetUserDetail).toHaveBeenCalledWith({ lang: 'de' })
  })

  test.each([
    ['the request throws', () => Promise.reject(new Error('network'))],
    ['the request returns nothing', () => Promise.resolve(undefined)],
    [
      'the request returns an error object',
      () => Promise.resolve({ code: 'SERVER_ERROR', httpStatus: 500 }),
    ],
  ])('falls back to English when %s', async (_, response) => {
    mockGetUserDetail.mockImplementation(response)
    const i18n = renderProvider()

    await waitFor(() => expect(i18n).toHaveBeenCalledWith('en', ...extraArgs))
  })

  test('renders the editor while the user language is still loading', () => {
    mockGetUserDetail.mockImplementation(() => new Promise(() => {}))
    renderProvider()

    expect(screen.getByText('editor content')).toBeInTheDocument()
  })
})
