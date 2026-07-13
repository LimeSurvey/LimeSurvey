import { useState } from 'react'
import classNames from 'classnames'

import { Button } from 'components'
import { TAB_KEYS } from './utils'
import { useParams } from 'react-router-dom'
import { panelItemsKeys } from './Sidebars'
import { StatisticsDetailModal } from './components/ResponsesStatistics/StatisticsDetailModal.js'
import { StatisticsFiltersBuilder } from './components/ResponsesStatistics/StatisticsFiltersModal'

export const ResponsesHeader = ({
  setShowFilters = () => {},
  showFilters,
  setFilters = () => {},
  tabKey,
  survey,
  questionOptions = [],
}) => {
  const { menu } = useParams()
  const [showFilterModal, setShowFilterModal] = useState(false)

  // The new condition-designer filter UI (statistics tab only) lives in a modal.
  const isStatistics = tabKey === TAB_KEYS.STATISTICS

  if (menu === panelItemsKeys.overview) {
    return null
  }

  return (
    <div className="d-flex justify-content-between">
      <div
        className={classNames('d-flex gap-2 align-items-center', {
          'opacity-0 disabled': tabKey === TAB_KEYS.OVERVIEW,
        })}
      >
        <div>
          <Button
            className={`btn filter-button`}
            onClick={() => setShowFilters(!showFilters)}
            variant="light"
          >
            {showFilters ? (
              <i className="ri-eye-off-line me-2"></i>
            ) : (
              <i className="ri-filter-2-line me-2"></i>
            )}
            {showFilters ? t('Hide filters') : t('Filter responses')}
          </Button>
        </div>
        <div>
          <Button
            className={`btn filter-button`}
            onClick={() => {
              setFilters({})
              setShowFilters(false)
            }}
            variant="light"
          >
            <i className="ri-filter-off-line me-2"></i>
            {t('Clear filters')}
          </Button>
        </div>
        {isStatistics && (
          <div>
            <Button
              className={`btn filter-button`}
              onClick={() => setShowFilterModal(true)}
              variant="light"
            >
              <i className="ri-filter-2-line me-2"></i>
              {t('Filters')}
            </Button>
          </div>
        )}
      </div>
      {isStatistics && (
        <StatisticsDetailModal
          show={showFilterModal}
          onHide={() => setShowFilterModal(false)}
          title={t('Filter')}
          modalClassname="responses-statistics-filters-modal"
        >
          <div className="responses-statistics-filters-modal-body">
            <StatisticsFiltersBuilder
              survey={survey}
              questionOptions={questionOptions}
            />
          </div>
        </StatisticsDetailModal>
      )}
    </div>
  )
}
