import React, { useState } from 'react'
import { PlusLg } from 'react-bootstrap-icons'

import { Button } from 'components'

import { FilterSelectionRow } from './FilterSelectionRow'
import { ResetIcon } from './ResetIcon'
import {
  FILE_UPLOADED,
  INCLUDED,
  createEmptyFilter,
  hasPrimarySelection,
  isFilterComplete,
} from './utils'

// The filter builder: holds the list of filter rows in local state and renders
// them plus the Add / Reset / Apply actions. Frontend only — "Apply" just emits
// the current filter model (no backend / useStatistics wiring).
export const StatisticsFiltersBuilder = ({
  questionOptions = [],
  survey,
  onApply = () => {},
}) => {
  // Start empty: the modal opens showing only "+ Add filter". The first filter
  // section (with the source tabs) appears once the user adds one.
  const [filters, setFilters] = useState([])

  const updateFilter = (id, key, value) => {
    setFilters((prev) =>
      prev.map((filter) => {
        if (filter.id !== id) return filter

        // Switching source resets the row's field values but keeps id + join.
        if (key === 'source') {
          return {
            ...createEmptyFilter(),
            id: filter.id,
            join: filter.join,
            source: value,
          }
        }

        const next = { ...filter, [key]: value }

        // Changing the question records its kind (drives the value UI) and
        // clears every previous value — the new question may be a different kind.
        if (key === 'questionQid') {
          next.questionKind =
            questionOptions.find((q) => q.value === value)?.kind ?? null
          next.answerCodes = []
          next.numberMin = ''
          next.numberMax = ''
          next.dateFrom = null
          next.dateTo = null
          next.textValue = ''
          next.subquestion = null
          next.row = null
          next.column = null
          next.column2 = null
          next.fileUploaded = FILE_UPLOADED.YES
        }

        // Switching "File uploaded" to No clears the title — there's no title to
        // match when filtering for respondents who did not upload anything.
        if (key === 'fileUploaded' && value === FILE_UPLOADED.NO) {
          next.textValue = ''
        }

        // Changing the sub-question clears its previous value.
        if (key === 'subquestion') {
          next.textValue = ''
          next.numberMin = ''
          next.numberMax = ''
        }

        // Changing the survey-data field clears the previous field's values.
        if (key === 'surveyField') {
          next.included = INCLUDED.ALL
          next.dateFrom = null
          next.dateTo = null
          next.numberMin = ''
          next.numberMax = ''
          next.languages = []
        }

        return next
      })
    )
  }

  const addFilter = () => setFilters((prev) => [...prev, createEmptyFilter()])

  const removeFilter = (id) => {
    // Allowed to go back to empty (the "+ Add filter" only state).
    setFilters((prev) => prev.filter((filter) => filter.id !== id))
  }

  const resetFilters = () => setFilters([])

  const applyFilters = () => {
    // Frontend-only scope
    console.log('Statistics filters applied:', filters)
    onApply(filters)
  }

  // Footer/button state:
  // - Apply is always shown once a row exists, enabled only when every row is
  //   complete (a question needs at least one answer option).
  // - Reset appears once any row has its primary selection.
  // - "+ Add filter" shows when there are no incomplete rows — true when empty
  //   (add the first) and again once all rows are complete.
  const allComplete = filters.every(isFilterComplete)
  const showReset = filters.some(hasPrimarySelection)
  const canApply = filters.length > 0 && allComplete

  return (
    <div className="responses-statistics-filters-builder">
      {filters.map((filter, index) => (
        <FilterSelectionRow
          key={filter.id}
          filter={filter}
          index={index}
          questionOptions={questionOptions}
          survey={survey}
          onUpdate={updateFilter}
          onRemove={removeFilter}
        />
      ))}

      {allComplete && (
        <Button
          variant="link"
          className="responses-statistics-filters-add p-0"
          onClick={addFilter}
        >
          <PlusLg className="me-2" />
          {t('Add filter')}
        </Button>
      )}

      {filters.length > 0 && (
        <div className="responses-statistics-filters-footer">
          {showReset && (
            <Button
              variant="link"
              className="responses-statistics-filters-reset p-0"
              onClick={resetFilters}
            >
              <ResetIcon className="me-2" />
              {t('Reset filter')}
            </Button>
          )}
          <Button variant="primary" onClick={applyFilters} disabled={!canApply}>
            {t('Apply filter')}
          </Button>
        </div>
      )}
    </div>
  )
}
