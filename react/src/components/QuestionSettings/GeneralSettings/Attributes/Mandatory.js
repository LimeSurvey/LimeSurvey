import { MandatoryOptions } from 'helpers'
import { ToggleButtons } from 'components'

export const Mandatory = ({ isMandatory, update }) => {
  return (
    <>
      <ToggleButtons
        labelText="Mandatory"
        id="mandatory-attribute-question-settings"
        value={isMandatory ? isMandatory : 'off'}
        onChange={(isMandatory) => update({ mandatory: isMandatory })}
        toggleOptions={[
          { name: 'On', value: MandatoryOptions.ON },
          { name: 'Soft', value: MandatoryOptions.SOFT },
          { name: 'Off', value: MandatoryOptions.OFF },
        ]}
      />
    </>
  )
}
