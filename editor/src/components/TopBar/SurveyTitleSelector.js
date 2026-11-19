import { useCallback, useEffect, useRef, useState } from 'react'
import { Entities, L10ns, RemoveHTMLTagsInString } from 'helpers'
import { Button, ContentEditor, TooltipContainer } from 'components'
import { SurveyListComponent } from './SurveyListComponent'
import { SurveyCodeBadge } from './SurveyCodeBadge'
import { SurveyCodeForm } from './SurveyCodeForm'
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
  onSurveyCodeSave,
  canEditSurveyCode,
  showCode = false,
}) => {
  const [surveyTitleIsFocused, setSurveyTitleIsFocused] = useState(false)
  const [internalFormOpen, setInternalFormOpen] = useState(false)
  const titleRef = useRef(null)

  const surveyCode = survey.code || ''

  // The top bar stays mounted when switching surveys: close the form, so a draft
  // for one survey can't be saved to the next
  useEffect(() => {
    setInternalFormOpen(false)
  }, [surveyId])

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
    setInternalFormOpen(true)
  }

  const handleCloseForm = () => {
    setInternalFormOpen(false)
  }

  // The change is buffered and persisted with the other survey operations;
  // server-side errors surface through the regular buffer error handling.
  const handleSave = (value) => {
    onSurveyCodeSave(value)
    setInternalFormOpen(false)
  }

  return (
    <div
      data-error={getError(null, Entities.languageSetting)}
      className="d-flex align-items-center text-align-center top-bar-select align-middle"
      id="top-bar-select"
    >
      <div className="d-flex align-items-center position-relative align-items-start">
        <SurveyCodeBadge
          surveyCode={surveyCode}
          canEdit={canEditSurveyCode}
          onClick={handleOpenForm}
          showBadge={showCode}
        />
        <div className="d-flex align-items-center">
          <ContentEditor
            value={RemoveHTMLTagsInString(surveyTitle)}
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

          <TooltipContainer
            tip={t(
              canEditSurveyCode && survey && showCode
                ? 'Add internal title'
                : ''
            )}
            placement="bottom"
          >
            <Button
              className={classNames('internal-title-plus-btn ms-2', {
                'pointer-events-none opacity-0':
                  !canEditSurveyCode || !surveyTitleIsFocused || !showCode,
              })}
              ariaLabel={t('Add internal title')}
              testId="internal-title-plus-button"
              // Out of the tab order when it can't be used
              disabled={!canEditSurveyCode || !showCode}
              // Mouse: open on mousedown, before the title loses focus and hides the button
              onMouseDown={(e) => {
                e.preventDefault()
                handleOpenForm()
              }}
              // Keyboard: Enter and Space click the button
              onClick={handleOpenForm}
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
          />
        </div>
        {internalFormOpen && (
          <SurveyCodeForm
            initialValue={surveyCode}
            isNew={!surveyCode}
            onSave={handleSave}
            onCancel={handleCloseForm}
          />
        )}
      </div>
    </div>
  )
}
