import 'tests/mocks'

import { DragDropContext } from '@hello-pangea/dnd'
import { renderWithProviders } from 'tests/testUtils'

import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'
import { RowQuestionsList } from './RowQuestionsList'

// The props of each row, keyed by qid: The test calls the row's handlers.
// The row only renders the drag handle the list needs.
const mockRowProps = {}
jest.mock('./RowQuestion', () => ({
  RowQuestion: (props) => {
    mockRowProps[props.question.qid] = props
    return <div {...props.provided?.dragHandleProps} />
  },
}))

const mockAddToBuffer = jest.fn()
jest.mock('hooks/useBuffer', () => ({
  useBuffer: () => ({ addToBuffer: mockAddToBuffer }),
}))

const settings = (image) => ({ width: 200, height: 100, image })

/**
 * Renders the list with one question and assigns numeric answer codes to it.
 *
 * @param {Object} question the question
 * @returns {Promise<Function>} the handleUpdate mock of the list
 */
const assignNumericCodes = async (question) => {
  const handleUpdate = jest.fn()
  await renderWithProviders(
    <DragDropContext onDragEnd={() => {}}>
      <RowQuestionsList
        questions={[question]}
        language="en"
        groupIndex={0}
        handleUpdate={handleUpdate}
      />
    </DragDropContext>
  )
  expect(mockRowProps[question.qid].assignNumericAnswerCodes(question)).toBe(
    true
  )
  return handleUpdate
}

const answers = [
  { aid: 1, code: 'A1', sortOrder: 1, scaleId: 0 },
  { aid: 2, code: 'A2', sortOrder: 2, scaleId: 0 },
]

describe('RowQuestionsList numeric answer codes', () => {
  beforeEach(() => mockAddToBuffer.mockClear())

  test('Image settings and alt texts move to the new codes', async () => {
    const question = {
      qid: 7,
      questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
      attributes: {
        image_option_settings: {
          en: JSON.stringify({ A1: settings('a.png'), A2: settings('b.png') }),
        },
        image_option_alt: { en: JSON.stringify({ A2: 'Second' }) },
      },
      answers,
    }
    const handleUpdate = await assignNumericCodes(question)

    const [updated] = handleUpdate.mock.calls[0][0]
    expect(updated.answers.map((answer) => answer.code)).toEqual(['1', '2'])
    expect(JSON.parse(updated.attributes.image_option_settings.en)).toEqual({
      1: settings('a.png'),
      2: settings('b.png'),
    })
    expect(JSON.parse(updated.attributes.image_option_alt.en)).toEqual({
      2: 'Second',
    })
    const attributeOperation = mockAddToBuffer.mock.calls
      .map(([operation]) => operation)
      .find((operation) => operation.entity === 'questionAttribute')
    expect(attributeOperation.id).toBe(7)
    expect(attributeOperation.props.image_option_alt).toEqual({
      en: JSON.stringify({ 2: 'Second' }),
    })
  })

  test('Other question types only get new answer codes', async () => {
    const question = {
      qid: 8,
      questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
      attributes: { image_option_alt: { en: JSON.stringify({ A1: 'x' }) } },
      answers,
    }
    const handleUpdate = await assignNumericCodes(question)

    const [updated] = handleUpdate.mock.calls[0][0]
    expect(updated.attributes).toBe(question.attributes)
    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
  })
})
