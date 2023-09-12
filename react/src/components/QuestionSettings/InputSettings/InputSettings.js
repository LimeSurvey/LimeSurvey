import { SettingsWrapper } from 'components/UIComponents'
import { DateTimeFormat } from '../DisplaySettings/Attributes'
import { MinuteStepInterval } from '../DisplaySettings/Attributes/MinuteStepInterval'

export const InputSettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => {
  return (
    <SettingsWrapper title="Input" isAdvanced={isAdvanced}>
      <DateTimeFormat
        condition={{ ...question?.attributes?.dateTimeFormat }}
        update={(changes) => {
          handleUpdate({
            dateTimeFormat: {
              ...question.attributes?.dateTimeFormat,
              ...changes,
            },
          })
        }}
      />
      <div className="mt-3">
        <MinuteStepInterval
          minuteStepInterval={{ ...question?.attributes?.minuteStepInterval }}
          update={(changes) =>
            handleUpdate({
              minuteStepInterval: {
                ...question.attributes?.minuteStepInterval,
                ...changes,
              },
            })
          }
        />
      </div>
    </SettingsWrapper>
  )
}
