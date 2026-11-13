import { Button } from 'react-bootstrap'

import { DeleteIcon } from 'components/icons'

/**
 * An image with a delete button on it. There is no edit button: The image
 * editor only supports the options of the image select themes.
 *
 * @param {string} width width of the image, e.g. "200px", or the full width
 * @param {string} height height of the image, or the full height
 * @param {Object} imageSrc the image, with its URL in preview and its corner
 *   radius in radius
 * @param {Function} handleRemoveImage called when the delete button is clicked
 * @param {boolean} showControllers whether the delete button is shown
 * @returns {JSX.Element} the image
 */
export const EditableImage = ({
  width,
  height,
  imageSrc,
  handleRemoveImage = () => {},
  showControllers = true,
}) => {
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
            aria-label={t('Delete image')}
            onClick={handleRemoveImage}
          >
            <DeleteIcon className="text-primary fill-current" />
          </Button>
        </div>
      )}
    </div>
  )
}
