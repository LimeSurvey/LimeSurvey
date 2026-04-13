import { useState } from 'react'
import { Button } from 'react-bootstrap'

import { ImageEditor } from 'components/UIComponents'
import { DeleteIcon, EditIcon } from 'components/icons'

export const EditableImage = ({
  width,
  height,
  imageSrc,
  handleRemoveImage = () => {},
  showControllers = true,
  update,
}) => {
  const [selectedFile, setSelectedFile] = useState(null)
  const [show, setShow] = useState(false)

  const handleClose = () => setShow(false)
  const handleEditImage = () => {
    setShow(true)
    setSelectedFile({
      ...{
        ...imageSrc,
        zoom: 1,
        rotate: 0,
        radius: 0,
      },
    })
  }

  return (
    <div
      className="position-relative transition-all"
      style={{
        width: width ? width : '100%',
        overflow: 'hidden',
      }}
    >
      <img
        width={width ? width : '100%'}
        height={height ? height : '100%'}
        src={imageSrc?.preview}
        alt="welcome header"
        className="transition-all"
        id="sadlifetest"
        style={{
          borderRadius: `${imageSrc?.radius}px`, // remove logic and put borderRadius inside scss file
        }}
      />
      {showControllers && (
        <div className="position-absolute image-handle-btn-wrapper">
          <Button
            variant="outline-light"
            className="image-handle-btn ms-1"
            size="sm"
            onClick={handleEditImage}
          >
            <EditIcon className="text-primary fill-current" />
          </Button>
          <Button
            variant="outline-light"
            className="image-handle-btn ms-1"
            size="sm"
            onClick={handleRemoveImage}
          >
            <DeleteIcon className="text-primary fill-current" />
          </Button>
        </div>
      )}

      {selectedFile && (
        <ImageEditor
          showModal={show}
          onClose={handleClose}
          onChange={(file) => update({ ...file })}
          file={selectedFile}
        />
      )}
    </div>
  )
}
