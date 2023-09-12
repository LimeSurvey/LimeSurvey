import { SettingsWrapper } from 'components/UIComponents'

import { DisplayChart } from './Attributes'

export const StatisticsSettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => {
  return (
    <SettingsWrapper title="Statistics" isAdvanced={isAdvanced}>
      <DisplayChart
        publicStatistics={{ ...question?.attributes?.public_statistics }}
        update={(changes) =>
          handleUpdate({
            public_statistics: {
              ...question.attributes?.public_statistics,
              ...changes,
            },
          })
        }
      />
    </SettingsWrapper>
  )
}
