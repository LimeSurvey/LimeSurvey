import { ToggleButtons } from 'components/UIComponents'

export const SaveAsDefault = ({ save_as_default: { value }, update }) => {
  return (
    <>
      <p className="right-side-bar-header">Save As Default</p>
      <ToggleButtons
        name="save-as-default-question-settings"
        isToggled={value}
        callBack={(value) => update({ value })}
      />
    </>
  )
}
