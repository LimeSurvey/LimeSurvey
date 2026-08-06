import { useCallback, useEffect, useRef, useState } from 'react'
import { Entities, L10ns } from 'helpers'
import { ContentEditor } from 'components'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { SurveyListComponent } from './SurveyListComponent'
import { ProjectTitleBadge } from './ProjectTitleBadge'
import { ProjectTitleForm } from './ProjectTitleForm'

const TITLE_SELECT_OFFSET = 40

export const SurveyTitleSelector = ({
  surveyId,
  survey,
  surveyList,
  activeLanguage,
  onSurveyTitleChange,
  handleSurveySwitch,
  getError,
  showCode,
  onProjectTitleSave,
  canEditProjectTitle,
}) => {
  const [surveyTitleIsFocused, setSurveyTitleIsFocused] = useState(false)
  const [formOpen, setFormOpen] = useState(false)
  const [isSaving, setIsSaving] = useState(false)
  const [saveError, setSaveError] = useState(false)
  const titleRef = useRef(null)

  const projectTitle = survey.projectTitle || ''

  const surveyTitle = L10ns({
    prop: 'title',
    language: activeLanguage,
    l10ns: survey.languageSettings,
  })

  const [surveyTitleWidth, setSurveyTitleWidth] = useState(0)

  useEffect(() => {
    const element = titleRef.current
    if (!element) return
    const resizeObserver = new ResizeObserver((entries) => {
      for (let entry of entries) {
        const width =
          entry.borderBoxSize?.[0]?.inlineSize || entry.contentRect.width
        setSurveyTitleWidth(width)
      }
    })

    resizeObserver.observe(element)
    return () => resizeObserver.disconnect()
  }, [])

  const handleSurveyTitleFocusChange = useCallback(
    (isFocused) => () => {
      setSurveyTitleIsFocused(isFocused)
    },
    []
  )

  const onTitleKeyDown = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault()
    }
  }

  const handleOpenForm = () => {
    setSaveError(false)
    setFormOpen(true)
  }

  const handleCloseForm = () => {
    setFormOpen(false)
    setSaveError(false)
  }

  const handleSave = async (value) => {
    if (isSaving) return
    setIsSaving(true)
    setSaveError(false)
    try {
      await onProjectTitleSave(value)
      setFormOpen(false)
    } catch {
      setSaveError(true)
    } finally {
      setIsSaving(false)
    }
  }

  const showProjectTitleUI = showCode

  return (
    <div
      data-error={getError(survey.sid, Entities.languageSetting)}
      className="d-flex align-items-center text-align-center top-bar-select align-middle"
      id="top-bar-select"
    >
      <div className="d-flex flex-column align-items-start">
        <div className="d-flex align-items-center">
          <ContentEditor
            value={surveyTitle}
            placeholder={t('Survey title')}
            update={onSurveyTitleChange}
            editorRef={titleRef}
            className="survey-title-content-editor"
            onBlur={handleSurveyTitleFocusChange(false)}
            onFocus={handleSurveyTitleFocusChange(true)}
            onKeyDown={onTitleKeyDown}
            noPermissionDisabled={true}
            toolTipPlacement={'bottom'}
            testId="topbar-survey-title-content-editor"
          />

          {showProjectTitleUI &&
            canEditProjectTitle &&
            surveyTitleIsFocused && (
              <TooltipContainer tip={t('Add project title')} placement="bottom">
                <button
                  className="project-title-plus-btn"
                  type="button"
                  aria-label={t('Add project title')}
                  onMouseDown={(e) => {
                    // prevent title blur before click fires
                    e.preventDefault()
                    handleOpenForm()
                  }}
                >
                  <i className="ri-add-line" aria-hidden="true" />
                </button>
              </TooltipContainer>
            )}

          {showProjectTitleUI && !surveyTitleIsFocused && (
            <ProjectTitleBadge
              projectTitle={projectTitle}
              canEdit={canEditProjectTitle}
              onClick={handleOpenForm}
            />
          )}

          <SurveyListComponent
            surveyId={surveyId}
            surveyList={surveyList}
            activeLanguage={activeLanguage}
            surveyTitleIsFocused={surveyTitleIsFocused}
            surveyTitleWidth={surveyTitleWidth}
            titleRef={titleRef}
            titleSelectOffset={TITLE_SELECT_OFFSET}
            handleSurveySwitch={handleSurveySwitch}
          />
        </div>

        {showProjectTitleUI && formOpen && (
          <ProjectTitleForm
            initialValue={projectTitle}
            isNew={!projectTitle}
            isSaving={isSaving}
            saveError={saveError}
            onSave={handleSave}
            onCancel={handleCloseForm}
          />
        )}
      </div>
    </div>
  )
}
