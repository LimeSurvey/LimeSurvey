import { useEffect, useState } from 'react'
import { format, parse, isValid, isBefore, isAfter, startOfDay } from 'date-fns'
import { Calendar3 } from 'react-bootstrap-icons'
import classNames from 'classnames'

export const DateInput = ({
  value,
  dateFormat,
  minDate,
  maxDate,
  onCommit,
  onValidityChange = () => {},
  label,
  locale,
  onIconClick,
  iconRef,
}) => {
  const localeOptions = locale ? { locale } : undefined
  const formatted = value ? format(value, dateFormat, localeOptions) : ''
  const [text, setText] = useState(formatted)
  const [isInvalid, setIsInvalid] = useState(false)

  useEffect(() => {
    setText(formatted)
    setIsInvalid(false)
  }, [formatted])

  useEffect(() => {
    onValidityChange(!isInvalid)
  }, [isInvalid, onValidityChange])

  const commit = () => {
    if (text === formatted) return
    if (!text.trim()) {
      setIsInvalid(false)
      if (formatted) onCommit(undefined)
      else setText('')
      return
    }

    const parsed = parse(text.trim(), dateFormat, new Date(), localeOptions)
    const inBounds =
      isValid(parsed) &&
      !isBefore(parsed, startOfDay(minDate)) &&
      !isAfter(parsed, maxDate)

    setIsInvalid(!inBounds)
    if (inBounds) onCommit(parsed)
  }

  return (
    <div
      className={classNames('date-range-input', { 'is-invalid': isInvalid })}
    >
      <input
        type="text"
        aria-label={label}
        aria-invalid={isInvalid}
        title={isInvalid ? t('Invalid date.') : undefined}
        placeholder={format(new Date(), dateFormat, localeOptions)}
        value={text}
        onChange={(e) => {
          setText(e.target.value)
          setIsInvalid(false)
        }}
        onBlur={commit}
        onKeyDown={(e) => e.key === 'Enter' && commit()}
      />
      {onIconClick ? (
        <button
          ref={iconRef}
          type="button"
          className="date-range-input-icon"
          aria-label={t('Open calendar')}
          onClick={onIconClick}
        >
          <Calendar3 size={14} />
        </button>
      ) : (
        <span className="date-range-input-icon">
          <Calendar3 size={14} />
        </span>
      )}
    </div>
  )
}
