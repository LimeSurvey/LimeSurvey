import classNames from 'classnames'

export const DateRangePresets = ({
  presets,
  trailingPresets,
  isPresetActive,
  daysUpToToday,
  onPresetSelect,
  onDaysUpToTodayChange,
}) => {
  const renderPreset = (preset) => (
    <button
      key={preset.key}
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
        <label className="date-range-days-input">
          <input
            type="number"
            min="1"
            placeholder="-"
            value={daysUpToToday}
            onChange={onDaysUpToTodayChange}
          />
          <span>{t('Days up to today')}</span>
        </label>
      </div>
      <div className="date-range-trailing-presets">
        {trailingPresets.map(renderPreset)}
      </div>
    </div>
  )
}
