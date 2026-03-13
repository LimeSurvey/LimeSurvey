import React, { useEffect, useState } from 'react'
import { Button, Form } from 'react-bootstrap'
import { Direction } from 'react-range'

import {
  AlignButtons,
  DropZone,
  ImageEditor,
  InputRange,
} from 'components/UIComponents'
import { DeleteIcon, EditIcon } from 'components/icons'
import { useFileService } from 'hooks'
import {
  getQuestionImageObjectFromImageAttribute,
  getAndGenerateImageStyles,
  getClearedQuestionImageObject,
} from 'helpers/questionImage'

export const ImageAttributes = ({
  update,
  value = {},
  isSimpleSettings = false,
}) => {
  const charLimit = 125
  const [show, setShow] = useState(false)
  const [remainingChars, setRemainingChars] = useState(charLimit) // Init with all remaining.
  const [imageState, setImageState] = useState({})
  const [previewUrl, setPreviewUrl] = useState(null)
  const [forceUpdateKey, setForceUpdateKey] = useState(0)
  const { fileService } = useFileService()

  // Initialize or update image state when value changes
  useEffect(() => {
    const imageObject = getQuestionImageObjectFromImageAttribute(value)
    setImageState(imageObject)
    setRemainingChars(charLimit - (imageObject.imageAltText?.length || 0))

    // Reset preview URL when the question/value changes
    if (imageObject && imageObject.imagePath) {
      setPreviewUrl(imageObject.imagePreviewUrl)
    } else {
      setPreviewUrl(null)
    }
  }, [value])

  // Only update the key when the image path changes when switching questions
  useEffect(() => {
    if (imageState.imagePath) {
      setForceUpdateKey((prev) => prev + 1)
    }
  }, [imageState.imagePath])

  const updateImageState = (changes) => {
    // Create new state with changes
    const newImageState = {
      ...imageState,
      ...changes,
    }

    // Generate styles if needed
    if (
      changes.imageBrightness !== undefined ||
      changes.imageRadius !== undefined
    ) {
      newImageState.imageStyles = getAndGenerateImageStyles(newImageState)
    }
    // Update component state
    setImageState(newImageState)

    // Create object for saving to backend
    const saveObject = {
      image_path: newImageState.imagePath || '',
      image_align: newImageState.imageAlign || 'left',
      image_brightness: newImageState.imageBrightness || 0,
      image_radius: newImageState.imageRadius || 0,
      image_alt_text: newImageState.imageAltText || '',
      image_styles: getAndGenerateImageStyles(newImageState, true),
    }

    // Save to backend
    update(JSON.stringify(saveObject))
  }

  const handleImageChange = (imagePath) => {
    const encodedPath = encodeURI(imagePath)
    updateImageState({ imagePath: encodedPath })
  }

  const handleAlignChange = (alignValue) => {
    updateImageState({ imageAlign: alignValue })
  }

  const handleBrightnessChange = (brightnessValue) => {
    updateImageState({ imageBrightness: brightnessValue[0] })
  }

  const handleRadiusChange = (radiusValue) => {
    updateImageState({ imageRadius: radiusValue[0] })
  }

  const handleAltTextChange = (altTextValue) => {
    updateImageState({ imageAltText: altTextValue })
  }

  const onChangePreview = (previewUrl) => {
    setPreviewUrl(previewUrl)
  }

  const handleDeleteImage = () => {
    updateImageState(getClearedQuestionImageObject())
    setPreviewUrl(null)
  }

  const handleEditModalSave = (imageObject) => {
    updateImageState({
      imageZoom: imageObject.imageZoom,
      imageRotate: imageObject.imageRotate,
      imageRadius: imageObject.imageRadius,
      imagePositionX: imageObject.imagePositionX,
      imagePositionY: imageObject.imagePositionY,
      imageStyles: imageObject.imageStyles,
    })
  }

  const handleEditImage = () => setShow(true)
  const handleClose = () => setShow(false)

  return (
    <>
      <div className="mb-3">
        {isSimpleSettings && <hr className="mb-3" />}
        <DropZone
          key={`dropzone-${previewUrl ? 'with-image' : 'empty'}`}
          previewUrlInit={previewUrl}
          onChangePreview={onChangePreview}
          fileService={fileService}
          onChange={handleImageChange}
          labelText={t('Add image')}
          image={previewUrl}
          dataTestId="add-image-or-video"
          trashIconEnabled={false}
        />

        {previewUrl && (
          <>
            <div
              className="mt-3 d-flex align-items-center justify-content-between"
              data-testid="image-or-video-edit-delete"
            >
              <span></span>
              <div>
                <Button
                  className="d-none"
                  onClick={handleEditImage}
                  variant="secondary"
                >
                  <EditIcon className=" fill-current" />
                </Button>
                <Button
                  onClick={handleDeleteImage}
                  variant="secondary"
                  className="ms-2"
                >
                  <DeleteIcon className="fill-current" />
                </Button>
              </div>
            </div>
            <div className={'qe-input-group mt-3'}>
              <AlignButtons
                update={handleAlignChange}
                labelText={t('Alignment')}
                value={imageState.imageAlign}
              />
            </div>
            <div className={'qe-input-group mt-3 image-attributes-range'}>
              <InputRange
                key={`brightness-${forceUpdateKey}`}
                onChange={handleBrightnessChange}
                labelText={t('Brightness')}
                min={-100}
                max={100}
                value={imageState.imageBrightness}
                step={1}
                direction={Direction.Right}
              />
            </div>
            <div className={'qe-input-group mt-3 image-attributes-range'}>
              <InputRange
                key={`radius-${forceUpdateKey}`}
                onChange={handleRadiusChange}
                labelText={t('Radius')}
                min={0}
                max={50}
                value={imageState.imageRadius}
                step={1}
                direction={Direction.Right}
              />
            </div>
            <div className="qe-input-group mt-3">
              <Form.Label>{t('Alt text')}</Form.Label>
              <div className=" position-relative">
                <Form.Control
                  value={imageState.imageAltText}
                  className="textarea"
                  maxLength={charLimit}
                  placeholder={t('Image description')}
                  as="textarea"
                  rows={6}
                  data-testid="alt-text"
                  onChange={(e) => {
                    if (charLimit) {
                      let remains = charLimit - e.target.value.length
                      setRemainingChars(remains) // Update characters remaining every change.
                    }
                    handleAltTextChange(e.target.value)
                  }}
                />
                <p
                  className="bottom-0 position-absolute"
                  style={{
                    right: '10px',
                    color: getCharactersColor(remainingChars, charLimit),
                  }}
                >
                  {remainingChars}/{charLimit}
                </p>
              </div>
            </div>
            <ImageEditor
              showModal={show}
              onClose={handleClose}
              onChange={(imageObject) => handleEditModalSave(imageObject)}
              imageObject={imageState}
            />
          </>
        )}
      </div>
    </>
  )
}

export const getCharactersColor = (remainingChars, maxChars) => {
  const ratio = remainingChars / maxChars
  if (ratio >= 0.2) return '#1A7A47'
  if (ratio >= 0.06) return '#8F5A00'
  if (ratio >= 0 || ratio < 0) return '#D12323'
}
