import { ToggleButtons } from 'components/UIComponents'

export const ShowComment = ({ showComment: { value }, update }) => (
  <ToggleButtons
    id="show-comment"
    labelText="Show Comment"
    value={value || false}
    onChange={(value) => update({ value })}
  />
)
