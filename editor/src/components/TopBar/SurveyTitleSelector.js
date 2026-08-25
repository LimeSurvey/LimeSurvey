import { useCallback, useEffect, useRef, useState } from 'react'
import { Entities, L10ns } from 'helpers'
import { Button, ContentEditor } from 'components'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { SurveyListComponent } from './SurveyListComponent'
import { InternalTitleBadge } from './InternalTitleBadge'
import { InternalTitleForm } from './InternalTitleForm'
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
  onInternalTitleSave,
  canEditInternalTitle,
}) => {
  const [surveyTitleIsFocused, setSurveyTitleIsFocused] = useState(false)
  const [internalFormOpen, setInternalFormOpen] = useState(false)
  const [saveError, setSaveError] = useState(false)
  const titleRef = useRef(null)

  const internalTitle = survey.internalTitle || ''

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

  const handleOpenForm = () => {
    setSaveError(false)
    setInternalFormOpen(true)
  }

  const handleCloseForm = () => {
    setInternalFormOpen(false)
    setSaveError(false)
  }

  const handleSave = async (value) => {
    setSaveError(false)
    try {
      await onInternalTitleSave(value)
      setInternalFormOpen(false)
    } catch {
      setSaveError(true)
    }
  }

  return (
    <div
      data-error={getError(survey.sid, Entities.languageSetting)}
      className="d-flex align-items-center text-align-center top-bar-select align-middle"
      id="top-bar-select"
    >
      <div className="d-flex align-items-center position-relative align-items-start">
        <InternalTitleBadge
          internalTitle={internalTitle}
          canEdit={canEditInternalTitle}
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
            noPermissionDisabled={true}
            toolTipPlacement={'bottom'}
            testId="topbar-survey-title-content-editor"
          />

          <TooltipContainer tip={t('Add internal title')} placement="bottom">
            <Button
              className={classNames('internal-title-plus-btn ms-2', {
                'pointer-events-none opacity-0':
                  !canEditInternalTitle ||
                  !surveyTitleIsFocused ||
                  !survey.showQNumCode?.showNumber,
              })}
              aria-label={t('Add internal title')}
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
        {internalFormOpen && (
          <InternalTitleForm
            initialValue={internalTitle}
            isNew={!internalTitle}
            saveError={saveError}
            onSave={handleSave}
            onCancel={handleCloseForm}
          />
        )}
      </div>
    </div>
  )
}
