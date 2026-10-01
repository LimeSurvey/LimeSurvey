import 'tests/mocks'

import { useState } from 'react'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { FivePointChoiceQuestion } from './FivePointChoiceQuestion/FivePointChoiceQuestion'
import { GenderQuestion } from './GenderQuestion/GenderQuestion'
import { OptionQuestionViewMode } from './QuestionModes/OptionQuestionViewMode'
import { YesNoQuestion } from './YesNoQuestion/YesNoQuestion'
import { getQuestionTypeInfo } from './getQuestionTypeInfo'

const question = { qid: 1, mandatory: false }

const PreselectionHarness = ({ QuestionComponent, componentProps }) => {
  const [preselectNoAnswer, setPreselectNoAnswer] = useState(false)

  return (
    <>
      <button onClick={() => setPreselectNoAnswer(true)}>
        Enable preselection
      </button>
      <QuestionComponent
        {...componentProps}
        surveySettings={{ showNoAnswer: true, preselectNoAnswer }}
      />
    </>
  )
}

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

  test.each([
    [
      '5 point choice',
      FivePointChoiceQuestion,
      {
        question,
        participantMode: true,
        values: [{ key: 'field', checked: false }],
      },
    ],
    [
      'Yes/No',
      YesNoQuestion,
      {
        question: { ...question, attributes: { display_type: '1' } },
        values: [{ key: 'field', value: null }],
      },
    ],
    [
      'gender',
      GenderQuestion,
      {
        question: { ...question, attributes: { display_type: '1' } },
        values: [{ key: 'field', value: null }],
      },
    ],
    [
      'list radio',
      OptionQuestionViewMode,
      {
        question: {
          ...question,
          gid: 1,
          attributes: {},
          other: false,
          questionThemeName:
            getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
        },
        language: 'en',
        _children: [
          { aid: 1, code: 'A1', l10ns: { en: { answer: 'Answer 1' } } },
        ],
        participantMode: true,
        values: [{ key: 'field', aid: null, checked: false }],
      },
    ],
  ])(
    'updates the mounted %s controls when preselection changes',
    async (_name, QuestionComponent, componentProps) => {
      const user = userEvent.setup()

      await renderWithProviders(
        <PreselectionHarness
          QuestionComponent={QuestionComponent}
          componentProps={componentProps}
        />
      )

      const answers = screen.getAllByRole('radio')
      expect(answers.at(-1)).not.toBeChecked()

      await user.click(
        screen.getByRole('button', { name: 'Enable preselection' })
      )

      expect(answers.at(-1)).toBeChecked()
    }
  )
})
