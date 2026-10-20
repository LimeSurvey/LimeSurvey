import React from 'react'

import { Input } from 'components'
import { getSurveyParticipantAttributes } from 'helpers'

import { FilterSelect } from '../FilterSelect'

// "Completed" is already covered by the Response data tab.
const EXCLUDED_ATTRIBUTES = ['{TOKEN:COMPLETED}']

// Integer columns, matched exactly by the backend, so they take a number.
const NUMERIC_ATTRIBUTES = ['{TOKEN:USESLEFT}', '{TOKEN:REMINDERCOUNT}']

// Participant data tab: pick a participant attribute, then enter a value.
export const ParticipantSource = ({ filter, survey, onUpdate }) => {
  const encryptedAttributes = Object.entries(
    survey?.attributeDescriptions || {}
  )
    .filter(([, description]) => description?.encrypted === 'Y')
    .map(([key]) => `{TOKEN:${key.toUpperCase()}}`)
  const options = getSurveyParticipantAttributes(survey).filter(
    ({ value }) =>
      !EXCLUDED_ATTRIBUTES.includes(value) &&
      !encryptedAttributes.includes(value)
  )
  const isNumeric = NUMERIC_ATTRIBUTES.includes(filter.attribute)

  const updateAttribute = (attribute) => {
    onUpdate('attribute', attribute)
    if (
      NUMERIC_ATTRIBUTES.includes(attribute) &&
      !/^-?\d+$/.test(String(filter.attributeValue ?? '').trim())
    ) {
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
    </div>
  )
}
