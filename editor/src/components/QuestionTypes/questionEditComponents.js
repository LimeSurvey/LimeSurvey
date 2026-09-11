import { getQuestionTypeInfo } from './getQuestionTypeInfo'
import { FivePointChoiceQuestion } from './FivePointChoiceQuestion/FivePointChoiceQuestion'
import { TextQuestion } from './TextQuestion/TextQuestion'
import { RatingQuestion } from './RatingQuestion/RatingQuestion'
import { FileUpload } from './FileUpload/FileUpload'
import { RankingQuestion } from './RankingQuestion/RankingQuestion'
import { RankingAdvancedQuestion } from './RankingAdvancedQuestion/RankingAdvancedQuestion'
import { Equation } from './Equation/Equation'
import { DateTime } from './DateTime/DateTime'
import { GenderQuestion } from './GenderQuestion/GenderQuestion'
import { YesNoQuestion } from './YesNoQuestion/YesNoQuestion'
import { ArrayQuestion } from './ArrayQuestion/ArrayQuestion'
import { OptionQuestionEditMode } from './QuestionModes/OptionQuestionEditMode'
import { TextDisplay } from './TextQuestion/TextDisplay'

/**
 * Maps question theme names to their edit mode components
 * Used in the editor for displaying questions in edit mode
 */
export const questionEditComponents = {
  [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme]:
    OptionQuestionEditMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme]:
    OptionQuestionEditMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme]: OptionQuestionEditMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme]: OptionQuestionEditMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme]:
    OptionQuestionEditMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE.theme]: OptionQuestionEditMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme]:
    OptionQuestionEditMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme]: OptionQuestionEditMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme]:
    OptionQuestionEditMode,
  [getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme]: OptionQuestionEditMode,
  [getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme]:
    OptionQuestionEditMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.theme]:
    FivePointChoiceQuestion,
  [getQuestionTypeInfo().SHORT_TEXT.theme]: TextQuestion,
  [getQuestionTypeInfo().NUMERIC.theme]: TextQuestion,
  [getQuestionTypeInfo().BROWSER_DETECTION.theme]: TextQuestion,
  [getQuestionTypeInfo().LONG_TEXT.theme]: TextQuestion,
  [getQuestionTypeInfo().ARRAY.theme]: ArrayQuestion,
  [getQuestionTypeInfo().ARRAY_NUMBERS.theme]: ArrayQuestion,
  [getQuestionTypeInfo().ARRAY_TEXT.theme]: ArrayQuestion,
  [getQuestionTypeInfo().ARRAY_COLUMN.theme]: ArrayQuestion,
  [getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme]: ArrayQuestion,
  [getQuestionTypeInfo().RATING.theme]: RatingQuestion,
  [getQuestionTypeInfo().FILE_UPLOAD.theme]: FileUpload,
  [getQuestionTypeInfo().RANKING.theme]: RankingQuestion,
  [getQuestionTypeInfo().RANKING_ADVANCED.theme]: RankingAdvancedQuestion,
  [getQuestionTypeInfo().EQUATION.theme]: Equation,
  [getQuestionTypeInfo().DATE_TIME.theme]: DateTime,
  [getQuestionTypeInfo().GENDER.theme]: GenderQuestion,
  [getQuestionTypeInfo().YES_NO.theme]: YesNoQuestion,
  [getQuestionTypeInfo().TEXT_DISPLAY.theme]: TextDisplay,
}
