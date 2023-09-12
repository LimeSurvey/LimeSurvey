import { ToggleButtons } from 'components/UIComponents'

// todo check if we are changing the right attribute name
export const MaskDisplayOptions = ({ question, handleUpdate }) => (
  <div>
    <ToggleButtons
      id="show-gender-buttons"
      labelText="Display type"
      value={question.displayType || 'buttons'}
      onChange={(displayType) => handleUpdate({ displayType }, false)}
      toggleOptions={[
        { name: 'Buttons', value: 'buttons' },
        { name: 'Radio', value: 'radio' },
      ]}
    />
  </div>
)
