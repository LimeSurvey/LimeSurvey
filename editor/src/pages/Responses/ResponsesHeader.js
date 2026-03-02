import classNames from 'classnames'

import { Button, ToggleButtons } from 'components'
import { TAB_KEYS } from './utils'
import { useParams } from 'react-router-dom'
import { panelItemsKeys } from './Sidebars'

export const ResponsesHeader = ({
  setShowFilters = () => {},
  showFilters,
  setFilters = () => {},
  setTabKey = () => {},
  tabKey,
}) => {
  const { menu } = useParams()
  const options = [
    {
      icon: () => <i className="ri-table-line"></i>,
      value: TAB_KEYS.RESPONSES,
    },
    {
      icon: () => <i className="ri-bar-chart-2-line"></i>,
      value: TAB_KEYS.STATISTICS,
    },
  ]

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
            {showFilters ? 'Hide filters' : 'Filter responses'}
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
      </div>
      <div>
        <ToggleButtons
          value={tabKey}
          update={(value) => setTabKey(value)}
          toggleOptions={options}
        />
      </div>
    </div>
  )
}
