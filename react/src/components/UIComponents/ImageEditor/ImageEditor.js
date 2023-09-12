import React, { useEffect, useRef, useState } from 'react'
import AvatarEditor from 'react-avatar-editor'
import { Direction } from 'react-range'
import Modal from 'react-bootstrap/Modal'

import { Button } from 'react-bootstrap'
import { InputRange } from 'components/UIComponents'

export const ImageEditor = ({ showModal, onClose, file, onChange }) => {
  const editor = useRef(null)
  const [selectedFile, setSelectedFile] = useState(null)
  useEffect(() => {
    if (file) {
      setSelectedFile({ ...file })
    }
  }, [file])

  const handleZoomChange = (value) =>
    setSelectedFile((prev) => ({ ...prev, zoom: value }))

  const handleRotateChange = (value) =>
    setSelectedFile((prev) => ({ ...prev, rotate: value }))

  const handleRadiusChange = (value) =>
    setSelectedFile((prev) => ({ ...prev, radius: value }))

  const handleSave = () => {
    if (editor?.current) {
      const canvasScaled = editor.current.getImageScaledToCanvas()
      const croppedImg = canvasScaled.toDataURL()
      onChange({ ...selectedFile, preview: croppedImg })
      onClose()
    }
  }
  if (!selectedFile) return <></>
  return (
    <Modal show={showModal} onHide={onClose}>
      <Modal.Header closeButton>
        <Modal.Title>LimeSurvey Image Editor</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <div className=" " style={{ width: '900px' }}>
          <div className="w-50">
            <AvatarEditor
              ref={editor}
              border={50}
              color={[255, 255, 255, 0.6]} // RGBA
              image={selectedFile.origin}
              scale={selectedFile.zoom}
              rotate={selectedFile.rotate}
              borderRadius={selectedFile.radius}
            />
          </div>
          <div className="w-50">
            <div>
              <InputRange
                onChange={(value) => handleZoomChange(value)}
                labelText="Zoom"
                min={0}
                step={0.1}
                max={4}
                direction={Direction.Right}
                value={selectedFile.zoom[0]}
              />
            </div>
            <div>
              <InputRange
                onChange={(value) => handleRotateChange(value)}
                labelText="Rotate"
                min={0}
                step={1}
                max={360}
                direction={Direction.Right}
                value={selectedFile.rotate[0]}
              />
            </div>
            <div>
              <InputRange
                onChange={(value) => handleRadiusChange(value)}
                labelText="Radius"
                min={0}
                step={1}
                max={100}
                direction={Direction.Right}
                value={selectedFile.radius[0]}
              />
            </div>
          </div>
        </div>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={onClose}>
          Close
        </Button>
        <Button variant="primary" onClick={handleSave}>
          Save Changes
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
