// Import shared mocks
import 'tests/mocks'

import { screen } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { getQuestionTypeInfo } from 'components/QuestionTypes'

import { OptionQuestionEditMode } from './OptionQuestionEditMode'

describe('OptionQuestionEditMode label sets', () => {
  const question = {
    qid: 42,
    gid: 7,
    questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
  }

  const renderEditMode = (isFocused) =>
    renderWithProviders(
      <OptionQuestionEditMode
        question={question}
        isFocused={isFocused}
        language="en"
        _children={[]}
        handleChildAdd={() => {}}
        handleChildDelete={() => {}}
        handleOnChildDragEnd={() => {}}
        handleChildLUpdate={() => {}}
      />
    )

  test('Shows the load label set link when the user may edit the question', async () => {
    await renderEditMode(true)

    expect(
      await screen.findByTestId('load-label-set-button')
    ).toBeInTheDocument()
  })

  test('Hides the load label set link without survey update permission', async () => {
    // QuestionBody passes isFocused=false when the user lacks update permission
    await renderEditMode(false)

    expect(screen.getByTestId('add-child-button')).toBeInTheDocument()
    expect(
      screen.queryByTestId('load-label-set-button')
    ).not.toBeInTheDocument()
  })
})
