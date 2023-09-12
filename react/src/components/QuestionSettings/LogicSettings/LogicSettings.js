import { SettingsWrapper } from 'components/UIComponents'
import { Condition, MinAnswers, MaxAnswers } from './Attributes'

export const LogicSettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => {
  return (
    <SettingsWrapper title="Logic" isAdvanced={isAdvanced}>
      <Condition
        condition={{ ...question?.attributes?.condition }}
        update={(changes) =>
          handleUpdate({
            condition: { ...question.attributes?.condition, ...changes },
          })
        }
      />
      <div className="mt-3">
        <MinAnswers
          minAnswer={{ ...question?.attributes?.minAnswer }}
          update={(changes) =>
            handleUpdate({
              minAnswer: { ...question.attributes?.minAnswer, ...changes },
            })
          }
        />
      </div>
      <div className="mt-3">
        <MaxAnswers
          maxAnswer={{ ...question?.attributes?.maxAnswer }}
          update={(changes) =>
            handleUpdate({
              maxAnswer: { ...question.attributes?.maxAnswer, ...changes },
            })
          }
        />
      </div>
    </SettingsWrapper>
  )
}
