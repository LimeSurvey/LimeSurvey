import React, { useMemo, useState, useEffect } from 'react'
import classNames from 'classnames'

import {
  isTempId,
  IMAGE_PREVIEW_HEIGHT,
  getNoAnswerLabel,
  docUrls,
} from 'helpers'
import NoImageFound from 'assets/images/no-image-found.jpg'
import {
  ContentEditor,
  DropZone,
  EditedImage,
  FormCheck,
  ImageEditor,
} from 'components/UIComponents'
import { useFileService } from 'hooks'

// Border around an option's image.
const IMAGE_BORDER_CLASSES = 'border border-3 border-secondary rounded'

/**
 * One option of the image select themes: The image with its upload drop zone,
 * and in edit mode the image editor modal.
 *
 * @param {number|string} id ID of the answer or subquestion
 * @param {string} idPrefix prefix for element IDs ('a' answers, 'q' subquestions)
 * @param {number} index position of the option
 * @param {string} code the option code, used as image name
 * @param {string} inputType 'checkbox' or 'radio'
 * @param {boolean} isFocused whether the question is in edit mode
 * @param {Function} update called with the new image path, or the checked state
 * @param {string} value the image path
 * @param {boolean} isNoAnswer whether this is the "No answer" option
 * @param {string} groupName name of the radio group
 * @param {boolean|null} checked controlled checked state
 * @param {boolean} defaultChecked initial checked state
 * @param {Object} errors errors of the option
 * @param {Function} setErrors called with the updated errors
 * @param {Object|null} imageSettings saved image editor settings of the option,
 *   the image is shown with them in view and edit mode
 * @param {string} imageAltText saved alt text in the current language
 * @param {boolean} imageApplyToAll whether "Apply to all images of this
 *   question" starts checked in the image editor: Its settings are kept for
 *   images added later
 * @param {Function|null} onImageSettingsSave called with the image editor
 *   settings, including the alt text, when they are saved. Without it, there
 *   is no edit button.
 * @returns {JSX.Element} the option
 */
export const ImageChoice = ({
  id,
  idPrefix,
  index,
  code = '',
  inputType = 'checkbox',
  isFocused,
  update,
  value,
  isNoAnswer,
  groupName,
  checked = null,
  defaultChecked = false,
  errors,
  setErrors = () => {},
  imageSettings = null,
  imageAltText = '',
  imageApplyToAll = false,
  onImageSettingsSave = null,
}) => {
  const [isLoading, setIsLoading] = useState(false)
  const [showImageEditor, setShowImageEditor] = useState(false)
  const { fileService } = useFileService()
  // REACT_APP_SITE_URL is needed in development environments
  // - because the react-app runs on a different port to the core app
  const [previewUrl, setPreviewUrl] = useState(
    value ? process.env.REACT_APP_SITE_URL + value : null
  )

  const onChange = (filePath) => {
    // A missing image replaced by one with the same name keeps its URL: The
    // new file gets its own chance to load.
    setFailedImageUrl(null)
    update(encodeURI(filePath))
  }

  const onChangePreview = (previewUrl) => {
    setPreviewUrl(previewUrl)
  }

  const initLoading = useMemo(() => isTempId(id), [id])

  const regexContainsHtml = /(<([^>]+)>)/i
  const isValidImg = useMemo(() => !regexContainsHtml.test(value), [value])
  // URL of an image that couldn't be loaded, e.g. its file is missing: The
  // placeholder is shown instead. Keeping the URL gives a new image its own
  // chance.
  const [failedImageUrl, setFailedImageUrl] = useState(null)
  const showImage = !!previewUrl && isValidImg && failedImageUrl !== previewUrl

  const idSuffix = idPrefix + id + '-i' + index

  const showLoader = (isFocused || isLoading) && !initLoading

  const handleImageEditorSave = (settings) => {
    onImageSettingsSave?.(settings)
    setShowImageEditor(false)
  }

  useEffect(() => {
    setPreviewUrl(value ? process.env.REACT_APP_SITE_URL + value : null)
  }, [value])

  useEffect(() => {
    let errorMessage = null
    if (!isValidImg && !isNoAnswer) {
      errorMessage = (
        <div className="error-message">
          <a
            href={docUrls.imageQuestionHtmlImageValue}
            target="_blank"
            rel="noreferrer"
          >
            {t('This image is not valid. Click here for more info.')}
          </a>
        </div>
      )
    }
    setErrors({ invalidImage: errorMessage, ...errors })
  }, [isValidImg, isNoAnswer])

  return (
    // The option and its image may shrink to the space they get, so that the
    // image never gets wider than the question.
    <div
      className="pe-4"
      style={{ minWidth: 0, maxWidth: '100%', flexShrink: 1 }}
    >
      {/* In view mode, the image is as far from the radio/checkbox as the
          text of other choice questions (24px): Without an empty label in
          between, which would take its minimum width. mt-1 keeps the input
          where the 32px high label row centered it. */}
      <div className={classNames('d-flex', isFocused ? 'gap-2' : 'gap-4')}>
        {!isFocused && (
          <FormCheck
            className={isNoAnswer ? '' : 'mt-1'}
            type={inputType}
            name={'image-choice-' + idSuffix}
            data-testid={'image-choice-' + idSuffix}
            checked={checked}
            defaultChecked={defaultChecked}
            groupName={groupName}
            update={(value) => update(value)}
            label={
              isNoAnswer ? (
                <ContentEditor
                  className="choice"
                  value={getNoAnswerLabel(true)}
                  disabled={true}
                />
              ) : undefined
            }
          />
        )}
        {isFocused && <div>&nbsp;</div>}
        {!isNoAnswer && !initLoading && (
          // In edit mode, the border goes around the image only: The edit and
          // delete buttons are next to it, outside.
          <div
            className={classNames('image-choice-preview', {
              [IMAGE_BORDER_CLASSES]: !showLoader,
            })}
            style={{ minWidth: 0, maxWidth: '100%' }}
          >
            <div className={classNames({ 'd-none': showLoader })}>
              <EditedImage
                src={showImage ? previewUrl : NoImageFound}
                alt={imageAltText || code}
                settings={showImage ? imageSettings : null}
                height={IMAGE_PREVIEW_HEIGHT}
                onError={() => setFailedImageUrl(previewUrl)}
                fallbackStyle={{
                  height: '100%',
                  maxHeight: IMAGE_PREVIEW_HEIGHT,
                  maxWidth: '100%',
                  backgroundSize: 'cover',
                }}
              />
            </div>
            <div className={classNames({ 'd-none': !showLoader })}>
              <DropZone
                dataTestId={'dropzone-' + idSuffix}
                previewUrlInit={previewUrl}
                fileService={fileService}
                previewMaxHeight={IMAGE_PREVIEW_HEIGHT}
                onChange={onChange}
                onChangePreview={onChangePreview}
                isValidImg={isValidImg}
                onLoading={(value) => setIsLoading(value)}
                onEdit={
                  previewUrl && isValidImg && onImageSettingsSave
                    ? () => setShowImageEditor(true)
                    : null
                }
                isEditing={showImageEditor}
                imageName={code}
                imageSettings={imageSettings}
                imageAlt={imageAltText || code}
                buttonsBeside={true}
                previewBoxClassName={IMAGE_BORDER_CLASSES}
              />
            </div>
          </div>
        )}
        {showImageEditor && (
          <ImageEditor
            showModal={showImageEditor}
            onClose={() => setShowImageEditor(false)}
            onSave={handleImageEditorSave}
            imageUrl={previewUrl}
            settings={imageSettings}
            altText={imageAltText}
            showApplyToAll={true}
            applyToAll={imageApplyToAll}
          />
        )}
        {isFocused && initLoading && !isNoAnswer && (
          <div style={{ width: 44, height: 44 }} className="loader ms-4"></div>
        )}
      </div>
    </div>
  )
}
