import { QuestionTypeInfo } from '../../QuestionTypes'
import { Prefix, Suffix } from './Attributes'

export const TextQuestionLayoutSettings = ({ question, handleUpdate }) => {
  return (
    <div>
      {question.type === QuestionTypeInfo.SHORT_TEXT.type && (
        <>
          <div className="mt-3">
            <Prefix
              prefix={question.attributes.prefix || {}}
              update={(changes) =>
                handleUpdate({
                  prefix: {
                    ...question.attributes.prefix,
                    ...changes,
                  },
                })
              }
            />
          </div>
          <div className="mt-3">
            <Suffix
              suffix={question.attributes.suffix || {}}
              update={(changes) =>
                handleUpdate({
                  suffix: {
                    ...question.attributes.suffix,
                    ...changes,
                  },
                })
              }
            />
          </div>
        </>
      )}
    </div>
  )
}
