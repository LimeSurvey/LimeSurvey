import { useCallback, useEffect, useRef, useState } from 'react'
import { Entities, L10ns } from 'helpers'
import { ContentEditor } from 'components'
import { SurveyListComponent } from './SurveyListComponent'

const TITLE_SELECT_OFFSET = 40

export const SurveyTitleSelector = ({
  surveyId,
  survey,
  surveyList,
  activeLanguage,
  onSurveyTitleChange,
  handleSurveySwitch,
  getError,
}) => {
  const [surveyTitleIsFocused, setSurveyTitleIsFocused] = useState(false)
  const titleRef = useRef(null)

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

  return (
    <div
      data-error={getError(survey.sid, Entities.languageSetting)}
      className="d-flex align-items-center text-align-center top-bar-select align-middle"
      id="top-bar-select"
    >
      <div className="d-flex justify-content-center">
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
    </div>
  )
}
