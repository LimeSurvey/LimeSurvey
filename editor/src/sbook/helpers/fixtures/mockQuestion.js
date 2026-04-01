import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { Direction } from 'react-range'
import { LANGUAGE_CODES } from 'helpers/constants'

const QUESTION_ATTRIBUTES = {
  useSlider: false,
  sliderOrientation: Direction.Right,
  maximumFileSizeAllowed: { value: false },
  public_statistics: {
    value: true,
  },
  minuteStepInterval: {
    value: 2,
  },
  imageAlign: 'center',
  imageBrightness: 1,
  // image: 'https://img.freepik.com/free-photo/lime-isolated_93675-131268.jpg',
}

/**
 * Create a mock Question object used for testing purposes.
 * @returns a Question object
 */
export function mockQuestion(
  type = getQuestionTypeInfo().ARRAY,
  questionAttributes = QUESTION_ATTRIBUTES
) {
  const attributes = { ...QUESTION_ATTRIBUTES, ...questionAttributes }

  return {
    gid: 1,
    qid: 1,
    sid: 1,
    type,
    l10ns: {
      en: {
        id: 1,
        language: LANGUAGE_CODES.EN,
        qid: 1,
        question: 'Hello there',
        script: '',
      },
    },

    answers: [
      {
        aid: 40,
        qid: 1,
        code: 'AO01',
        sortOrder: 0,
        assessmentValue: 0,
        scaleId: 0,
        l10ns: {
          en: {
            id: 40,
            aid: 40,
            answer: 'Answer 1',
            language: 'en',
          },
        },
      },
    ],

    subquestions: [],
    attributes,
  }
}
