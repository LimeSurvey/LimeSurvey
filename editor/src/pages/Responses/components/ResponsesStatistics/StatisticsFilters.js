import { useCallback, useEffect, useState } from 'react'
import { debounce } from 'lodash'

import { Badge, Button, Select } from 'components'
import { SideBarHeader } from 'components/SideBar'
import { CloseIcon } from 'components/icons'

const MAX_SEARCH_TERMS = 10

export const StatisticsFilters = ({ filters, setFilters, setShowFilters }) => {
  const [_filters, _setFilters] = useState(filters)
  const [error, setError] = useState(null)
  const [searchInput, setSearchInput] = useState('')

  const searchTerms = _filters.search ?? []

  const addSearchTerm = () => {
    const term = searchInput.trim()
    if (
      !term ||
      searchTerms.includes(term) ||
      searchTerms.length >= MAX_SEARCH_TERMS
    ) {
      return
    }
    _setFilters({ ..._filters, search: [...searchTerms, term] })
    setSearchInput('')
  }

  const removeSearchTerm = (term) => {
    const next = searchTerms.filter((item) => item !== term)
    const rest = { ..._filters }
    delete rest.search
    _setFilters(next.length ? { ...rest, search: next } : rest)
  }

  const handleOnFilterChange = useCallback(
    debounce((filter) => {
      setFilters(filter)
    }, 1000),
    [setFilters]
  )

  useEffect(() => {
    if (_filters.maxId && _filters.minId && _filters.maxId < _filters.minId) {
      setError(t('Max ID must be greater than Min ID'))
      handleOnFilterChange.cancel()
    } else if (
      _filters.minId &&
      _filters.maxId &&
      _filters.minId > _filters.maxId
    ) {
      setError(t('Min ID must be less than Max ID'))
      handleOnFilterChange.cancel()
    } else if (_filters.maxId && _filters.maxId < 0) {
      setError(t('Max ID must be a positive integer'))
      handleOnFilterChange.cancel()
    } else if (_filters.minId && _filters.minId < 0) {
      setError(t('Min ID must be a positive integer'))
      handleOnFilterChange.cancel()
    } else {
      setError(null)
      handleOnFilterChange(_filters)
    }
  }, [_filters])

  return (
    <div className={'right-side-bar bg-white sidebar active-side-bar'}>
      <SideBarHeader className="right-side-bar-header primary">
        <div className="focused-question-code">{t('Filter responses')}</div>
        <Button
          variant="link"
          style={{ padding: 0 }}
          onClick={() => setShowFilters(false)}
        >
          <CloseIcon className="text-black fill-current" />
        </Button>
      </SideBarHeader>
      <div className="px-3 mt-1">
        <div>
          <p className="label-s mb-1">{t('Completion state')}</p>
          <div>
            <Select
              options={[
                { label: t('All responses'), value: '' },
                { label: t('Complete only'), value: true },
                { label: t('Incomplete only'), value: false },
              ]}
              update={(value) => _setFilters({ ..._filters, completed: value })}
              defaultValue={filters.completed}
            />
          </div>
        </div>
        <div className="mt-2">
          <p className="label-s mb-1">{t('Response ID')}</p>
          <div>
            <div className="d-flex gap-1" onClick={(e) => e.stopPropagation()}>
              <input
                type="number"
                className="form-control form-control-sm"
                placeholder={t('Min')}
                onChange={({ target: { value } }) =>
                  _setFilters({
                    ..._filters,
                    minId: value === '' ? '' : +value,
                  })
                }
                min={0}
                pattern="^[0-9]*$"
                defaultValue={filters.minId}
              />
              <input
                type="number"
                className="form-control form-control-sm"
                placeholder={t('Max')}
                onChange={({ target: { value } }) =>
                  _setFilters({
                    ..._filters,
                    maxId: value === '' ? '' : +value,
                  })
                }
                min={0}
                pattern="^[0-9]*$"
                defaultValue={filters.maxId}
              />
            </div>
          </div>
          <p>{error && <span className="text-danger">{error}</span>}</p>
        </div>
        <div className="mt-2">
          <p className="label-s mb-1">{t('Search in answers')}</p>
          <input
            type="text"
            className="form-control form-control-sm"
            placeholder={t('Type a word and press Enter')}
            maxLength={250}
            value={searchInput}
            onChange={({ target: { value } }) => setSearchInput(value)}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                event.preventDefault()
                addSearchTerm()
              }
            }}
          />
          {searchTerms.length > 0 && (
            <div className="responses-statistics-search-terms mt-1">
              {searchTerms.map((term) => (
                <Badge key={term} className="responses-statistics-search-term">
                  <span className="responses-statistics-search-term-label">
                    {term}
                  </span>
                  <button
                    type="button"
                    aria-label={t('Remove search term')}
                    onClick={() => removeSearchTerm(term)}
                  >
                    <i className="ri-close-line"></i>
                  </button>
                </Badge>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
