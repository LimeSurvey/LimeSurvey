import { XIcon } from 'components/icons'
import { Button, Input } from 'components/UIComponents'
import { useEffect, useRef, useState } from 'react'

export const ProjectTitleForm = ({
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

  const onKeyDownProjectFormOpen = (e) => {
    if (e.key === 'Escape') {
      onCancel()
    } else if (e.key === 'Enter') {
      onSave(value.trim())
    }
  }

  useEffect(() => {
    document.addEventListener('keydown', onKeyDownProjectFormOpen)

    return () =>
      document.removeEventListener('keydown', onKeyDownProjectFormOpen)
  }, [])

  const isSaveDisabled = isNew ? value.trim() === '' : false

  return (
    <div
      className="project-title-form"
      role="region"
      aria-label={t('Project title')}
    >
      <div className="d-flex close-button justify-content-end align-items-center mb-1">
        <Button onClick={() => onSave(value.trim())} variant="">
          <XIcon />
        </Button>
      </div>
      <div className="project-title-label mb-1">
        <label htmlFor="project-title-input">{t('Project title')}</label>
      </div>
      <div className="project-title-input">
        <Input
          id="project-title-input"
          ref={inputRef}
          type="text"
          value={value}
          placeholder={t('Enter here')}
          maxLength={255}
          onChange={(e) => setValue(e.target.value)}
          aria-describedby={saveError ? 'project-title-error' : undefined}
          focus={true}
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
          id="project-title-error"
          className="project-title-form__error"
          role="alert"
        >
          {t('Failed to save project title. Please try again.')}
        </div>
      )}
    </div>
  )
}
