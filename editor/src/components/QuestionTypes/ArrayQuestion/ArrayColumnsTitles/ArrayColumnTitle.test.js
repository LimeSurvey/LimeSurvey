import 'tests/mocks'

import { act } from 'react'
import { fireEvent, screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'
import { setSurveyActive, WithCodesShown } from 'tests/surveyEditing'

import { ArrayColumnTitle } from './ArrayColumnTitle'

const renderColumnTitle = async ({ itemsKey, code, handleChildCodeUpdate }) => {
  await renderWithProviders(
    <WithCodesShown>
      <ArrayColumnTitle
        isFocused={true}
        index={1}
        itemsKey={itemsKey}
        code={code}
        title="Column"
        removeItem={jest.fn()}
        handleUpdateL10ns={jest.fn()}
        handleChildCodeUpdate={handleChildCodeUpdate}
      />
    </WithCodesShown>
  )
  await act(async () => {
    await new Promise((resolve) => setTimeout(resolve, 0))
  })
  await setSurveyActive(true)
}

describe('ArrayColumnTitle on an active survey', () => {
  afterEach(async () => {
    await setSurveyActive(false)
  })

  test('answer option codes are editable and the answer option can be removed', async () => {
    const handleChildCodeUpdate = jest.fn()
    await renderColumnTitle({
      itemsKey: 'answers',
      code: 'A2',
      handleChildCodeUpdate,
    })

    fireEvent.change(screen.getByRole('textbox', { name: 'Answer code A2' }), {
      target: { value: 'B2' },
    })

    expect(handleChildCodeUpdate).toHaveBeenCalledWith('B2', 1)
    expect(
      screen.getByTestId('remove-horizontal-option-button')
    ).toBeInTheDocument()
  })

  test('subquestion codes are read-only and the subquestion can not be removed', async () => {
    await renderColumnTitle({
      itemsKey: 'subquestions',
      code: 'SQ001',
      handleChildCodeUpdate: jest.fn(),
    })

    expect(screen.queryByRole('textbox', { name: /code/ })).toBeNull()
    expect(screen.getByText('SQ001')).toHaveClass('question-code-tag')
    expect(screen.queryByTestId('remove-horizontal-option-button')).toBeNull()
  })
})
