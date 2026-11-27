import React, { useLayoutEffect, useMemo, useRef, useState } from 'react'
import classNames from 'classnames'

import { TooltipContainer } from 'components'

import { isFilterComplete } from '../ResponsesStatistics/StatisticsFiltersModal/utils'
import { countChipsOnOneLine } from './countChipsOnOneLine'
import { describeFilter } from './describeFilter'

const CHIP_GAP = 8

const ChipTooltip = ({ chip }) => (
  <div className="responses-active-filters-tooltip-content">
    <div>{chip.heading}</div>
    {chip.text && <div>{chip.text}</div>}
    {chip.lines.length > 0 && (
      <ul>
        {chip.lines.map((line, index) => (
          <li key={index}>{line}</li>
        ))}
      </ul>
    )}
  </div>
)

export const ActiveFilters = ({
  appliedFilters = [],
  questionOptions = [],
  survey,
  className,
}) => {
  const chips = useMemo(
    () =>
      appliedFilters.filter(isFilterComplete).map((filter) => ({
        id: filter.id,
        ...describeFilter(filter, { questionOptions, survey }),
      })),
    [appliedFilters, questionOptions, survey]
  )
  const [expanded, setExpanded] = useState(false)
  const [visibleCount, setVisibleCount] = useState(chips.length)
  const measureRef = useRef(null)

  useLayoutEffect(() => {
    const measure = measureRef.current
    if (!measure) return undefined

    const update = () => {
      if (!measure.clientWidth) {
        setVisibleCount(chips.length)
        return
      }
      const chipWidths = [
        ...measure.querySelectorAll('.responses-active-filters-chip'),
      ].map((chip) => chip.offsetWidth)
      const toggle = measure.querySelector('.responses-active-filters-toggle')
      setVisibleCount(
        countChipsOnOneLine(
          chipWidths,
          measure.clientWidth,
          toggle?.offsetWidth ?? 0,
          CHIP_GAP
        )
      )
    }

    update()
    const observer = new ResizeObserver(update)
    observer.observe(measure)
    return () => observer.disconnect()
  }, [chips])

  if (!chips.length) {
    return null
  }

  const hasMore = visibleCount < chips.length
  const isExpanded = expanded && hasMore
  const shownChips = isExpanded ? chips : chips.slice(0, visibleCount)

  return (
    <div className={classNames('responses-active-filters', className)}>
      <div
        ref={measureRef}
        className="responses-active-filters-measure"
        aria-hidden="true"
      >
        {chips.map((chip) => (
          <span key={chip.id} className="responses-active-filters-chip">
            {chip.label}
          </span>
        ))}
        <button
          type="button"
          className="responses-active-filters-toggle"
          tabIndex={-1}
        >
          {t('Show more')}
        </button>
      </div>
      <ul
        className="responses-active-filters-list"
        aria-label={t('Active filters')}
      >
        {shownChips.map((chip) => (
          <li key={chip.id}>
            <TooltipContainer
              tip={<ChipTooltip chip={chip} />}
              placement="bottom"
              tooltipClassName="responses-active-filters-tooltip"
            >
              <span className="responses-active-filters-chip" tabIndex={0}>
                {chip.label}
              </span>
            </TooltipContainer>
          </li>
        ))}
        {hasMore && (
          <li>
            <button
              type="button"
              className="responses-active-filters-toggle"
              aria-expanded={isExpanded}
              onClick={() => setExpanded(!isExpanded)}
            >
              {isExpanded ? t('Show less') : t('Show more')}
            </button>
          </li>
        )}
      </ul>
    </div>
  )
}
