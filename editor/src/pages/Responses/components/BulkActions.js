import classNames from 'classnames'
import { Button } from 'components'
import {
  DeleteIcon,
  DownloadIcon,
  Seprator,
  // UploadIcon,
  XIcon,
} from 'components/icons'

export const BulkActions = ({
  table,
  selectedCount = 0,
  onDeleteClick = () => {},
  onAttachmentsDeleteClick = () => {},
  onDownloadFilesClick = () => {},
  onUnselectAll = () => {},
}) => {
  const tableHasRowsSelected = selectedCount > 0

  const handleUnSelectAll = () => {
    table.toggleAllRowsSelected(false)
    onUnselectAll()
  }

  return (
    <div
      className={classNames(`bulk-actions`, {
        'pointer-events-none': !tableHasRowsSelected,
      })}
      style={{
        opacity: tableHasRowsSelected ? '1' : '0',
      }}
    >
      <div className="number-selected">
        <span className="number">{selectedCount}</span> {t('selected')}
      </div>
      <div className="seprator">
        <Seprator />
      </div>
      {/* <Button className="primary" variant="none">
        <UploadIcon className="bulk-icon primary" />
        {t('Export')}
      </Button> */}
      <Button onClick={onDownloadFilesClick} variant="none">
        <DownloadIcon fill="currentColor" className="bulk-icon" />
        {t('Download files')}
      </Button>
      <Button onClick={onAttachmentsDeleteClick} variant="none">
        <DeleteIcon fill="currentColor" className="bulk-icon" />
        {t('Delete attachments')}
      </Button>
      <Button onClick={onDeleteClick} variant="none" className="text-danger">
        <DeleteIcon fill="currentColor" className="bulk-icon text-danger" />
        {t('Delete')}
      </Button>
      <Button onClick={handleUnSelectAll} className="x-button" variant="none">
        <XIcon className="bulk-icon" />
      </Button>
    </div>
  )
}
