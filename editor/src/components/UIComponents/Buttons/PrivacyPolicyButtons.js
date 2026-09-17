import ToggleButton from 'react-bootstrap/ToggleButton'
import { Form } from 'react-bootstrap'
import { PrivacyMandatoryInlineIcon } from 'components/icons/PrivacyMandatoryInlineIcon'
import { PrivacyMandatoryPopUpIcon } from 'components/icons/PrivacyMandatoryPopUpIcon'
import { PrivacyMandatoryNoIcon } from 'components/icons/PrivacyMandatoryNoIcon'

export const PrivacyPolicyButtons = ({
  value,
  update = () => {},
  labelText,
}) => {
  const PRIVACY_POLICY_OPTIONS = [
    {
      value: 1,
      label: t('Inline'),
      Icon: PrivacyMandatoryInlineIcon,
      testId: 'inline-privacy-btn',
    },
    {
      value: 2,
      label: t('Pop-Up'),
      Icon: PrivacyMandatoryPopUpIcon,
      testId: 'popup-privacy-btn',
    },
    {
      value: 0,
      label: t('No'),
      Icon: PrivacyMandatoryNoIcon,
      testId: 'no-privacy-btn',
    },
  ]

  return (
    <div>
      {labelText && (
        <Form.Label data-testid="privacy-policy-buttons-label-text">
          {labelText}
        </Form.Label>
      )}
      <div className="lime-svg-btn-group lime-privacy-policy-btn-group d-flex">
        {PRIVACY_POLICY_OPTIONS.map(
          ({ value: optionValue, label, Icon, testId }) => (
            <div key={optionValue} data-testid={`option-${optionValue}`}>
              <ToggleButton
                id={`privacy-policy-option-${optionValue}`}
                name="privacy-policy-display-mode"
                type="radio"
                variant="outline-lime-svg"
                checked={value === optionValue}
                onClick={() => update(optionValue)}
                className="d-flex flex-column align-items-center"
                data-testid={testId}
              >
                <Icon />
                <span className="privacy-policy-btn-label">{label}</span>
              </ToggleButton>
            </div>
          )
        )}
      </div>
    </div>
  )
}
