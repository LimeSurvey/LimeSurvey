import React, { useEffect, useRef, useState } from 'react'
import AvatarEditor from 'react-avatar-editor'
import { Direction } from 'react-range'
import Modal from 'react-bootstrap/Modal'
import { Button } from 'react-bootstrap'
import { InputRange } from '../InputRange/InputRange'
import { getAndGenerateImageStyles } from 'helpers/questionImage'

/**
 * Image editor component for editing images
 * This is not used at the moment, as it still needs some love (design and functionality)
 * @param showModal
 * @param onClose
 * @param imageObject
 * @param onChange
 * @returns {Element}
 * @constructor
 */
export const ImageEditor = ({ showModal, onClose, imageObject, onChange }) => {
  const editor = useRef(null)
  const [selectedFile, setSelectedFile] = useState(null)
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    if (imageObject) {
      setSelectedFile({ ...imageObject })
    }
  }, [imageObject])

  const handleZoomChange = (value) =>
    setSelectedFile((prev) => ({ ...prev, imageZoom: value[0] }))

  const handleRotateChange = (value) =>
    setSelectedFile((prev) => ({ ...prev, imageRotate: value[0] }))

  const handleRadiusChange = (value) =>
    setSelectedFile((prev) => ({ ...prev, imageRadius: value[0] }))

  // Track position changes when user drags the image
  const handlePositionChange = (position) => {
    setSelectedFile((prev) => ({
      ...prev,
      imagePositionX: position.x,
      imagePositionY: position.y,
    }))
  }

  // Save just the transformation settings without processing the image
  const handleSave = () => {
    setIsLoading(true)
    try {
      // Create updated image object with new transformation settings
      const updatedImageObject = {
        ...imageObject,
        imageZoom: selectedFile.imageZoom || 1,
        imageRotate: selectedFile.imageRotate || 0,
        imageRadius: selectedFile.imageRadius || 0,
        imagePositionX:
          parseFloat(selectedFile.imagePositionX.toFixed(2)) || 0.5,
        imagePositionY:
          parseFloat(selectedFile.imagePositionY.toFixed(2)) || 0.5,
      }

      // Generate and add the CSS styles
      updatedImageObject.imageStyles =
        getAndGenerateImageStyles(updatedImageObject)

      // Pass back the updated image object with styles
      onChange(updatedImageObject)
      onClose()
    } catch (error) {
      // console.error('Error saving image settings:', error);
      alert(t('Unable to save image settings. Please try again.'))
    } finally {
      setIsLoading(false)
    }
  }

  if (!selectedFile) return <></>

  return (
    <Modal show={showModal} onHide={onClose}>
      <Modal.Header closeButton>
        <Modal.Title>{t('LimeSurvey Image Editor')}</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <div className=" " style={{ width: '900px' }}>
          <div className="w-50">
            <AvatarEditor
              ref={editor}
              border={50}
              color={[255, 255, 255, 0.6]}
              image={selectedFile.imagePreviewUrl}
              scale={selectedFile.imageZoom || 1}
              rotate={selectedFile.imageRotate || 0}
              borderRadius={selectedFile.imageRadius || 0}
              position={{
                x: selectedFile.imagePositionX || 0.5,
                y: selectedFile.imagePositionY || 0.5,
              }}
              onPositionChange={handlePositionChange}
              width={300}
              height={300}
            />
          </div>
          <div className="w-50">
            <div>
              <InputRange
                onChange={(value) => handleZoomChange(value)}
                labelText={t('Zoom')}
                min={1}
                step={0.1}
                max={4}
                direction={Direction.Right}
                value={selectedFile.imageZoom || 1}
              />
            </div>
            <div>
              <InputRange
                onChange={(value) => handleRotateChange(value)}
                labelText={t('Rotate')}
                min={0}
                step={1}
                max={360}
                direction={Direction.Right}
                value={selectedFile.imageRotate || 0}
              />
            </div>
            <div>
              <InputRange
                onChange={handleRadiusChange}
                labelText={t('Radius')}
                min={0}
                step={1}
                max={100}
                direction={Direction.Right}
                value={selectedFile.imageRadius || 0}
              />
            </div>
          </div>
        </div>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={onClose}>
          {t('Close')}
        </Button>
        <Button variant="primary" onClick={handleSave} disabled={isLoading}>
          {isLoading ? (
            <>
              <span
                className="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              {t('Saving...')}
            </>
          ) : (
            t('Save changes')
          )}
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
