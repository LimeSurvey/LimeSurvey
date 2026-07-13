import React, { useMemo } from 'react'

import { Input } from 'components'

import { FilterSelect } from '../FilterSelect'
import { CheckedToggle, DateRangeField, NumberRangeField } from './fields'

// Question tab: pick a question, then filter by its value
export const QuestionSource = ({ filter, questionOptions = [], onUpdate }) => {
  const selectedQuestion = useMemo(
    () => questionOptions.find((q) => q.value === filter.questionQid) || null,
    [questionOptions, filter.questionQid]
  )

  // Multiselect needs the selected option objects, not just their values.
  const answerValue = useMemo(() => {
    if (!selectedQuestion) return []
    return selectedQuestion.answerOptions.filter((option) =>
      filter.answerCodes.includes(option.value)
    )
  }, [selectedQuestion, filter.answerCodes])

  // The value control shown once a sub-question is chosen.
  const renderSubquestionValue = (kind) => {
    switch (kind) {
      case 'subCheckbox':
        return (
          <CheckedToggle
            id={`checked-${filter.id}`}
            value={filter.checkState}
            onChange={(value) => onUpdate('checkState', value)}
          />
        )
      case 'subText':
        return (
          <div className="responses-statistics-filters-row-field">
            <Input
              placeholder={t('Contains ...')}
              value={filter.textValue}
              update={(value) => onUpdate('textValue', value)}
            />
          </div>
        )
      case 'subNumber':
        return (
          <NumberRangeField
            min={filter.numberMin}
            max={filter.numberMax}
            onMinChange={(value) => onUpdate('numberMin', value)}
            onMaxChange={(value) => onUpdate('numberMax', value)}
          />
        )
      default:
        return null
    }
  }

  const renderValueField = () => {
    switch (selectedQuestion.kind) {
      case 'number':
        return (
          <NumberRangeField
            min={filter.numberMin}
            max={filter.numberMax}
            onMinChange={(value) => onUpdate('numberMin', value)}
            onMaxChange={(value) => onUpdate('numberMax', value)}
          />
        )
      case 'date':
        return (
          <DateRangeField
            from={filter.dateFrom}
            to={filter.dateTo}
            onFromChange={(value) => onUpdate('dateFrom', value)}
            onToChange={(value) => onUpdate('dateTo', value)}
          />
        )
      case 'text':
        return (
          <div className="responses-statistics-filters-row-field">
            <Input
              placeholder={t('Contains ...')}
              value={filter.textValue}
              update={(value) => onUpdate('textValue', value)}
            />
          </div>
        )
      case 'subCheckbox':
      case 'subText':
      case 'subNumber':
        return (
          <>
            <div className="responses-statistics-filters-row-field">
              <FilterSelect
                options={selectedQuestion.subquestions}
                value={filter.subquestion}
                defaultValue={null}
                placeholder={t('Please select sub-question ...')}
                update={(value) => onUpdate('subquestion', value)}
              />
            </div>
            {filter.subquestion != null &&
              renderSubquestionValue(selectedQuestion.kind)}
          </>
        )
      default:
        return (
          <div className="responses-statistics-filters-row-field">
            <FilterSelect
              options={selectedQuestion.answerOptions}
              value={answerValue}
              isMultiselect
              placeholder={t('Please select answer options ...')}
              update={(values) => onUpdate('answerCodes', values)}
            />
          </div>
        )
    }
  }

  return (
    <div className="responses-statistics-filters-row-body">
      <div className="responses-statistics-filters-row-field">
        <FilterSelect
          options={questionOptions}
          value={filter.questionQid}
          defaultValue={null}
          placeholder={t('Please select ...')}
          update={(value) => onUpdate('questionQid', value)}
        />
      </div>

      {selectedQuestion && renderValueField()}
    </div>
  )
}
