import 'tests/mocks'

import { act, useState } from 'react'
import { renderWithProviders } from 'tests/testUtils'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { OptionQuestionEditMode } from './OptionQuestionEditMode'

// The props each option's ImageChoice got last, keyed by option code.
const mockImageChoiceProps = {}
jest.mock('components/QuestionTypes/ImageChoice', () => ({
  ImageChoice: (props) => {
    mockImageChoiceProps[props.code] = props
    return null
  },
}))

// The other images of "Apply to all" load only when the test resolves them.
const mockImageLoads = []
jest.mock('components/UIComponents/ImageEditor/imageEditorUtils', () => ({
  ...jest.requireActual('components/UIComponents/ImageEditor/imageEditorUtils'),
  loadImageSize: () =>
    new Promise((resolve) => {
      mockImageLoads.push(resolve)
    }),
}))

const getAnswer = (aid, code) => ({
  aid,
  code,
  sortOrder: aid,
  l10ns: { en: { answer: `/upload/surveys/1/images/${code}.png` } },
})

const settings = {
  width: 200,
  height: 100,
  keepRatio: true,
  zoom: 1,
  positionX: 0.5,
  positionY: 0.5,
  radius: 0,
  crop: { x: 0, y: 0.25, width: 1, height: 0.5 },
}

let setTestQuestion
const Harness = ({ initialQuestion, handleUpdate }) => {
  const [question, setQuestion] = useState(initialQuestion)
  setTestQuestion = setQuestion
  return (
    <OptionQuestionEditMode
      question={question}
      handleUpdate={handleUpdate}
      handleChildLUpdate={jest.fn()}
      isFocused={true}
      handleChildAdd={jest.fn()}
      handleOnChildDragEnd={jest.fn()}
      handleChildDelete={jest.fn()}
      language="en"
      handleChildCodeUpdate={jest.fn()}
      handleChildAssessmentValueUpdate={jest.fn()}
      _children={question.answers}
    />
  )
}

/**
 * Renders an image select list question with the options A1, A2 and A3 and
 * starts saving A1's image with "Apply to all", which waits for the other
 * images to load.
 *
 * @param {Function} handleUpdate gets the question changes
 * @returns {Promise<{savePromise: Promise<void>}>} the pending save, wrapped:
 *   An async function returning the promise itself would wait for it
 */
const startApplyToAllSave = async (handleUpdate) => {
  await renderWithProviders(
    <Harness
      handleUpdate={handleUpdate}
      initialQuestion={{
        qid: 1,
        questionThemeName:
          getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
        attributes: {},
        answers: [getAnswer(1, 'A1'), getAnswer(2, 'A2'), getAnswer(3, 'A3')],
      }}
    />
  )
  let savePromise
  act(() => {
    savePromise = mockImageChoiceProps.A1.onImageSettingsSave({
      ...settings,
      altText: 'First',
      applyToAll: true,
    })
  })
  return { savePromise }
}

/**
 * Lets the other images load and waits for the save to finish.
 *
 * @param {Promise<void>} savePromise the pending save
 * @returns {Promise<void>}
 */
const finishImageLoads = async (savePromise) => {
  await act(async () => {
    mockImageLoads
      .splice(0)
      .forEach((resolve) => resolve({ width: 400, height: 400 }))
    await savePromise
  })
}

describe('OptionQuestionEditMode image option settings', () => {
  beforeEach(() => {
    mockImageLoads.splice(0)
  })

  test('"Apply to all" keeps changes made while the other images were loading', async () => {
    const handleUpdate = jest.fn()
    const { savePromise } = await startApplyToAllSave(handleUpdate)

    // Meanwhile, A2 gets an alt text and A3 is deleted.
    act(() =>
      setTestQuestion((question) => ({
        ...question,
        attributes: { image_option_alt: { en: '{"A2":"Second"}' } },
        answers: question.answers.filter((answer) => answer.code !== 'A3'),
      }))
    )
    await finishImageLoads(savePromise)

    expect(handleUpdate).toHaveBeenCalledTimes(1)
    const { attributes } = handleUpdate.mock.calls[0][0]
    expect(JSON.parse(attributes.image_option_alt.en)).toEqual({
      A1: 'First',
      A2: 'Second',
    })
    expect(
      Object.keys(JSON.parse(attributes.image_option_settings['']))
    ).toEqual(['A2', 'A1'])
  })

  test('Nothing is saved when the edited option was deleted while the other images were loading', async () => {
    const handleUpdate = jest.fn()
    const { savePromise } = await startApplyToAllSave(handleUpdate)

    act(() =>
      setTestQuestion((question) => ({
        ...question,
        answers: question.answers.filter((answer) => answer.code !== 'A1'),
      }))
    )
    await finishImageLoads(savePromise)

    expect(handleUpdate).not.toHaveBeenCalled()
  })
})
