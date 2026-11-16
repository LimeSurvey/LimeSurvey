import React from 'react'

import { FilterSelect } from '../FilterSelect'

// Ranking question value UI (R — Ranking / Ranking advanced): pick a rank
// position and an item, i.e. "which item was ranked at this position". Shape of
// `ranking` (ranks / items) is built in buildQuestionOptions. Reuses the array
// row/column model fields: row = rank position, column = item.
export const RankingFilter = ({ question, filter, onUpdate }) => {
  const { ranking } = question

  return (
    <div className="responses-statistics-filters-row-range">
      <div className="responses-statistics-filters-row-field">
        <FilterSelect
          options={ranking.ranks}
          value={filter.row}
          defaultValue={null}
          placeholder={t('Select rank ...')}
          update={(value) => onUpdate('row', value)}
        />
      </div>
      <div className="responses-statistics-filters-row-field">
        <FilterSelect
          options={ranking.items}
          value={filter.column}
          defaultValue={null}
          placeholder={t('Select answer ...')}
          update={(value) => onUpdate('column', value)}
        />
      </div>
    </div>
  )
}
