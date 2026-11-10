import { XIcon } from 'components/icons'
import { Button, Input } from 'components/UIComponents'
import { useEffect, useRef, useState } from 'react'

export const SURVEY_CODE_MAX_LENGTH = 128

export const SurveyCodeForm = ({
  initialValue = '',
  isNew,
  isSaving,
  onSave,
  onCancel,
}) => {
  const [value, setValue] = useState(initialValue)
  const inputRef = useRef(null)
  const onCancelRef = useRef(onCancel)

  useEffect(() => {
    onCancelRef.current = onCancel
  }, [onCancel])

  useEffect(() => {
    inputRef.current?.focus()
  }, [])

  // Sync initial value when opening form for an existing title
  useEffect(() => {
    setValue(initialValue)
  }, [initialValue])

  // Escape closes the form wherever the focus is (handled once, here)
  useEffect(() => {
    const onDocumentKeyDown = (e) => {
      if (e.key === 'Escape') {
        onCancelRef.current()
      }
    }

    document.addEventListener('keydown', onDocumentKeyDown)
    return () => document.removeEventListener('keydown', onDocumentKeyDown)
  }, [])

  const isSaveDisabled = (isNew && value.trim() === '') || isSaving

  const handleSave = () => {
    if (isSaveDisabled) {
      return
    }
    onSave(value.trim())
  }

  const onKeyDown = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault()
      handleSave()
    }
  }

  return (
    <div
      className="internal-title-form"
      role="region"
      aria-label={t('Internal title')}
    >
      <div className="d-flex close-button justify-content-end align-items-center mb-1">
        <Button onClick={() => onCancel()} variant="">
          <XIcon />
        </Button>
      </div>
      <div className="internal-title-label mb-1">
        <label htmlFor="internal-title-input">{t('Internal title')}</label>
      </div>
      <div className="internal-title-input">
        <Input
          id="internal-title-input"
          inputRef={inputRef}
          type="text"
          value={value}
          placeholder={t('Enter here')}
          maxLength={SURVEY_CODE_MAX_LENGTH}
          onChange={(e) => setValue(e.target.value)}
          focus={true}
          onKeyDown={onKeyDown}
        />
      </div>
      <div className="d-flex gap-1 buttons-container justify-content-end">
        <Button variant="secondary" onClick={onCancel}>
          {t('Cancel')}
        </Button>
        <Button
          variant="primary"
          disabled={isSaveDisabled}
          onClick={handleSave}
        >
          {t('Save')}
        </Button>
      </div>
    </div>
  )
}
