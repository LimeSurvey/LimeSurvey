import { getQuestionTypeInfo } from './getQuestionTypeInfo'
import { getNotSupportedQuestionTypeInfo } from './getNotSupportedQuestionTypeInfo'
import { FivePointChoiceQuestion } from './FivePointChoiceQuestion/FivePointChoiceQuestion'
import { TextQuestion } from './TextQuestion/TextQuestion'
import { RatingQuestion } from './RatingQuestion/RatingQuestion'
import { FileUpload } from './FileUpload/FileUpload'
import { RankingAdvancedQuestion } from './RankingAdvancedQuestion/RankingAdvancedQuestion'
import { Equation } from './Equation/Equation'
import { GenderQuestion } from './GenderQuestion/GenderQuestion'
import { YesNoQuestion } from './YesNoQuestion/YesNoQuestion'
import { OptionQuestionViewMode } from './QuestionModes/OptionQuestionViewMode'
import { TextDisplay } from './TextQuestion/TextDisplay'
import { DateTimePickerComponent } from 'components/UIComponents'
import { ArrayParticipantMode } from 'components/QuestionsParticipantMode'

/**
 * Maps question theme names to their preview/participant mode components
 * Used in the preview/responses view for displaying questions in participant mode
 * Note: This uses different components than the editor view (e.g., ArrayParticipantMode vs ArrayQuestion)
 */
export const questionPreviewComponents = {
  [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme]: OptionQuestionViewMode,
  [getNotSupportedQuestionTypeInfo().LANGUAGE_SWITCH.theme]:
    OptionQuestionViewMode,
  [getNotSupportedQuestionTypeInfo().LIST_DROPDOWN_DEFAULT.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme]: OptionQuestionViewMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE.theme]: OptionQuestionViewMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme]: OptionQuestionViewMode,
  [getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme]: OptionQuestionViewMode,
  [getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme]:
    OptionQuestionViewMode,
  [getNotSupportedQuestionTypeInfo().INPUT_ON_DEMAND.theme]:
    OptionQuestionViewMode,
  [getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.theme]:
    FivePointChoiceQuestion,
  [getQuestionTypeInfo().SHORT_TEXT.theme]: TextQuestion,
  [getQuestionTypeInfo().NUMERIC.theme]: TextQuestion,
  [getQuestionTypeInfo().BROWSER_DETECTION.theme]: TextQuestion,
  [getNotSupportedQuestionTypeInfo().HUGE_FREE_TEXT.theme]: TextQuestion,
  [getQuestionTypeInfo().LONG_TEXT.theme]: TextQuestion,
  [getQuestionTypeInfo().ARRAY.theme]: ArrayParticipantMode,
  [getQuestionTypeInfo().ARRAY_NUMBERS.theme]: ArrayParticipantMode,
  [getQuestionTypeInfo().ARRAY_TEXT.theme]: ArrayParticipantMode,
  [getQuestionTypeInfo().ARRAY_COLUMN.theme]: ArrayParticipantMode,
  [getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme]: ArrayParticipantMode,
  [getNotSupportedQuestionTypeInfo().ARRAY_INCREASE_SAME_DECREASE.theme]:
    ArrayParticipantMode,
  [getNotSupportedQuestionTypeInfo().ARRAY_YES_NO_UNCERTAIN.theme]:
    ArrayParticipantMode,
  [getNotSupportedQuestionTypeInfo().ARRAY_TEN_POINT.theme]:
    ArrayParticipantMode,
  [getNotSupportedQuestionTypeInfo().ARRAY_FIVE_POINT.theme]:
    ArrayParticipantMode,
  [getQuestionTypeInfo().RATING.theme]: RatingQuestion,
  [getQuestionTypeInfo().FILE_UPLOAD.theme]: FileUpload,
  [getQuestionTypeInfo().RANKING.theme]: RankingAdvancedQuestion, // todo: update this to use another component once it's ready.
  [getQuestionTypeInfo().RANKING_ADVANCED.theme]: RankingAdvancedQuestion,
  [getQuestionTypeInfo().EQUATION.theme]: Equation,
  [getQuestionTypeInfo().DATE_TIME.theme]: DateTimePickerComponent,
  [getQuestionTypeInfo().GENDER.theme]: GenderQuestion,
  [getQuestionTypeInfo().YES_NO.theme]: YesNoQuestion,
  [getQuestionTypeInfo().TEXT_DISPLAY.theme]: TextDisplay,
}
