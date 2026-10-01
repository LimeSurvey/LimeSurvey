import React, { useEffect, useMemo, useRef, useState } from 'react'
import copy from 'copy-text-to-clipboard'
import classNames from 'classnames'

import { useSurveyUpdatePermission } from 'hooks'
import { formatUrlPreview, getSurveyAccessLink } from 'helpers'
import { Button, CustomizeButton, Input } from 'components/UIComponents'

import CopyButton from './CopyButton'
import { SurveyAccessModeSelector } from './SurveyAccessMode/SurveyAccessModeSelector'

export const PublicSurveyAlias = ({
  parentName = null, // Name of the parent component where this component is used (e.g. 'Overview, SharingPanel'). Used to apply specific styling adjustments based on the parent component
  survey = {},
  update,
  language = '',
  onAliasChange = () => {},
  setLink,
  editable = true,
  aliasHasError = false,
  currentSurveyAccessMode,
  onSurveyAccessModeChange = () => {},
  createBufferOperation,
  addToBuffer,
}) => {
  const [isEditingAlias, setIsEditingAlias] = useState(false)
  const inputRef = useRef(null)
  const aliasRef = useRef(null)
  const linkPrefix =
    window.location.protocol + '//' + window.location.host + '/'
  const alias = useMemo(
    () => survey.languageSettings[language]?.alias?.trim() || '',
    [language, survey]
  )

  const hasUpdatePermission = useSurveyUpdatePermission(survey)

  const [surveyAlias, setSurveyAlias] = useState(alias)

  const link = useMemo(() => {
    return getSurveyAccessLink({ survey, language })
  }, [survey, language])

  const linkForPreview = useMemo(() => {
    return getSurveyAccessLink({ survey, language, isPreviewLink: true })
  }, [survey, language])

  const maxPreviewLength = useMemo(() => 40, [])

  const cleanLinkForPreview = useMemo(
    () => formatUrlPreview(linkForPreview, maxPreviewLength),
    [linkForPreview]
  )
  const cleanLinkPrefix = useMemo(
    () => formatUrlPreview(linkPrefix, maxPreviewLength),
    [linkPrefix]
  )
  const aliasWidth = useMemo(() => {
    if (aliasRef.current) {
      return aliasRef.current.offsetWidth
    }
    return 0
  }, [cleanLinkPrefix, aliasRef.current])

  const copySurveyLink = () => {
    copy(link)
  }

  const onAliasSave = () => {
    if (hasUpdatePermission) {
      setIsEditingAlias(false)
      onAliasChange(surveyAlias.trim())
    }
  }

  const onAliasCancel = () => {
    setIsEditingAlias(false)
    setSurveyAlias(alias)
  }

  const handleLinkToggle = () => {
    if (hasUpdatePermission) {
      setIsEditingAlias(!isEditingAlias)
    }
  }

  useEffect(() => {
    if (isEditingAlias) {
      inputRef.current.focus()
      inputRef.current.setSelectionRange(surveyAlias.length, surveyAlias.length)
    }
  }, [isEditingAlias])

  useEffect(() => {
    setLink(link)
    setIsEditingAlias(false)
    setSurveyAlias(alias.trim())
  }, [alias])

  return (
    <>
      <SurveyAccessModeSelector
        parentName={parentName}
        survey={survey}
        onSurveyAccessModeChange={onSurveyAccessModeChange}
        currentSurveyAccessMode={currentSurveyAccessMode}
        createBufferOperation={createBufferOperation}
        addToBuffer={addToBuffer}
        update={update}
      />
      <div
        className={classNames('d-flex align-items-center my-3 gap-0', {
          'border border-danger': aliasHasError,
        })}
      >
        {isEditingAlias && (
          <Input
            type="text"
            className="survey-alias-input pe-0 "
            style={{ width: `${aliasWidth}px` }}
            value={cleanLinkPrefix}
            disabled
            inputClass={`border-none p-0`}
          />
        )}
        <Input
          type="text"
          className={`survey-alias-input me-2 p-relative ${isEditingAlias ? 'px-0' : 'w-100'}`}
          style={
            isEditingAlias ? { width: `calc(100% - ${aliasWidth}px)` } : {}
          }
          value={isEditingAlias ? surveyAlias : cleanLinkForPreview}
          onChange={({ target: { value } }) => {
            setSurveyAlias(value)
          }}
          placeholder={t('Enter survey alias')}
          disabled={!isEditingAlias}
          Icon={isEditingAlias ? null : <CopyButton onClick={copySurveyLink} />}
          inputClass={`border-none p-0 text-cursor ${isEditingAlias ? '' : ''}`}
          inputRef={inputRef}
        />
      </div>
      {editable && !isEditingAlias && (
        <CustomizeButton
          text={t('Customize link')}
          isDisabled={!hasUpdatePermission}
          onClick={handleLinkToggle}
        />
      )}
      {isEditingAlias && (
        <div className="survey-alias-actions">
          <Button variant="outline-dark" onClick={onAliasCancel}>
            {t('Cancel')}
          </Button>
          <Button variant="outline-dark" onClick={onAliasSave}>
            {t('Save')}
          </Button>
        </div>
      )}
      {/* Temporary span element to measure alias width */}
      <span
        ref={aliasRef}
        className="invisible position-absolute form-control survey-alias-input w-auto"
      >
        {cleanLinkPrefix}
      </span>
    </>
  )
}
