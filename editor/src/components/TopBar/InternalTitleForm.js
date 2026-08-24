import { XIcon } from 'components/icons'
import { Button, Input } from 'components/UIComponents'
import { useEffect, useRef, useState } from 'react'

export const InternalTitleForm = ({
  initialValue = '',
  isNew,
  isSaving,
  saveError,
  onSave,
  onCancel,
}) => {
  const [value, setValue] = useState(initialValue)
  const inputRef = useRef(null)

  useEffect(() => {
    inputRef.current?.focus()
  }, [])

  // Sync initial value when opening form for an existing title
  useEffect(() => {
    setValue(initialValue)
  }, [initialValue])

  const onKeyDown = (e) => {
    if (e.key === 'Escape') {
      onCancel()
    } else if (e.key === 'Enter') {
      onSave(value.trim())
    }
  }

  const onEscapeClickCheck = (e) => {
    if (e.key === 'Escape') {
      onCancel()
    }
  }

  useEffect(() => {
    document.addEventListener('keydown', onEscapeClickCheck)

    return () => document.removeEventListener('keydown', onEscapeClickCheck)
  }, [])

  const isSaveDisabled = isNew ? value.trim() === '' : false

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
          ref={inputRef}
          type="text"
          value={value}
          placeholder={t('Enter here')}
          maxLength={255}
          onChange={(e) => setValue(e.target.value)}
          aria-describedby={saveError ? 'internal-title-error' : undefined}
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
          disabled={isSaveDisabled || isSaving}
          onClick={() => onSave(value.trim())}
        >
          {t('Save')}
        </Button>
      </div>
      {saveError && (
        <div
          id="internal-title-error"
          className="internal-title-form__error"
          role="alert"
        >
          {t('Failed to save internal title. Please try again.')}
        </div>
      )}
    </div>
  )
}
