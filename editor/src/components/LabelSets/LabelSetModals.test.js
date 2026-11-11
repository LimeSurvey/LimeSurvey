// Import shared mocks
import 'tests/mocks'

import { useState } from 'react'
import { act, screen, fireEvent, waitFor } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { Entities } from 'helpers'

import { LoadLabelSetModal } from './LoadLabelSetModal'
import { SaveLabelSetModal } from './SaveLabelSetModal'

const mockLabelSets = { current: [] }
const mockListError = { current: false }
const mockRefetch = jest.fn()
const mockGetLabelSet = jest.fn()
const mockSaveLabelSet = jest.fn()

jest.mock('hooks/useLabelSets', () => ({
  useLabelSets: () => ({
    labelSets: mockLabelSets.current,
    isFetching: false,
    isError: mockListError.current,
    refetch: mockRefetch,
  }),
  useLabelSetService: () => ({ getLabelSet: mockGetLabelSet }),
  useSaveLabelSet: () => ({
    saveLabelSet: mockSaveLabelSet,
    isSaving: false,
  }),
}))

const getConfirmButton = (text) => screen.getByRole('button', { name: text })

const openSelect = (testId) => {
  const input = screen.getByTestId(testId).querySelector('input')
  fireEvent.keyDown(input, { key: 'ArrowDown', keyCode: 40 })
}

beforeEach(() => {
  mockLabelSets.current = []
  mockListError.current = false
  mockRefetch.mockReset()
})

describe('LoadLabelSetModal', () => {
  beforeEach(() => {
    mockGetLabelSet.mockReset()
  })

  test('Shows an error with a retry when the list could not be loaded', async () => {
    mockListError.current = true
    await renderWithProviders(<LoadLabelSetModal show languages={['en']} />)

    expect(
      await screen.findByTestId('load-label-set-list-error')
    ).toBeInTheDocument()
    expect(screen.queryByTestId('load-label-set-empty')).not.toBeInTheDocument()
    expect(getConfirmButton('Load label set')).toBeDisabled()

    fireEvent.click(screen.getByRole('button', { name: 'Try again' }))
    expect(mockRefetch).toHaveBeenCalled()
  })

  test('Ignores a label set that arrives after the modal was closed', async () => {
    mockLabelSets.current = [{ lid: 5, name: 'Yes/No', languages: ['en'] }]
    let resolveLoad
    mockGetLabelSet.mockReturnValue(
      new Promise((resolve) => {
        resolveLoad = resolve
      })
    )
    const onLoad = jest.fn()

    const Harness = () => {
      const [show, setShow] = useState(true)
      return (
        <LoadLabelSetModal
          show={show}
          languages={['en']}
          onLoad={onLoad}
          onHide={() => setShow(false)}
        />
      )
    }
    await renderWithProviders(<Harness />)

    openSelect('load-label-set-select')
    fireEvent.click(await screen.findByText('Yes/No'))
    fireEvent.click(getConfirmButton('Load label set'))
    fireEvent.click(getConfirmButton('Cancel'))

    await act(async () => {
      resolveLoad({ lid: 5, labels: [{ code: 'A1', l10ns: { en: 'Yes' } }] })
    })

    expect(onLoad).not.toHaveBeenCalled()
  })

  test('Shows a message when no label set has the survey languages', async () => {
    mockLabelSets.current = []
    await renderWithProviders(
      <LoadLabelSetModal show languages={['en', 'de']} />
    )

    expect(
      await screen.findByTestId('load-label-set-empty')
    ).toBeInTheDocument()
    expect(getConfirmButton('Load label set')).toBeDisabled()
  })

  test('Loads the selected label set and passes its labels on', async () => {
    const labels = [{ code: 'A1', l10ns: { en: 'Yes' } }]
    mockLabelSets.current = [{ lid: 5, name: 'Yes/No', languages: ['en'] }]
    mockGetLabelSet.mockResolvedValue({ lid: 5, labels })
    const onLoad = jest.fn()
    const onHide = jest.fn()

    await renderWithProviders(
      <LoadLabelSetModal
        show
        languages={['en']}
        onLoad={onLoad}
        onHide={onHide}
      />
    )

    expect(getConfirmButton('Load label set')).toBeDisabled()
    openSelect('load-label-set-select')
    fireEvent.click(await screen.findByText('Yes/No'))
    expect(getConfirmButton('Load label set')).toBeEnabled()

    fireEvent.click(getConfirmButton('Load label set'))

    await waitFor(() => expect(onLoad).toHaveBeenCalledWith(labels))
    expect(mockGetLabelSet).toHaveBeenCalledWith(5)
    expect(onHide).toHaveBeenCalled()
  })
})

describe('SaveLabelSetModal', () => {
  const answers = [
    {
      code: 'A1',
      sortOrder: 0,
      assessmentValue: 0,
      l10ns: { en: { answer: 'Yes' }, de: { answer: 'Ja' } },
    },
  ]

  beforeEach(() => {
    mockSaveLabelSet.mockReset()
    mockSaveLabelSet.mockResolvedValue({ lid: 9 })
  })

  test('Saves a new label set with all survey languages by default', async () => {
    const onHide = jest.fn()
    await renderWithProviders(
      <SaveLabelSetModal
        show
        onHide={onHide}
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en', 'de']}
      />
    )

    expect(getConfirmButton('Save label set')).toBeDisabled()

    const nameInput = screen
      .getByTestId('save-label-set-modal')
      .querySelector('input[type="text"]')
    fireEvent.change(nameInput, { target: { value: 'My answers' } })
    expect(getConfirmButton('Save label set')).toBeEnabled()

    fireEvent.click(getConfirmButton('Save label set'))

    await waitFor(() =>
      expect(mockSaveLabelSet).toHaveBeenCalledWith({
        lid: undefined,
        name: 'My answers',
        languages: ['en', 'de'],
        labels: [
          { code: 'A1', assessmentValue: 0, l10ns: { en: 'Yes', de: 'Ja' } },
        ],
      })
    )
    expect(onHide).toHaveBeenCalled()
  })

  test('Overwrites the selected label set and hides the name field', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
      />
    )

    fireEvent.click(screen.getByText('Yes'))
    expect(screen.queryByTestId('save-label-set-name')).not.toBeInTheDocument()
    expect(getConfirmButton('Save label set')).toBeDisabled()

    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))
    expect(
      screen.getByTestId('save-label-set-overwrite-warning')
    ).toHaveTextContent(
      'You are about to replace an existing label set with the current answer options.'
    )
    expect(
      screen.queryByTestId('save-label-set-removed-languages')
    ).not.toBeInTheDocument()
    fireEvent.click(getConfirmButton('Save label set'))

    await waitFor(() =>
      expect(mockSaveLabelSet).toHaveBeenCalledWith(
        expect.objectContaining({ lid: 4, languages: ['en'] })
      )
    )
  })

  test('Warns about lost translations and assessment values on overwrite', async () => {
    mockLabelSets.current = [
      {
        lid: 4,
        name: 'Old set',
        languages: ['en', 'de', 'fr'],
        hasAssessmentValues: true,
      },
    ]
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
      />
    )

    fireEvent.click(screen.getByText('Yes'))
    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))

    const removedLanguages = screen.getByTestId(
      'save-label-set-removed-languages'
    )
    expect(removedLanguages).toHaveTextContent(
      'If you replace the label set these translations will be lost.'
    )
    expect(
      [...removedLanguages.querySelectorAll('li')].map((li) => li.textContent)
    ).toEqual(['de', 'fr'])
    expect(
      screen.getByTestId('save-label-set-assessment-warning')
    ).toHaveTextContent(
      'If you replace the label set the existing assessment values will be lost.'
    )
    // Like the classic editor, the overwrite is still allowed
    expect(getConfirmButton('Save label set')).toBeEnabled()
  })

  test('Shows an error with a retry when the overwrite list could not be loaded', async () => {
    mockListError.current = true
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
      />
    )

    fireEvent.click(screen.getByText('Yes'))

    expect(screen.getByTestId('save-label-set-list-error')).toBeInTheDocument()
    expect(
      screen.queryByTestId('save-label-set-overwrite-select')
    ).not.toBeInTheDocument()
    expect(getConfirmButton('Save label set')).toBeDisabled()
    fireEvent.click(screen.getByRole('button', { name: 'Try again' }))
    expect(mockRefetch).toHaveBeenCalled()
  })
})
