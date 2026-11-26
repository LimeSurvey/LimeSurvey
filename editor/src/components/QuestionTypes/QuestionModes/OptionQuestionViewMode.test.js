import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { OptionQuestionViewMode } from './OptionQuestionViewMode'

// The props each option's ImageChoice got last, keyed by option code.
const mockImageChoiceProps = {}
jest.mock('components/QuestionTypes/ImageChoice', () => ({
  ImageChoice: (props) => {
    mockImageChoiceProps[props.code] = props
    return null
  },
}))

const getAnswer = (aid, code) => ({
  aid,
  code,
  sortOrder: aid,
  l10ns: { en: { answer: `/upload/surveys/1/images/${code}.png` } },
})

describe('OptionQuestionViewMode image options', () => {
  test('Passes the code, so an image without alt text uses it', async () => {
    const answers = [getAnswer(1, 'A1'), getAnswer(2, 'A2')]
    await renderWithProviders(
      <OptionQuestionViewMode
        question={{
          qid: 1,
          gid: 1,
          questionThemeName:
            getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
          attributes: {
            image_option_settings: {
              en: JSON.stringify({ A1: { width: 200, image: 'A1.png' } }),
            },
            image_option_alt: { en: JSON.stringify({ A1: 'Lime' }) },
          },
        }}
        language="en"
        _children={answers}
      />
    )

    expect(mockImageChoiceProps.A1.imageAltText).toBe('Lime')
    expect(mockImageChoiceProps.A2.code).toBe('A2')
    expect(mockImageChoiceProps.A2.imageAltText).toBe('')
  })
})
