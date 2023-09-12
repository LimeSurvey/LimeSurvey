import { SettingsWrapper } from 'components/UIComponents'
import { QuestionTypeInfo } from '../../QuestionTypes'

import { ImageSettings } from './ImageSettings/ImageSettings'
import { HideQuestion } from './Attributes'
import { RatingLayoutSettings } from './RatingLayoutSettings'

export const LayoutSettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => {
  return (
    <SettingsWrapper title="Layout" isAdvanced={isAdvanced}>
      <ImageSettings
        handleUpdate={handleUpdate}
        imageAlign={question.attributes?.imageAlign}
        imageBrightness={question.attributes?.imageBrightness || 0}
        image={question.attributes?.image}
      />
      {question.questionThemeName === QuestionTypeInfo.RATING.theme && (
        <RatingLayoutSettings question={question} handleUpdate={handleUpdate} />
      )}
      {question.questionThemeName ===
        QuestionTypeInfo.BROWSER_DETECTION.theme && (
        <div className="mt-3">
          <HideQuestion
            hide_question={question.attributes?.hide_question || {}}
            update={(changes) =>
              handleUpdate({
                hide_question: {
                  ...question.attributes?.hide_question,
                  ...changes,
                },
              })
            }
          />
        </div>
      )}
      {/* <div className="mt-3">
        <CssClasses
          cssclass={question.attributes?.cssclass || {}}
          update={(changes) =>
            handleUpdate({
              cssclass: {
                ...question.attributes?.cssclass,
                ...changes,
              },
            })
          }
        />
      </div> */}
      {/* {(question.questionThemeName === QuestionTypeInfo.SHORT_TEXT.theme ||
        question.questionThemeName === QuestionTypeInfo.LONG_TEXT.theme) && (
        <TextQuestionLayoutSettings
          handleUpdate={handleUpdate}
          question={question}
        />
      )} */}
    </SettingsWrapper>
  )
}
