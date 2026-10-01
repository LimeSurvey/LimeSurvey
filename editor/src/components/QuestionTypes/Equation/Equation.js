import { ContentEditor } from 'components/UIComponents'

export const Equation = ({ values = [], onValueChange, participantMode }) => {
  const valueInfo = values?.[0]

  if (participantMode) {
    return (
      <ContentEditor
        value={valueInfo.value}
        update={(value) => onValueChange(value, valueInfo.key)}
      />
    )
  }

  return null
}
