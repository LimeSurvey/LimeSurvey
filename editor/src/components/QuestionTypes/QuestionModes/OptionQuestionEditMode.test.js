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
// Without that, they stop being waited for after mockImageLoadTimeout ms.
const mockImageLoads = []
let mockImageLoadTimeout = 60000
jest.mock('components/UIComponents/ImageEditor/imageEditorUtils', () => ({
  ...jest.requireActual('components/UIComponents/ImageEditor/imageEditorUtils'),
  get IMAGE_LOAD_TIMEOUT() {
    return mockImageLoadTimeout
  },
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
 * @param {Object[]} answers the options, A1, A2 and A3 with English images by
 *   default
 * @returns {Promise<{savePromise: Promise<void>}>} the pending save, wrapped:
 *   An async function returning the promise itself would wait for it
 */
const startApplyToAllSave = async (
  handleUpdate,
  answers = [getAnswer(1, 'A1'), getAnswer(2, 'A2'), getAnswer(3, 'A3')]
) => {
  await renderWithProviders(
    <Harness
      handleUpdate={handleUpdate}
      initialQuestion={{
        qid: 1,
        questionThemeName:
          getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
        attributes: {},
        answers,
      }}
    />
  )
  return startSave('A1', { ...settings, altText: 'First', applyToAll: true })
}

/**
 * Starts saving the image settings of an option, as the image editor does.
 *
 * @param {string} code the option code
 * @param {Object} imageSettings the settings from ImageEditor's onSave
 * @returns {Promise<{savePromise: Promise<void>}>} the pending save, wrapped:
 *   An async function returning the promise itself would wait for it
 */
const startSave = async (code, imageSettings) => {
  let savePromise
  // Async act: The queued save starts and begins to load the images.
  await act(async () => {
    savePromise = mockImageChoiceProps[code].onImageSettingsSave(imageSettings)
  })
  return { savePromise }
}

/**
 * Lets the images load, the newest first, until the saves are done.
 *
 * @param {...Promise<void>} savePromises the pending saves
 * @returns {Promise<void>}
 */
const finishImageLoads = async (...savePromises) => {
  await act(async () => {
    let done = false
    Promise.all(savePromises).then(() => {
      done = true
    })
    while (!done) {
      mockImageLoads
        .splice(0)
        .reverse()
        .forEach((resolve) => resolve({ width: 400, height: 400 }))
      await new Promise((resolve) => setTimeout(resolve, 0))
    }
  })
}

describe('OptionQuestionEditMode image option settings', () => {
  beforeEach(() => {
    mockImageLoads.splice(0)
    mockImageLoadTimeout = 60000
  })

  test('An image that never loads is left out, the saves after it still run', async () => {
    mockImageLoadTimeout = 20
    const handleUpdate = jest.fn()
    const { savePromise: first } = await startApplyToAllSave(handleUpdate)
    const { savePromise: second } = await startSave('A2', {
      ...settings,
      altText: 'Second',
    })
    // The images of the first save never load.
    await act(async () => {
      await Promise.all([first, second])
    })

    expect(handleUpdate).toHaveBeenCalledTimes(2)
    const { attributes } = handleUpdate.mock.calls[1][0]
    expect(
      Object.keys(JSON.parse(attributes.image_option_settings.en)).sort()
    ).toEqual(['A1', 'A2'])
    expect(JSON.parse(attributes.image_option_alt.en)).toEqual({
      A1: 'First',
      A2: 'Second',
    })
  })

  test('A queued save is dropped when the option gets another image while it waits', async () => {
    const handleUpdate = jest.fn()
    const { savePromise: first } = await startApplyToAllSave(handleUpdate)
    const { savePromise: second } = await startSave('A2', {
      ...settings,
      width: 300,
      altText: 'Second',
    })
    // A2 gets another image before the queued save runs.
    act(() =>
      setTestQuestion((question) => ({
        ...question,
        answers: question.answers.map((answer) =>
          answer.code === 'A2'
            ? { ...answer, l10ns: { en: { answer: '/upload/new.png' } } }
            : answer
        ),
      }))
    )
    await finishImageLoads(first, second)

    // Only the first save, without A2: Its image changed.
    expect(handleUpdate).toHaveBeenCalledTimes(1)
    const { attributes } = handleUpdate.mock.calls[0][0]
    expect(
      Object.keys(JSON.parse(attributes.image_option_settings.en)).sort()
    ).toEqual(['A1', 'A3'])
    expect(JSON.parse(attributes.image_option_alt.en)).toEqual({ A1: 'First' })
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
      Object.keys(JSON.parse(attributes.image_option_settings.en))
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

  test('"Apply to all" leaves out an option whose image was replaced while it was loading', async () => {
    const handleUpdate = jest.fn()
    const { savePromise } = await startApplyToAllSave(handleUpdate)

    act(() =>
      setTestQuestion((question) => ({
        ...question,
        answers: question.answers.map((answer) =>
          answer.code === 'A2'
            ? { ...answer, l10ns: { en: { answer: '/upload/new.png' } } }
            : answer
        ),
      }))
    )
    await finishImageLoads(savePromise)

    expect(handleUpdate).toHaveBeenCalledTimes(1)
    const { attributes } = handleUpdate.mock.calls[0][0]
    expect(
      Object.keys(JSON.parse(attributes.image_option_settings.en))
    ).toEqual(['A3', 'A1'])
  })

  test('The settings also go to the languages with the same image', async () => {
    const handleUpdate = jest.fn()
    // A1 and A3 have the same image in German, A2 has its own.
    const withGerman = (answer, germanImage) => ({
      ...answer,
      l10ns: { ...answer.l10ns, de: { answer: germanImage } },
    })
    const { savePromise } = await startApplyToAllSave(handleUpdate, [
      withGerman(getAnswer(1, 'A1'), '/upload/surveys/1/images/A1.png'),
      withGerman(getAnswer(2, 'A2'), '/upload/surveys/1/images/A2-de.png'),
      withGerman(getAnswer(3, 'A3'), '/upload/surveys/1/images/A3.png'),
    ])
    await finishImageLoads(savePromise)

    const { attributes } = handleUpdate.mock.calls[0][0]
    expect(
      Object.keys(JSON.parse(attributes.image_option_settings.en)).sort()
    ).toEqual(['A1', 'A2', 'A3'])
    expect(
      Object.keys(JSON.parse(attributes.image_option_settings.de)).sort()
    ).toEqual(['A1', 'A3'])
    // The alt text is only for the language it was written in.
    expect(attributes.image_option_alt).toEqual({
      en: JSON.stringify({ A1: 'First' }),
    })
  })

  test('A later "Apply to all" wins, also when the earlier one loads more slowly', async () => {
    const handleUpdate = jest.fn()
    const { savePromise: first } = await startApplyToAllSave(handleUpdate)
    const { savePromise: second } = await startSave('A2', {
      ...settings,
      width: 300,
      altText: 'Second',
      applyToAll: true,
    })
    await finishImageLoads(first, second)

    const { attributes } = handleUpdate.mock.calls.at(-1)[0]
    const savedSettings = JSON.parse(attributes.image_option_settings.en)
    expect(savedSettings.A1.width).toBe(300)
    expect(savedSettings.A2.width).toBe(300)
    expect(savedSettings.A3.width).toBe(300)
    expect(JSON.parse(attributes.image_option_alt.en)).toEqual({
      A1: 'First',
      A2: 'Second',
    })
  })
})
