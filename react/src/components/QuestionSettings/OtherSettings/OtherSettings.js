import { SettingsWrapper } from 'components/UIComponents'
import { FileUploadSettings } from './QuestionTypes'

export const OtherSettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => (
  <SettingsWrapper title="Other" isAdvanced={isAdvanced}>
    <FileUploadSettings question={question} handleUpdate={handleUpdate} />
  </SettingsWrapper>
)
