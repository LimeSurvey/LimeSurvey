import { getPresets, isSameRange } from 'helpers/dateRangeUtils'

export const DateRangePresets = ({
  tempRange,
  dayCounts,
  onPresetSelect,
  onDayCountChange,
}) => {
  const presets = getPresets()
  const hasDayCount = !!(dayCounts.upTo || dayCounts.from)

  return (
    <div className="date-range-presets">
      {presets.map(({ label, range: presetRange }) => (
        <button
          key={label}
          type="button"
          className={`date-range-preset ${
            !hasDayCount && isSameRange(tempRange, presetRange) ? 'active' : ''
          }`}
          onClick={() => onPresetSelect(presetRange)}
        >
          {label}
        </button>
      ))}
      <div className="date-range-day-counts">
        <label className="date-range-days-input">
          <input
            type="number"
            min="1"
            placeholder="-"
            value={dayCounts.upTo}
            onChange={onDayCountChange('upTo')}
          />
          <span>{t('Days up to today')}</span>
        </label>
        <label className="date-range-days-input">
          <input
            type="number"
            min="1"
            placeholder="-"
            value={dayCounts.from}
            onChange={onDayCountChange('from')}
          />
          <span>{t('Days starting today')}</span>
        </label>
      </div>
    </div>
  )
}
