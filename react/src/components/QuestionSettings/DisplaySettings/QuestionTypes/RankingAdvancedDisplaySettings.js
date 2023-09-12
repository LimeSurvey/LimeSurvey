import { ToggleButtons } from 'components/UIComponents'

export const RankingAdvancedDisplaySettings = ({ question, handleUpdate }) => (
  <>
    <ToggleButtons
      id="sameHeight"
      labelText="Same height for all answer options"
      value={question?.attributes?.sameHeight?.value || false}
      onChange={(value) =>
        handleUpdate({
          sameHeight: {
            ...question.attributes?.sameHeight,
            value,
          },
        })
      }
    />
  </>
)
