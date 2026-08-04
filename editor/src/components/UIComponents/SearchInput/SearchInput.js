import { useEffect, useMemo, useRef, useState } from 'react'
import { Badge, CloseButton } from 'react-bootstrap'

const MAX_TERMS = 10
const TYPING_DEBOUNCE_MS = 600

/**
 * Search-term state for SearchInput consumers: `search` combines the
 * Enter-committed chips with the debounced in-progress input, so results
 * filter while typing.
 */
export const useSearchTerms = () => {
  const [terms, setTerms] = useState([])
  const [typed, setTyped] = useState('')
  const [debouncedTyped, setDebouncedTyped] = useState('')

  useEffect(() => {
    const id = setTimeout(
      () => setDebouncedTyped(typed.trim()),
      TYPING_DEBOUNCE_MS
    )
    return () => clearTimeout(id)
  }, [typed])

  const search = useMemo(
    () =>
      debouncedTyped && !terms.includes(debouncedTyped)
        ? [...terms, debouncedTyped]
        : terms,
    [terms, debouncedTyped]
  )

  return { terms, setTerms, setTyped, search }
}

const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

/**
 * Renders `text` with every (case-insensitive) occurrence of the search terms
 * wrapped in a highlight mark, mirroring the backend's LIKE matching.
 */
export const HighlightedText = ({ text, terms = [] }) => {
  const value = String(text ?? '')
  const clean = terms.filter(Boolean)
  if (!value || !clean.length) {
    return value
  }

  // Split on a capture group: matched terms end up at the odd indices.
  const pattern = new RegExp(`(${clean.map(escapeRegExp).join('|')})`, 'gi')
  return value.split(pattern).map((part, index) =>
    index % 2 === 1 ? (
      <mark key={index} className="search-highlight">
        {part}
      </mark>
    ) : (
      part
    )
  )
}

/**
 * Text input that turns each Enter-committed word into a removable Bootstrap
 * badge kept inside the input frame. Backspace in the empty input removes the
 * last badge. `onTyping` reports the in-progress input so parents can filter
 * while the user types (see useSearchTerms); the filtering itself happens in
 * the backend query.
 */
export const SearchInput = ({
  terms = [],
  onChange,
  onTyping,
  placeholder,
  maxTerms = MAX_TERMS,
}) => {
  const [input, setInput] = useState('')
  const inputRef = useRef(null)

  const setInputValue = (value) => {
    setInput(value)
    onTyping?.(value)
  }

  const addTerm = () => {
    const term = input.trim()
    if (!term || terms.includes(term) || terms.length >= maxTerms) {
      return
    }
    onChange([...terms, term])
    setInputValue('')
  }

  const removeTerm = (term) => {
    onChange(terms.filter((item) => item !== term))
  }

  return (
    <div
      className="form-control form-control-sm search-input"
      onClick={() => inputRef.current?.focus()}
    >
      <i className="ri-search-line search-input-icon"></i>
      {terms.map((term) => (
        <Badge key={term} bg="light" text="dark" className="search-input-badge">
          {term}
          <CloseButton
            aria-label={t('Remove search term')}
            onClick={(event) => {
              event.stopPropagation()
              removeTerm(term)
            }}
          />
        </Badge>
      ))}
      <input
        ref={inputRef}
        type="text"
        maxLength={250}
        value={input}
        placeholder={terms.length ? '' : placeholder}
        onChange={({ target: { value } }) => setInputValue(value)}
        onKeyDown={(event) => {
          if (event.key === 'Enter') {
            event.preventDefault()
            addTerm()
          } else if (event.key === 'Backspace' && !input && terms.length) {
            removeTerm(terms[terms.length - 1])
          }
        }}
      />
    </div>
  )
}
