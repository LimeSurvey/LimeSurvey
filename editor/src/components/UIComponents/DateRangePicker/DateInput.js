import { useEffect, useState } from 'react'
import { format, parse, isValid, isBefore, isAfter, startOfDay } from 'date-fns'
import { Calendar3 } from 'react-bootstrap-icons'

export const DateInput = ({
  value,
  inputFormat,
  fallbackFormat,
  minDate,
  maxDate,
  onCommit,
  onIconClick,
  label,
}) => {
  const formatted = value ? format(value, inputFormat) : ''
  const [text, setText] = useState(formatted)

  useEffect(() => setText(formatted), [formatted])

  const commit = () => {
    if (text === formatted) return

    let parsed = parse(text, inputFormat, new Date())
    if (!isValid(parsed)) parsed = parse(text, fallbackFormat, new Date())
    const inBounds =
      isValid(parsed) &&
      !isBefore(parsed, startOfDay(minDate)) &&
      !isAfter(parsed, maxDate)

    if (inBounds) {
      onCommit(parsed)
    } else {
      setText(formatted)
    }
  }

  return (
    <div className="date-range-input">
      <input
        type="text"
        aria-label={label}
        placeholder={inputFormat}
        value={text}
        onChange={(e) => setText(e.target.value)}
        onBlur={commit}
        onKeyDown={(e) => e.key === 'Enter' && commit()}
      />
      <button
        type="button"
        className="date-range-input-icon"
        aria-label={label}
        onClick={() => value && onIconClick(value)}
      >
        <Calendar3 size={14} />
      </button>
    </div>
  )
}
