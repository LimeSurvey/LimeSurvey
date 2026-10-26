import React from 'react'

import { Input } from 'components'
import { getSurveyParticipantAttributes } from 'helpers'

import { FilterSelect } from '../FilterSelect'

// Participant data tab: pick a participant attribute, then enter a value.
export const ParticipantSource = ({ filter, survey, onUpdate }) => {
  const options = getSurveyParticipantAttributes(survey)

  return (
    <div className="responses-statistics-filters-row-body">
      <div className="responses-statistics-filters-row-field">
        <FilterSelect
          options={options}
          value={filter.attribute}
          defaultValue={null}
          placeholder={t('Please select ...')}
          update={(value) => onUpdate('attribute', value)}
        />
      </div>

      {filter.attribute && (
        <div className="responses-statistics-filters-row-field">
          <Input
            placeholder={t('Enter value')}
            value={filter.attributeValue}
            update={(value) => onUpdate('attributeValue', value)}
          />
        </div>
      )}
    </div>
  )
}
