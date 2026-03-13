import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useOperationCallback, useSurvey } from 'hooks'
import { Entities, getSiteUrl, Operations } from 'helpers'
import { ExpandPreviewIcon, MinimizeIcon } from 'components/icons'

const addPopupPreviewParam = (url) => {
  const parsedUrl = new URL(url, window.location.origin)
  return parsedUrl.toString() + '&popuppreview=true'
}

const ThemeOptionsPreview = ({ shouldBeVisible = true }) => {
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const { subscribeToOperationFinish } = useOperationCallback()

  const iframeSrc = useMemo(() => {
    const previewUrl = getSiteUrl(survey.previewLink)
    return addPopupPreviewParam(previewUrl)
  }, [survey.previewLink])

  const iframeRef = useRef(null)
  const prevVisibleRef = useRef(shouldBeVisible)
  const [isPreviewLarge, setIsPreviewLarge] = useState(false)
  const [previewLoading, setPreviewLoading] = useState(false)

  const togglePreviewSize = useCallback(
    () => setIsPreviewLarge((prev) => !prev),
    []
  )

  useEffect(() => {
    subscribeToOperationFinish({
      entity: Entities.themeSettings,
      operation: Operations.update,
      callback: () => {
        if (iframeRef.current) {
          setPreviewLoading(true)
          // eslint-disable-next-line no-self-assign
          iframeRef.current.src = iframeRef.current.src
        }
      },
      once: false,
    })
  }, [subscribeToOperationFinish])

  useEffect(() => {
    if (!prevVisibleRef.current && shouldBeVisible) {
      setPreviewLoading(true)
    }
    prevVisibleRef.current = shouldBeVisible
  }, [shouldBeVisible])

  const previewFinishedLoading = useCallback(() => setPreviewLoading(false), [])

  if (!shouldBeVisible && !previewLoading) {
    return null
  }

  return (
    <div
      id="theme-options-preview-container"
      className={classNames('theme-options-preview-container', {
        'theme-options-preview-largeSize': isPreviewLarge,
        'theme-options-preview-normalSize': !isPreviewLarge,
        'theme-options-preview-visible': shouldBeVisible,
        'theme-options-preview-hidden': !shouldBeVisible,
      })}
    >
      <div className="theme-options-preview-container-inner">
        <iframe
          ref={iframeRef}
          className="theme-options-preview-iframe"
          style={{
            border: shouldBeVisible ? '1px solid black' : 'none',
          }}
          onLoad={previewFinishedLoading}
          src={iframeSrc}
        />
        {previewLoading && shouldBeVisible && (
          <span className="theme-options-preview-loading loader"></span>
        )}
        {shouldBeVisible && (
          <div
            className="theme-options-preview-icon"
            onClick={togglePreviewSize}
          >
            {isPreviewLarge ? <MinimizeIcon /> : <ExpandPreviewIcon />}
          </div>
        )}
      </div>
    </div>
  )
}

export default ThemeOptionsPreview
