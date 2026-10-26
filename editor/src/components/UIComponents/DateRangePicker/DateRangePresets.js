import classNames from 'classnames'

const DAY_COUNT_LABELS = {
  upTo: () => t('Days up to today'),
  from: () => t('Days starting today'),
}

export const DateRangePresets = ({
  presets,
  trailingPresets = [],
  dayCountKeys,
  isPresetActive,
  dayCounts,
  onPresetSelect,
  onDayCountChange,
}) => {
  const renderPreset = (preset) => (
    <button
      key={preset.key ?? preset.label}
      type="button"
      className={classNames('date-range-preset', {
        active: isPresetActive(preset),
      })}
      onClick={() => onPresetSelect(preset)}
    >
      {preset.label}
    </button>
  )

  return (
    <div className="date-range-presets">
      {presets.map(renderPreset)}
      <div className="date-range-day-counts">
        {dayCountKeys.map((key) => (
          <label key={key} className="date-range-days-input">
            <input
              type="number"
              min="1"
              placeholder="-"
              value={dayCounts[key]}
              onChange={onDayCountChange(key)}
            />
            <span>{DAY_COUNT_LABELS[key]()}</span>
          </label>
        ))}
      </div>
      {trailingPresets.length > 0 && (
        <div className="date-range-trailing-presets">
          {trailingPresets.map(renderPreset)}
        </div>
      )}
    </div>
  )
}
