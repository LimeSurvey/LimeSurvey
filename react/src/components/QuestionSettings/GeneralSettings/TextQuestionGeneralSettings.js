import { QuestionTypeInfo } from '../../QuestionTypes'

import { MaximumCharacters, NumbersOnly } from './Attributes'

export const TextQuestionGeneralSettings = ({ question, handleUpdate }) => {
  return (
    <>
      {question.type === QuestionTypeInfo.SHORT_TEXT.type && (
        <div className="mt-3">
          <NumbersOnly
            numbers_only={question.attributes.numbers_only || {}}
            update={(changes) =>
              handleUpdate({
                numbers_only: {
                  ...question.attributes.numbers_only,
                  ...changes,
                },
              })
            }
          />
        </div>
      )}
      <div className="mt-3">
        <MaximumCharacters
          maximum_chars={{ ...question.attributes.maximum_chars }}
          update={(changes) =>
            handleUpdate({
              maximum_chars: {
                ...question.attributes.maximum_chars,
                ...changes,
              },
            })
          }
        />
      </div>
    </>
  )
}
