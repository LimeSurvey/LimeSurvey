import { SettingsWrapper } from 'components'
import { QuestionTypeInfo } from '../../QuestionTypes'

import { TextQuestionGeneralSettings } from './TextQuestionGeneralSettings'
import {
  FormFieldText,
  Mandatory,
  QuestionCode,
  QuestionType,
} from './Attributes'
import { QuestionGroup } from './Attributes/QuestionGroup'

export const GeneralSettings = ({
  question: { questionThemeName } = {},
  question = {},
  questionGroups = [],
  language,
  handleUpdate,
  groupIndex,
  questionIndex,
  surveyUpdate,
  surveyId,
  isAdvanced = false,
}) => {
  return (
    <SettingsWrapper title="General" isDefaultOpen isAdvanced={isAdvanced}>
      <QuestionCode
        value={question.title}
        onChange={(title) => handleUpdate({ title }, false)}
      />
      <div className="mt-3">
        <QuestionType
          questionThemeName={questionThemeName}
          update={(type) => handleUpdate({ ...type }, false)}
        />
      </div>
      <div className="mt-3">
        <QuestionGroup
          question={question}
          language={language}
          questionGroups={questionGroups}
          questionIndex={questionIndex}
          groupIndex={groupIndex}
          update={(questionGroups) => {
            surveyUpdate({ questionGroups: [...questionGroups] })
          }}
        />
      </div>
      {questionThemeName !== QuestionTypeInfo.BROWSER_DETECTION.theme && (
        <div className="mt-3">
          <Mandatory
            isMandatory={question.mandatory}
            update={(mandatory) => handleUpdate({ ...mandatory }, false)}
          />
        </div>
      )}
      {(questionThemeName === QuestionTypeInfo.SHORT_TEXT.theme ||
        questionThemeName === QuestionTypeInfo.LONG_TEXT.theme) && (
        <div className="mt-3">
          <TextQuestionGeneralSettings
            handleUpdate={(changes) => handleUpdate(changes)}
            question={question}
          />
        </div>
      )}
      {(questionThemeName === QuestionTypeInfo.SHORT_TEXT.theme ||
        questionThemeName === QuestionTypeInfo.LONG_TEXT.theme ||
        questionThemeName === QuestionTypeInfo.MULTIPLE_SHORT_TEXTS.theme ||
        questionThemeName === QuestionTypeInfo.ARRAY_TEXT.theme) && (
        <div className="mt-3">
          <FormFieldText
            form_field_text={{ ...question.attributes.form_field_text }}
            maximumChars={question.attributes?.maximum_chars?.value}
            update={(changes) =>
              handleUpdate({
                form_field_text: {
                  ...question.attributes.form_field_text,
                  ...changes,
                },
              })
            }
          />
        </div>
      )}
    </SettingsWrapper>
  )
}
