import { render, screen } from '@testing-library/react'

import 'tests/mocks'
import { renderWithProviders, setSurveyUpdatePermission } from 'tests/testUtils'

import { withSurveyUpdatePermission } from './withSurveyUpdatePermission'
import { Input } from './Input/Input'
import { Select, selectName } from './Select/Select'
import { ToggleButtons } from './Buttons/index.js'
import { ExpressionScriptEditor } from './ExpressionScriptEditor/index.js'

const PermissionProbe = ({ hasSurveyUpdatePermission = 'default' }) => (
  <span data-testid="probe">{String(hasSurveyUpdatePermission)}</span>
)

describe('withSurveyUpdatePermission', () => {
  afterEach(async () => {
    await setSurveyUpdatePermission(true)
  })

  // ArrayParticipantMode and OptionQuestionViewMode identify components by name
  test('Should keep a distinct name for each wrapped component', () => {
    expect(Input.name).toBe('Input')
    expect(Select.name).toBe('Select')
    expect(selectName).toBe('Select')
    expect(ToggleButtons.name).toBe('ToggleButtons')
    expect(ExpressionScriptEditor.name).toBe('ExpressionScriptEditor')
  })

  test('Should pass the permission from the app state', async () => {
    const Probe = withSurveyUpdatePermission(PermissionProbe, 'Probe')
    await renderWithProviders(<Probe />)
    await setSurveyUpdatePermission(false)

    expect(screen.getByTestId('probe')).toHaveTextContent('false')
  })

  test('Should let an explicit hasSurveyUpdatePermission prop win', async () => {
    const Probe = withSurveyUpdatePermission(PermissionProbe, 'Probe')
    await renderWithProviders(<Probe hasSurveyUpdatePermission={false} />)
    await setSurveyUpdatePermission(true)

    expect(screen.getByTestId('probe')).toHaveTextContent('false')
  })

  test('Should render with the field default outside a QueryClientProvider', () => {
    const Probe = withSurveyUpdatePermission(PermissionProbe, 'Probe')
    render(<Probe />)

    expect(screen.getByTestId('probe')).toHaveTextContent('default')
  })

  test('Should lock the expression script editor without permission', async () => {
    await renderWithProviders(
      <ExpressionScriptEditor
        ariaLabel="Expression"
        noPermissionDisabled={true}
      />
    )
    await setSurveyUpdatePermission(false)

    expect(screen.getByRole('textbox', { name: 'Expression' })).toHaveAttribute(
      'contenteditable',
      'false'
    )
  })
})
