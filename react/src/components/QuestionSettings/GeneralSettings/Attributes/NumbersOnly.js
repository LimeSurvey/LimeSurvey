import { ToggleButtons } from 'components'

export const NumbersOnly = ({ numbers_only: { value }, update }) => {
  return (
    <>
      <ToggleButtons
        labelText="Numbers only"
        id="numbers-only-attribute-question-settings"
        onChange={(isNumbersOnly) => update({ value: isNumbersOnly })}
        value={value || '0'}
        toggleOptions={[
          { name: 'On', value: '1' },
          { name: 'Off', value: '0' },
        ]}
      />
    </>
  )
}
