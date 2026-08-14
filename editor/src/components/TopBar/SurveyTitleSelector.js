import { useCallback, useEffect, useRef, useState } from 'react'
import { Entities, L10ns } from 'helpers'
import { Button, ContentEditor } from 'components'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { SurveyListComponent } from './SurveyListComponent'
import { ProjectTitleBadge } from './ProjectTitleBadge'
import { ProjectTitleForm } from './ProjectTitleForm'
import classNames from 'classnames'

const TITLE_SELECT_OFFSET = 40

export const SurveyTitleSelector = ({
  surveyId,
  survey,
  surveyList,
  activeLanguage,
  onSurveyTitleChange,
  handleSurveySwitch,
  getError,
  onProjectTitleSave,
  canEditProjectTitle,
}) => {
  const [surveyTitleIsFocused, setSurveyTitleIsFocused] = useState(false)
  const [projectFormOpen, setProjectFormOpen] = useState(false)
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

  const onKeyDownProjectFormOpen = (e) => {
    if (e.key === 'Escape') {
      handleCloseForm()
    } else if (e.key === 'Enter') {
      handleSave(projectTitle)
    }
  }

  useEffect(() => {
    document.removeEventListener('keydown', onKeyDownProjectFormOpen)

    if (projectFormOpen) {
      document.addEventListener('keydown', onKeyDownProjectFormOpen)
    } else {
      document.removeEventListener('keydown', onKeyDownProjectFormOpen)
    }
  }, [projectFormOpen])

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
    setProjectFormOpen(true)
  }

  const handleCloseForm = () => {
    setProjectFormOpen(false)
    setSaveError(false)
  }

  const handleSave = async (value) => {
    if (isSaving) return
    setIsSaving(true)
    setSaveError(false)
    try {
      await onProjectTitleSave(value)
      setProjectFormOpen(false)
    } catch {
      setSaveError(true)
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <div
      data-error={getError(survey.sid, Entities.languageSetting)}
      className="d-flex align-items-center text-align-center top-bar-select align-middle"
      id="top-bar-select"
    >
      <div className="d-flex align-items-center position-relative align-items-start">
        <ProjectTitleBadge
          projectTitle={projectTitle}
          canEdit={canEditProjectTitle}
          onClick={handleOpenForm}
          showBadge={survey.showQNumCode?.showNumber}
        />
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

          <TooltipContainer tip={t('Add project title')} placement="bottom">
            <Button
              className={classNames('project-title-plus-btn ms-2', {
                'pointer-events-none opacity-0':
                  !canEditProjectTitle ||
                  !surveyTitleIsFocused ||
                  !survey.showQNumCode?.showNumber,
              })}
              aria-label={t('Add project title')}
              onMouseDown={(e) => {
                e.preventDefault()
                handleOpenForm()
              }}
            >
              <i className="ri-add-line" aria-hidden="true" />
            </Button>
          </TooltipContainer>

          <SurveyListComponent
            surveyId={surveyId}
            surveyList={surveyList}
            activeLanguage={activeLanguage}
            surveyTitleIsFocused={surveyTitleIsFocused}
            surveyTitleWidth={surveyTitleWidth}
            titleRef={titleRef}
            titleSelectOffset={TITLE_SELECT_OFFSET}
            handleSurveySwitch={handleSurveySwitch}
            showCode={survey.showQNumCode?.showCode}
          />
        </div>
        {projectFormOpen && (
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
