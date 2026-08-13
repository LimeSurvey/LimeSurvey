import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { renderWithProviders } from 'tests/testUtils'
import { ImportSurveyModal } from './ImportSurveyModal'

describe('ImportSurveyModal', () => {
  test('submits the selected options and displays the returned summary', async () => {
    const user = userEvent.setup()
    const onImport = jest.fn().mockResolvedValue({
      newsid: 123456,
      surveys: 1,
      questions: 4,
    })

    await renderWithProviders(<ImportSurveyModal show onImport={onImport} />)

    const file = new File(['survey'], 'survey.lss', { type: 'text/xml' })
    await user.upload(screen.getByLabelText('Select or drop a file here'), file)
    await user.click(screen.getByRole('button', { name: 'Import survey' }))

    expect(onImport).toHaveBeenCalledWith({
      convertResourceLinks: true,
      file,
      groupStrategy: 'default',
    })
    expect(await screen.findByText('Import summary')).toBeInTheDocument()
    expect(screen.getByText('Questions')).toBeInTheDocument()
    expect(screen.getByText('4')).toBeInTheDocument()
  })

  test('renders a controlled summary with navigation actions', async () => {
    const user = userEvent.setup()
    const onGoToSurvey = jest.fn()

    await renderWithProviders(
      <ImportSurveyModal
        show
        onGoToSurvey={onGoToSurvey}
        summary={{ newsid: 987654, surveys: 1 }}
      />
    )

    await user.click(screen.getByRole('button', { name: 'Go to survey' }))
    expect(onGoToSurvey).toHaveBeenCalledWith(987654)
  })

  test('decodes HTML entities in import warnings', async () => {
    await renderWithProviders(
      <ImportSurveyModal
        show
        summary={{
          newsid: 987654,
          surveys: 1,
          importwarnings: ['Setting was not imported: allow_embed =&gt; Y'],
        }}
      />
    )

    expect(
      screen.getByText('Setting was not imported: allow_embed => Y')
    ).toBeInTheDocument()
  })
})
