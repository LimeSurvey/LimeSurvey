import React from 'react'

// Shared "Distribution by choice" legend used by the segmented charts
// (stacked bar, dual-scale stacked bar and doughnut grid).
export const StackedLegend = ({ segments = [] }) => (
  <div className="responses-statistics-stacked-legend">
    <span className="responses-statistics-stacked-legend-title">
      {t('Distribution by choice')}:
    </span>
    <div className="responses-statistics-stacked-legend-items">
      {segments.map((segment) => (
        <span
          key={segment.title}
          className="responses-statistics-stacked-legend-item"
        >
          <span
            className="responses-statistics-stacked-legend-swatch"
            style={{ backgroundColor: segment.color }}
          />
          {segment.title}
        </span>
      ))}
    </div>
  </div>
)
