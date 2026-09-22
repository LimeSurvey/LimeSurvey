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
  appliedFilters = [],
  setAppliedFilters = () => {},
}) => {
  const { menu } = useParams()
  const [showFilterModal, setShowFilterModal] = useState(false)

  const isStatistics = tabKey === TAB_KEYS.STATISTICS

  if (menu === panelItemsKeys.overview) {
    return null
  }

  const applyFilters = (filters) => {
    setAppliedFilters(filters)
    setShowFilterModal(false)
  }

  return (
    <div className="d-flex justify-content-between">
      <div
        className={classNames('d-flex gap-2 align-items-center', {
          'opacity-0 disabled': tabKey === TAB_KEYS.OVERVIEW,
        })}
      >
        {!isStatistics && (
          <>
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
          </>
        )}
        <div>
          <Button
            className={`btn filter-button`}
            onClick={() => setShowFilterModal(true)}
            variant="light"
          >
            <i className="ri-filter-2-line me-2"></i>
            {t('Filter')}
          </Button>
        </div>
      </div>
      <StatisticsDetailModal
        show={showFilterModal}
        onHide={() => setShowFilterModal(false)}
        title={
          <h2 className="responses-statistics-modal-title">{t('Filter')}</h2>
        }
        modalClassname="responses-statistics-filters-modal"
      >
        <div className="responses-statistics-filters-modal-body">
          {showFilterModal && (
            <StatisticsFiltersBuilder
              survey={survey}
              questionOptions={questionOptions}
              appliedFilters={appliedFilters}
              onApply={applyFilters}
            />
          )}
        </div>
      </StatisticsDetailModal>
    </div>
  )
}
