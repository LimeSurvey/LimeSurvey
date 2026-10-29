import React from 'react'

import { Input } from 'components'
import { getSurveyParticipantAttributes } from 'helpers'

import { FilterSelect } from '../FilterSelect'
import { EncryptedHint } from './fields'

// "Completed" is already covered by the Response data tab.
const EXCLUDED_ATTRIBUTES = ['{TOKEN:COMPLETED}']

// Integer columns, matched exactly by the backend, so they take a number.
const NUMERIC_ATTRIBUTES = ['{TOKEN:USESLEFT}', '{TOKEN:REMINDERCOUNT}']

const isNumericAttribute = (attribute) => NUMERIC_ATTRIBUTES.includes(attribute)

// Participant data tab: pick a participant attribute, then enter a value.
export const ParticipantSource = ({ filter, survey, onUpdate }) => {
  // Core fields (first name, last name, email) and custom attributes alike.
  const encryptedAttributes = (
    survey?.participantEncryptedAttributes || []
  ).map((name) => `{TOKEN:${name.toUpperCase()}}`)
  const options = getSurveyParticipantAttributes(survey).filter(
    ({ value }) => !EXCLUDED_ATTRIBUTES.includes(value)
  )
  const isNumeric = isNumericAttribute(filter.attribute)
  const isEncrypted = encryptedAttributes.includes(filter.attribute)

  const updateAttribute = (attribute) => {
    onUpdate('attribute', attribute)
    // A number and a text value do not carry over to each other.
    if (isNumericAttribute(attribute) !== isNumeric) {
      onUpdate('attributeValue', '')
    }
  }

  return (
    <div className="responses-statistics-filters-row-body">
      <div className="responses-statistics-filters-row-field">
        <FilterSelect
          options={options}
          value={filter.attribute}
          defaultValue={null}
          placeholder={t('Please select ...')}
          update={updateAttribute}
        />
      </div>

      {filter.attribute && (
        <div className="responses-statistics-filters-row-field">
          <Input
            type={isNumeric ? 'number' : 'text'}
            placeholder={t('Enter value')}
            value={filter.attributeValue}
            update={(value) => onUpdate('attributeValue', value)}
          />
        </div>
      )}
      {filter.attribute && isEncrypted && <EncryptedHint />}
    </div>
  )
}
