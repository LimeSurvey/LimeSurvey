import classNames from 'classnames'
import { ChevronDown, ChevronUp } from 'react-bootstrap-icons'

export const DateRangePresets = ({
  presets,
  trailingPresets,
  isPresetActive,
  daysUpToToday,
  onPresetSelect,
  onDaysUpToTodayChange,
}) => {
  const stepDays = (step) =>
    onDaysUpToTodayChange({
      target: {
        value: String(Math.max(1, (parseInt(daysUpToToday, 10) || 0) + step)),
      },
    })

  const renderPreset = (preset) => (
    <button
      key={preset.key}
      type="button"
      aria-pressed={isPresetActive(preset)}
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
          <span className="date-range-days-field">
            <input
              type="number"
              min="1"
              placeholder="-"
              value={daysUpToToday}
              onChange={onDaysUpToTodayChange}
            />
            <span className="date-range-days-steppers" aria-hidden="true">
              <button type="button" tabIndex={-1} onClick={() => stepDays(1)}>
                <ChevronUp size={9} />
              </button>
              <button type="button" tabIndex={-1} onClick={() => stepDays(-1)}>
                <ChevronDown size={9} />
              </button>
            </span>
          </span>
          <span>{t('Days up to today')}</span>
        </label>
      </div>
      <div className="date-range-trailing-presets">
        {trailingPresets.map(renderPreset)}
      </div>
    </div>
  )
}
