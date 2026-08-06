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

  const handleKeyDown = (e) => {
    if (e.key === 'Escape') {
      onCancel()
    }
  }

  const isSaveDisabled = isNew ? value.trim() === '' : false

  return (
    <div
      className="project-title-form"
      role="region"
      aria-label={t('Project title')}
    >
      <label
        htmlFor="project-title-input"
        className="project-title-form__label"
      >
        {t('Project title')}
      </label>
      <div className="project-title-form__row">
        <input
          id="project-title-input"
          ref={inputRef}
          type="text"
          className="project-title-form__input"
          value={value}
          placeholder={t('Enter here')}
          maxLength={255}
          onChange={(e) => setValue(e.target.value)}
          onKeyDown={handleKeyDown}
          aria-describedby={saveError ? 'project-title-error' : undefined}
        />
        <button
          className="project-title-form__btn project-title-form__btn--cancel"
          type="button"
          onClick={onCancel}
        >
          {t('Cancel')}
        </button>
        <button
          className="project-title-form__btn project-title-form__btn--save"
          type="button"
          disabled={isSaveDisabled || isSaving}
          onClick={() => onSave(value.trim())}
        >
          {t('Save')}
        </button>
        <button
          className="project-title-form__btn project-title-form__btn--close"
          type="button"
          aria-label={t('Close project title form')}
          onClick={onCancel}
        >
          <i className="ri-close-line" aria-hidden="true" />
        </button>
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
