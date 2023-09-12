import { ToggleButtons } from 'components/UIComponents'

// todo: check if we are updating the right attribute.
export const HideQuestion = ({ hide_question: { value }, update }) => {
  return (
    <>
      <ToggleButtons
        name="hide-question-question-settings"
        value={value || '0'}
        onChange={(value) => update({ value })}
        labelText="Hide Question"
        toggleOptions={[
          { name: 'On', value: '1' },
          { name: 'Off', value: '0' },
        ]}
      />
    </>
  )
}
