import { waitFor } from '@storybook/test'

import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { ATTRIBUTES } from './attributes'
import { dateTimeTests } from './dateTimeTests'
import { genderTests } from './genderTests'
import { ratingTests } from './ratingTest'
import { shortTextTests } from './shortTextTests'
import { longTextTests } from './longTestTests'
import { radioListTests } from './radioListTests'
import { radioListWithComments } from './radioListWithCommentsTests'
import { singleChoiceDropdownTests } from './singleChoiceDropdownTests'
import { singleChoiceButtonsTests } from './singleChoiceButtonsTests'
import { fivePointChoice } from './fivePointChoice'
import {
  multipleChoiceTests,
  multipleChoiceWithCommentsTests,
  multipleChoiceButtonsTests,
  equationTests,
  yesNoTests,
  multipleNumericalInputTests,
  arrayTests,
  arrayNumbersTests,
  arrayTextTests,
  arrayColumnTests,
  arrayDualScaleTests,
  fileUploadTests,
} from './functions'

// Common attributes
const attributes = ATTRIBUTES
attributes.general['question-code'].value = true
attributes.general['question-type'].value = true
attributes.general['mandatory'].value = true
attributes.display['add-image-or-video'].value = true
attributes.logic['condition-attribute-input'].value = true
attributes.statistics['display-chart'].value = true

const OPTIONS = {
  SKIP_HEADER: false,
  SKIP_MAIN: false,
  SKIP_FOOTER: false,
  SKIP_FINISH: false,
  SKIP_ATTRIBUTES: false,
}

export async function handleTests(
  step,
  canvas,
  question,
  type,
  attrs = attributes,
  options = OPTIONS
) {
  await waitFor(() => canvas.getByTestId('story-wrapper'), { timeout: 10000 })

  if (!options.SKIP_ATTRIBUTES) {
    const attributes = { ...ATTRIBUTES, ...attrs }

    if (question.attributes.image) {
      attributes.display['align-buttons-label-text'].value = true
      attributes.display['image-or-video-edit-delete'].value = true
      attributes.display['alt-text'].value = true
    }
  }

  if (!options.SKIP_MAIN) {
    if (type === getQuestionTypeInfo().MULTIPLE_CHOICE)
      await multipleChoiceTests(step, canvas)
    else if (type === getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS)
      await multipleChoiceWithCommentsTests(step, canvas)
    else if (type === getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS)
      await multipleChoiceButtonsTests(step, canvas)
    else if (type === getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO)
      await radioListTests(step, canvas)
    else if (
      type === getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT
    )
      await radioListWithComments(step, canvas)
    else if (type === getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN)
      await singleChoiceDropdownTests(step, canvas)
    else if (type === getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS)
      await singleChoiceButtonsTests(step, canvas)
    else if (type === getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE)
      await fivePointChoice(step, canvas)
    else if (type === getQuestionTypeInfo().SHORT_TEXT)
      await shortTextTests(step, canvas)
    else if (type === getQuestionTypeInfo().LONG_TEXT)
      await longTextTests(step, canvas)
    else if (type === getQuestionTypeInfo().ARRAY)
      await arrayTests(step, canvas)
    else if (type === getQuestionTypeInfo().ARRAY_NUMBERS)
      await arrayNumbersTests(step, canvas)
    else if (type === getQuestionTypeInfo().ARRAY_TEXT)
      await arrayTextTests(step, canvas)
    else if (type === getQuestionTypeInfo().ARRAY_COLUMN)
      await arrayColumnTests(step, canvas)
    else if (type === getQuestionTypeInfo().ARRAY_DUAL_SCALE)
      await arrayDualScaleTests(step, canvas)
    else if (type === getQuestionTypeInfo().RATING)
      await ratingTests(step, canvas)
    else if (type === getQuestionTypeInfo().EQUATION)
      await equationTests(step, canvas)
    else if (type === getQuestionTypeInfo().FILE_UPLOAD)
      await fileUploadTests(step, canvas)
    else if (type === getQuestionTypeInfo().DATE_TIME)
      await dateTimeTests(step, canvas)
    else if (type === getQuestionTypeInfo().GENDER)
      await genderTests(step, canvas)
    else if (type === getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS)
      await multipleNumericalInputTests(step, canvas)
    else if (type === getQuestionTypeInfo().YES_NO)
      await yesNoTests(step, canvas, { qid: question.qid })
  }
}
