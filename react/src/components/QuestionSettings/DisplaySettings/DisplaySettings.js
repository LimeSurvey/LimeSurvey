import { SettingsWrapper } from 'components/UIComponents'
import { QuestionTypeInfo } from 'components/QuestionTypes'
import { MaskDisplayOptions } from './MaskDisplayOptions'
import {
  RankingAdvancedDisplaySettings,
  DateTimeDisplaySettings,
} from './QuestionTypes'

export const DisplaySettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => (
  <SettingsWrapper title="Display" isAdvanced={isAdvanced}>
    {question.questionThemeName === QuestionTypeInfo.DATE_TIME.theme && (
      <DateTimeDisplaySettings
        question={question}
        handleUpdate={handleUpdate}
      />
    )}
    {question.questionThemeName === QuestionTypeInfo.RANKING_ADVANCED.theme && (
      <RankingAdvancedDisplaySettings
        question={question}
        handleUpdate={handleUpdate}
      />
    )}
    {(question.questionThemeName === QuestionTypeInfo.GENDER.theme ||
      question.questionThemeName === QuestionTypeInfo.YES_NO.theme) && (
      <MaskDisplayOptions question={question} handleUpdate={handleUpdate} />
    )}
  </SettingsWrapper>
)
