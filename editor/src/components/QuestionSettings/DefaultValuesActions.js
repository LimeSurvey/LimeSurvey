import React, { useState } from 'react'

import { Button } from 'react-bootstrap'
import { ArrowDownIcon, ArrowUpIcon, InformationIcon } from 'components/icons'

/**
 * Renders expandable explanatory content for a default-values action.
 * @param {Object} props Component properties.
 * @param {React.ReactNode} props.children Explanatory content.
 * @returns {JSX.Element} Expandable details control.
 */
const Details = ({ children }) => {
  const [isOpen, setIsOpen] = useState(false)

  return (
    <div className="default-values-details">
      <button
        type="button"
        className="default-values-details-toggle"
        aria-expanded={isOpen}
        onClick={() => setIsOpen((open) => !open)}
      >
        <InformationIcon />
        <span>{isOpen ? t('Hide details') : t('Learn more')}</span>
        {isOpen ? <ArrowUpIcon /> : <ArrowDownIcon />}
      </button>
      {isOpen && <div className="default-values-details-text">{children}</div>}
    </div>
  )
}

/**
 * Renders the actions for saving and clearing question attribute defaults.
 * @param {Object} props Component properties.
 * @param {Function} props.update Handles a default-values action.
 * @param {boolean} props.hasDefaultAttributeValues Whether saved defaults exist.
 * @param {boolean} props.activeDisabled Whether the actions are disabled.
 * @returns {JSX.Element} Default-values action controls.
 */
export const DefaultValuesActions = ({
  update,
  hasDefaultAttributeValues = false,
  activeDisabled = false,
}) => {
  return (
    <div className="default-values-actions">
      <div>
        <Button
          id="save-as-default-values"
          className="w-100 justify-content-center"
          variant="secondary"
          onClick={() => update({ saveAsDefault: true })}
          disabled={activeDisabled}
        >
          {t('Save as default values')}
        </Button>
        <Details>
          {t(
            'The current attribute values will be saved as defaults for this question type. They will be automatically applied to future questions of the same type.'
          )}
        </Details>
      </div>
      {hasDefaultAttributeValues && (
        <div>
          <Button
            id="clear-default-values"
            className="w-100 justify-content-center"
            variant="secondary"
            onClick={() => update({ clearDefault: true })}
            disabled={activeDisabled}
          >
            {t('Clear default values')}
          </Button>
          <Details>
            {t(
              "This will clear the default attribute values for this question type. They won't be applied to future questions of this type."
            )}
          </Details>
        </div>
      )}
    </div>
  )
}
