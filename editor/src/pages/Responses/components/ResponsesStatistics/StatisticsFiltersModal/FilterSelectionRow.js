import React from 'react'

import { Button, ToggleButtons } from 'components'
import { DeleteIcon } from 'components/icons'

import { FilterSelect } from './FilterSelect'
import { JOIN, SOURCE } from './utils'
import { ParticipantSource, QuestionSource, SurveyDataSource } from './sources'

const sourceTabs = () => [
  { name: t('Question'), value: SOURCE.QUESTION },
  { name: t('Survey data'), value: SOURCE.SURVEY_DATA },
  { name: t('Participant data'), value: SOURCE.PARTICIPANT },
]

const joinOptions = () => [
  { value: JOIN.AND, label: t('AND') },
  { value: JOIN.OR, label: t('OR') },
]

// One "Filter selection" row: the AND/OR joiner (except the first row), the
// source tabs, the matching source body, and a delete button. Presentational —
// all state changes bubble up through onUpdate / onRemove.
export const FilterSelectionRow = ({
  filter,
  index,
  questionOptions,
  survey,
  onUpdate,
  onRemove,
}) => {
  // Scope updates to this row so the source components stay unaware of ids.
  const update = (key, value) => onUpdate(filter.id, key, value)

  return (
    <>
      {index > 0 && (
        <div className="responses-statistics-filters-join">
          <FilterSelect
            options={joinOptions()}
            value={filter.join}
            update={(value) => update('join', value)}
          />
        </div>
      )}

      <div className="responses-statistics-filters-row">
        <div className="responses-statistics-filters-row-label">
          {`${t('Filter selection')} #${index + 1}`}
        </div>
        <div className="responses-statistics-filters-row-tabs">
          <ToggleButtons
            id={`filter-source-${filter.id}`}
            toggleOptions={sourceTabs()}
            value={filter.source}
            onChange={(value) => update('source', value)}
          />
        </div>

        <div className="responses-statistics-filters-row-source">
          {filter.source === SOURCE.QUESTION && (
            <QuestionSource
              filter={filter}
              questionOptions={questionOptions}
              onUpdate={update}
            />
          )}
          {filter.source === SOURCE.SURVEY_DATA && (
            <SurveyDataSource
              filter={filter}
              survey={survey}
              onUpdate={update}
            />
          )}
          {filter.source === SOURCE.PARTICIPANT && (
            <ParticipantSource
              filter={filter}
              survey={survey}
              onUpdate={update}
            />
          )}
        </div>
        <Button
          variant="link"
          className="responses-statistics-filters-row-delete p-0"
          onClick={() => onRemove(filter.id)}
          aria-label={t('Remove filter')}
        >
          <DeleteIcon className="fill-current" />
        </Button>
      </div>
    </>
  )
}
