// Import shared mocks
import 'tests/mocks'

import { useState } from 'react'
import { act, screen, fireEvent, waitFor, within } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { Entities } from 'helpers'

import { LoadLabelSetModal } from './LoadLabelSetModal'
import { SaveLabelSetModal } from './SaveLabelSetModal'

const mockLabelSets = { current: [] }
const mockListError = { current: false }
const mockListFetching = { current: false }
const mockRefetch = jest.fn()
// Label sets as returned by the detail request, keyed by lid; by default
// derived from the list, see mockGetDetail
const mockDetails = { current: {} }
const mockDetailError = { current: false }
const mockDetailFetching = { current: false }
const mockRefetchDetail = jest.fn()
const mockGetLabelSet = jest.fn()
const mockSaveLabelSet = jest.fn()

const mockGetDetail = (lid) => {
  if (mockDetails.current[lid]) {
    return mockDetails.current[lid]
  }
  const listed = mockLabelSets.current.find((labelSet) => labelSet.lid === lid)
  return (
    listed && {
      lid,
      name: listed.name,
      languages: listed.languages,
      labels: listed.hasAssessmentValues ? [{ assessmentValue: 1 }] : [],
      revision: `revision-${lid}`,
    }
  )
}

jest.mock('hooks/useLabelSets', () => ({
  LABEL_SET_CONFLICT: 'LABEL_SET_CONFLICT',
  useLabelSets: () => ({
    labelSets: mockLabelSets.current,
    isFetching: mockListFetching.current,
    isError: mockListError.current,
    refetch: mockRefetch,
  }),
  useLabelSet: ({ lid }) => ({
    labelSet: mockDetailError.current || !lid ? undefined : mockGetDetail(lid),
    isFetching: mockDetailFetching.current,
    isError: mockDetailError.current,
    refetch: mockRefetchDetail,
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
  mockListFetching.current = false
  mockRefetch.mockReset()
  mockDetails.current = {}
  mockDetailError.current = false
  mockDetailFetching.current = false
  mockRefetchDetail.mockReset()
})

// Renders the modal again on demand, so changed list mocks take effect
const RerenderHarness = (props) => {
  const [, setRenderCount] = useState(0)
  return (
    <>
      <button
        type="button"
        onClick={() => setRenderCount((count) => count + 1)}
      >
        Rerender
      </button>
      <SaveLabelSetModal {...props} />
    </>
  )
}
const rerender = () => fireEvent.click(screen.getByText('Rerender'))

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

  test('Ignores a label set that arrives after the modal was unmounted', async () => {
    mockLabelSets.current = [{ lid: 5, name: 'Yes/No', languages: ['en'] }]
    let resolveLoad
    mockGetLabelSet.mockReturnValue(
      new Promise((resolve) => {
        resolveLoad = resolve
      })
    )
    const onLoad = jest.fn()

    // Like the question editor, which unmounts the modal when the question loses focus
    const Harness = () => {
      const [isMounted, setIsMounted] = useState(true)
      return (
        <>
          <button type="button" onClick={() => setIsMounted(false)}>
            Unmount
          </button>
          {isMounted && (
            <LoadLabelSetModal show languages={['en']} onLoad={onLoad} />
          )}
        </>
      )
    }
    await renderWithProviders(<Harness />)

    openSelect('load-label-set-select')
    fireEvent.click(await screen.findByText('Yes/No'))
    fireEvent.click(getConfirmButton('Load label set'))
    fireEvent.click(screen.getByText('Unmount'))

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
        expect.objectContaining({
          lid: 4,
          revision: 'revision-4',
          languages: ['en'],
        })
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
      'If you replace the label set these translations will be lost:'
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

  test('Only offers overwriting without the right to create label sets', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    expect(screen.queryByText('Yes')).not.toBeInTheDocument()
    expect(screen.queryByTestId('save-label-set-name')).not.toBeInTheDocument()
    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))
    fireEvent.click(getConfirmButton('Save label set'))

    await waitFor(() =>
      expect(mockSaveLabelSet).toHaveBeenCalledWith(
        expect.objectContaining({ lid: 4 })
      )
    )
  })

  test('Only offers a new label set without the right to update label sets', async () => {
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canUpdate={false}
      />
    )

    expect(screen.queryByText('Yes')).not.toBeInTheDocument()
    expect(screen.getByTestId('save-label-set-name')).toBeInTheDocument()
    expect(
      screen.queryByTestId('save-label-set-overwrite-select')
    ).not.toBeInTheDocument()
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

  test('Names the dialog after the selected mode', async () => {
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
      />
    )

    expect(
      screen.getByRole('dialog', { name: 'Save as new label set' })
    ).toBeInTheDocument()
    fireEvent.click(screen.getByText('Yes'))
    expect(
      screen.getByRole('dialog', { name: 'Overwrite an existing label set' })
    ).toBeInTheDocument()
  })

  test('Blocks the overwrite while the list is refreshing or after it failed', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    await renderWithProviders(
      <RerenderHarness
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))
    expect(getConfirmButton('Save label set')).toBeEnabled()

    // E.g. refetched on window focus: the warnings may be outdated
    mockListFetching.current = true
    rerender()
    expect(getConfirmButton('Save label set')).toBeDisabled()

    mockListFetching.current = false
    mockListError.current = true
    rerender()
    expect(getConfirmButton('Save label set')).toBeDisabled()

    mockListError.current = false
    rerender()
    expect(getConfirmButton('Save label set')).toBeEnabled()
  })

  test('Blocks the overwrite when the selected set is gone from the refreshed list', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    await renderWithProviders(
      <RerenderHarness
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))
    expect(getConfirmButton('Save label set')).toBeEnabled()

    mockLabelSets.current = [{ lid: 6, name: 'Other set', languages: ['en'] }]
    rerender()
    expect(getConfirmButton('Save label set')).toBeDisabled()
    expect(
      screen.queryByTestId('save-label-set-overwrite-warning')
    ).not.toBeInTheDocument()
  })

  test('A save that finishes after the modal was reopened does not close it', async () => {
    let resolveSave
    mockSaveLabelSet.mockReturnValue(
      new Promise((resolve) => {
        resolveSave = resolve
      })
    )
    const onHide = jest.fn()

    const Harness = () => {
      const [show, setShow] = useState(true)
      return (
        <>
          <button type="button" onClick={() => setShow(true)}>
            Reopen
          </button>
          <SaveLabelSetModal
            show={show}
            onHide={() => {
              onHide()
              setShow(false)
            }}
            _children={answers}
            entityType={Entities.answer}
            surveyLanguages={['en']}
          />
        </>
      )
    }
    await renderWithProviders(<Harness />)

    const nameInput = screen
      .getByTestId('save-label-set-modal')
      .querySelector('input[type="text"]')
    fireEvent.change(nameInput, { target: { value: 'My answers' } })
    fireEvent.click(getConfirmButton('Save label set'))
    fireEvent.click(getConfirmButton('Cancel'))
    expect(onHide).toHaveBeenCalledTimes(1)
    fireEvent.click(screen.getByText('Reopen'))

    await act(async () => {
      resolveSave({ lid: 9 })
    })

    expect(onHide).toHaveBeenCalledTimes(1)
  })

  test('Warns about what the freshly loaded set contains, not the list', async () => {
    // The list was loaded before someone else added German and assessment values
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    mockDetails.current = {
      4: {
        lid: 4,
        name: 'Old set',
        languages: ['en', 'de'],
        labels: [{ code: 'A1', assessmentValue: 3, l10ns: {} }],
        revision: 'changed',
      },
    }
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))

    expect(
      screen.getByTestId('save-label-set-removed-languages')
    ).toHaveTextContent('de')
    expect(
      screen.getByTestId('save-label-set-assessment-warning')
    ).toBeInTheDocument()
    fireEvent.click(getConfirmButton('Save label set'))
    await waitFor(() =>
      expect(mockSaveLabelSet).toHaveBeenCalledWith(
        expect.objectContaining({ lid: 4, revision: 'changed' })
      )
    )
  })

  test('Blocks the overwrite while the selected set is loading or after it failed', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    await renderWithProviders(
      <RerenderHarness
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))
    expect(getConfirmButton('Save label set')).toBeEnabled()

    mockDetailFetching.current = true
    rerender()
    expect(getConfirmButton('Save label set')).toBeDisabled()

    mockDetailFetching.current = false
    mockDetailError.current = true
    rerender()
    expect(getConfirmButton('Save label set')).toBeDisabled()
    expect(
      screen.queryByTestId('save-label-set-overwrite-warning')
    ).not.toBeInTheDocument()
    fireEvent.click(
      within(screen.getByTestId('save-label-set-selected-error')).getByRole(
        'button',
        { name: 'Try again' }
      )
    )
    expect(mockRefetchDetail).toHaveBeenCalled()
  })

  test('Reloads the set and stays open when it was changed in the meantime', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    mockSaveLabelSet.mockRejectedValue({
      code: 'LABEL_SET_CONFLICT',
      message: 'The label set was changed in the meantime',
    })
    const onHide = jest.fn()
    await renderWithProviders(
      <SaveLabelSetModal
        show
        onHide={onHide}
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    openSelect('save-label-set-overwrite-select')
    fireEvent.click(await screen.findByText('Old set'))
    fireEvent.click(getConfirmButton('Save label set'))

    await waitFor(() => expect(mockRefetchDetail).toHaveBeenCalled())
    expect(mockRefetch).toHaveBeenCalled()
    expect(onHide).not.toHaveBeenCalled()
  })

  test('Focuses the set to overwrite when the user may only overwrite', async () => {
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    await renderWithProviders(
      <SaveLabelSetModal
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )

    await waitFor(() =>
      expect(
        screen
          .getByTestId('save-label-set-overwrite-select')
          .querySelector('input')
      ).toHaveFocus()
    )
  })

  test('Focuses the set to overwrite once the list has loaded', async () => {
    mockListFetching.current = true
    await renderWithProviders(
      <RerenderHarness
        show
        _children={answers}
        entityType={Entities.answer}
        surveyLanguages={['en']}
        canCreate={false}
      />
    )
    const select = screen
      .getByTestId('save-label-set-overwrite-select')
      .querySelector('input')
    // A browser doesn't focus the disabled select while the list loads, unlike jsdom
    act(() => select.blur())
    expect(select).not.toHaveFocus()

    mockListFetching.current = false
    mockLabelSets.current = [{ lid: 4, name: 'Old set', languages: ['en'] }]
    rerender()

    await waitFor(() => expect(select).toHaveFocus())
  })
})
