// Import shared mocks
import 'tests/mocks'

import { useState } from 'react'
import { fireEvent, screen } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'

import { SurveyCodeForm } from './SurveyCodeForm'
import { SurveyTitleSelector } from './SurveyTitleSelector'

const makeSurvey = (sid, code = '') => ({
  sid,
  code,
  languageSettings: { en: { sid, language: 'en', title: `Survey ${sid}` } },
})

const selectorProps = {
  surveyList: [],
  activeLanguage: 'en',
  onSurveyTitleChange: () => {},
  handleSurveySwitch: () => {},
  getError: () => undefined,
  canEditSurveyCode: true,
  showCode: true,
}

// The test wrapper passes its own `survey` to the rendered element, so render through this
const SelectorHarness = ({ selector }) => (
  <SurveyTitleSelector
    surveyId={1}
    survey={makeSurvey(1)}
    onSurveyCodeSave={jest.fn()}
    {...selectorProps}
    {...selector}
  />
)
const renderSelector = (props = {}) =>
  renderWithProviders(<SelectorHarness selector={props} />)

const getAddButton = () =>
  screen.getByRole('button', { name: 'Add internal title' })
const queryForm = () => screen.queryByRole('region', { name: 'Internal title' })
const getInput = () => document.getElementById('internal-title-input')

describe('SurveyTitleSelector internal title', () => {
  test('The add button has an accessible name and opens the form by click, e.g. from the keyboard', async () => {
    await renderSelector()

    expect(queryForm()).not.toBeInTheDocument()
    // Enter and Space on a button fire a click, not a mousedown
    fireEvent.click(getAddButton())
    expect(queryForm()).toBeInTheDocument()
  })

  test('The add button is out of reach when it cannot be used', async () => {
    await renderSelector({ showCode: false })
    expect(getAddButton()).toBeDisabled()
  })

  test('The add button is out of reach without the edit right', async () => {
    await renderSelector({ canEditSurveyCode: false })
    expect(getAddButton()).toBeDisabled()
  })

  test('The badge opens the form with the keyboard', async () => {
    await renderSelector({ survey: makeSurvey(1, 'ACME') })

    const badge = screen.getByRole('button', {
      name: 'Internal title: ACME',
    })
    expect(badge).toHaveAttribute('tabindex', '0')
    fireEvent.keyDown(badge, { key: 'Enter' })

    expect(queryForm()).toBeInTheDocument()
    expect(getInput()).toHaveValue('ACME')
  })

  test('The badge cannot be focused or used without the edit right', async () => {
    await renderSelector({
      survey: makeSurvey(1, 'ACME'),
      canEditSurveyCode: false,
    })

    const badge = screen.getByRole('button', {
      name: 'Internal title: ACME',
    })
    expect(badge).toHaveAttribute('aria-disabled', 'true')
    expect(badge).not.toHaveAttribute('tabindex')
    fireEvent.keyDown(badge, { key: 'Enter' })
    fireEvent.click(badge)
    expect(queryForm()).not.toBeInTheDocument()
  })

  test('Closes the form when switching to another survey', async () => {
    const onSurveyCodeSave = jest.fn()
    const Harness = () => {
      const [surveyId, setSurveyId] = useState(1)
      return (
        <>
          <button type="button" onClick={() => setSurveyId(2)}>
            Switch survey
          </button>
          <SurveyTitleSelector
            {...selectorProps}
            surveyId={surveyId}
            survey={makeSurvey(surveyId)}
            onSurveyCodeSave={onSurveyCodeSave}
          />
        </>
      )
    }
    await renderWithProviders(<Harness />)

    fireEvent.click(getAddButton())
    fireEvent.change(getInput(), {
      target: { value: 'Draft for survey 1' },
    })
    fireEvent.click(screen.getByText('Switch survey'))

    expect(queryForm()).not.toBeInTheDocument()
    expect(onSurveyCodeSave).not.toHaveBeenCalled()
  })
})

describe('SurveyCodeForm', () => {
  test('Enter saves the trimmed value, but not while an IME candidate is confirmed', async () => {
    const onSave = jest.fn()
    await renderWithProviders(
      <SurveyCodeForm isNew onSave={onSave} onCancel={() => {}} />
    )

    const input = getInput()
    fireEvent.change(input, { target: { value: ' 調査 ' } })

    fireEvent.keyDown(input, { key: 'Enter', isComposing: true })
    expect(onSave).not.toHaveBeenCalled()

    fireEvent.keyDown(input, { key: 'Enter' })
    expect(onSave).toHaveBeenCalledWith('調査')
  })
})
