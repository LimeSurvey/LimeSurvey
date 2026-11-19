import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { setSurveyActive } from 'tests/surveyEditing'

import { ArrayRow } from './ArrayRow'

// Renders a focused row of the given kind on an active survey and returns its drag area
const renderRowOnActiveSurvey = async (itemsKey) => {
  await renderWithProviders(
    <ArrayRow
      isFocused={true}
      showContentEditor={true}
      index={0}
      itemsKey={itemsKey}
      code="A1"
      titleValue="Row"
      scaleId={0}
      highestWidth={0}
      numberOfHorizontalEntities={0}
      removeItem={jest.fn()}
      handleUpdateL10ns={jest.fn()}
      handleCodeUpdate={jest.fn()}
    />
  )
  await setSurveyActive(true)
  return document.querySelector('.action-item-button.drag')
}

describe('ArrayRow drag handle on an active survey', () => {
  afterEach(async () => {
    await setSurveyActive(false)
  })

  test('answer option rows can be dragged', async () => {
    const dragArea = await renderRowOnActiveSurvey('answers')

    expect(dragArea).not.toHaveClass('cursor-not-allowed')
    expect(dragArea.firstChild).not.toHaveClass('disabled')
  })

  test('subquestion rows are locked', async () => {
    const dragArea = await renderRowOnActiveSurvey('subquestions')

    expect(dragArea).toHaveClass('cursor-not-allowed')
    expect(dragArea.firstChild).toHaveClass('disabled')
  })
})
