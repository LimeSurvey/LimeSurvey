import React from 'react'

import { FilterSelect } from '../FilterSelect'

import { FIELD_KIND, getSurveyDataFieldOptions, getSurveyFieldKind } from '../utils'
import {
  DateRangeField,
  IncludedToggle,
  LanguageMultiSelect,
  NumberRangeField,
} from './fields'

// Survey data tab: pick a field, then render the control that fits it.
// The field → control mapping lives in utils/surveyDataFields, so this stays a
// thin switch over the leaf field components.
export const SurveyDataSource = ({ filter, survey, onUpdate }) => {
  const kind = getSurveyFieldKind(filter.surveyField)

  return (
    <div className="responses-statistics-filters-row-body">
      <div className="responses-statistics-filters-row-field">
        <FilterSelect
          options={getSurveyDataFieldOptions()}
          value={filter.surveyField}
          defaultValue={null}
          placeholder={t('Please select ...')}
          update={(value) => onUpdate('surveyField', value)}
        />
      </div>

      {kind === FIELD_KIND.INCLUDED && (
        <IncludedToggle
          id={`included-${filter.id}`}
          value={filter.included}
          onChange={(value) => onUpdate('included', value)}
        />
      )}

      {kind === FIELD_KIND.DATE_RANGE && (
        <DateRangeField
          from={filter.dateFrom}
          to={filter.dateTo}
          onFromChange={(value) => onUpdate('dateFrom', value)}
          onToChange={(value) => onUpdate('dateTo', value)}
        />
      )}

      {kind === FIELD_KIND.NUMBER_RANGE && (
        <NumberRangeField
          min={filter.numberMin}
          max={filter.numberMax}
          onMinChange={(value) => onUpdate('numberMin', value)}
          onMaxChange={(value) => onUpdate('numberMax', value)}
        />
      )}

      {kind === FIELD_KIND.LANGUAGE_MULTI && (
        <LanguageMultiSelect
          languages={survey?.languages}
          value={filter.languages}
          onChange={(value) => onUpdate('languages', value)}
        />
      )}
    </div>
  )
}
