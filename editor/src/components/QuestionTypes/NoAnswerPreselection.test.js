import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { FivePointChoiceQuestion } from './FivePointChoiceQuestion/FivePointChoiceQuestion'
import { GenderQuestion } from './GenderQuestion/GenderQuestion'
import { YesNoQuestion } from './YesNoQuestion/YesNoQuestion'

const question = { qid: 1, mandatory: false }

describe('No answer preselection', () => {
  test('shows No answer without selecting it when preselection is disabled', async () => {
    await renderWithProviders(
      <FivePointChoiceQuestion
        question={question}
        surveySettings={{ showNoAnswer: true, preselectNoAnswer: false }}
        participantMode={true}
        values={[{ key: 'field', checked: false }]}
      />
    )

    const answers = screen.getAllByRole('radio')
    expect(answers).toHaveLength(6)
    expect(answers[5]).not.toBeChecked()
  })

  test('preserves the historic preselection when enabled', async () => {
    await renderWithProviders(
      <FivePointChoiceQuestion
        question={question}
        surveySettings={{ showNoAnswer: true, preselectNoAnswer: true }}
        participantMode={true}
        values={[{ key: 'field', checked: false }]}
      />
    )

    const answers = screen.getAllByRole('radio')
    expect(answers[5]).toBeChecked()
  })

  test('does not render No answer when visibility is disabled', async () => {
    await renderWithProviders(
      <FivePointChoiceQuestion
        question={question}
        surveySettings={{ showNoAnswer: false, preselectNoAnswer: true }}
        participantMode={true}
        values={[{ key: 'field', checked: false }]}
      />
    )

    expect(screen.getAllByRole('radio')).toHaveLength(5)
  })

  test.each([
    [false, false],
    [true, true],
  ])(
    'applies preselection=%s to Yes/No questions',
    async (preselectNoAnswer, expectedChecked) => {
      await renderWithProviders(
        <YesNoQuestion
          question={{ ...question, attributes: { display_type: '1' } }}
          surveySettings={{ showNoAnswer: true, preselectNoAnswer }}
          values={[{ key: 'field', value: null }]}
        />
      )

      const answers = screen.getAllByRole('radio')
      expect(answers).toHaveLength(3)
      expect(answers[2].checked).toBe(expectedChecked)
    }
  )

  test('allows participants to select No answer when it was not preselected', async () => {
    const user = userEvent.setup()

    await renderWithProviders(
      <GenderQuestion
        question={{ ...question, attributes: { display_type: '1' } }}
        surveySettings={{ showNoAnswer: true, preselectNoAnswer: false }}
        values={[{ key: 'field', value: null }]}
      />
    )

    const answers = screen.getAllByRole('radio')
    expect(answers).toHaveLength(3)
    expect(answers[2]).not.toBeChecked()

    await user.click(answers[2])

    expect(answers[2]).toBeChecked()
  })
})
