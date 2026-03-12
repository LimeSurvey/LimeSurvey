import {
  ColumnsIcon,
  DeleteIcon,
  DownloadIcon,
  // EditIcon,
  EyeIcon,
} from 'components/icons'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'

export const SelectColumnId = 'column-select'
export const ActionsColumnId = 'response-actions'

export const getDefaultColumns = ({
  onResponseDetailViewClick = () => {},
  deleteCallback = () => {},
  showColumnsManagment = () => {},
  onDownloadAllFilesClick = () => {},
  onDeleteResponseFilesClick = () => {},
}) => {
  return {
    SELECT: {
      id: SelectColumnId,
      maxSize: 60,
      header: ({ table }) => (
        <input
          type="checkbox"
          className="form-check-input"
          checked={table.getIsAllRowsSelected()}
          onChange={table.getToggleAllRowsSelectedHandler()}
        />
      ),
      cell: ({ row }) => (
        <input
          type="checkbox"
          className="form-check-input"
          checked={row.getIsSelected()}
          onChange={row.getToggleSelectedHandler()}
        />
      ),
    },
    ACTIONS: {
      id: ActionsColumnId,
      header: (
        <span onClick={showColumnsManagment}>
          <ColumnsIcon />
        </span>
      ),
      cell: () => (
        <MeatballMenu
          shouldDisableIfSurveyActive={false}
          items={[
            {
              label: t('View/Edit response details'),
              icon: <EyeIcon />,
              onClick: onResponseDetailViewClick,
            },
            // {
            //   label: t('Edit this response'),
            //   icon: <EditIcon />,
            //   onClick: () => {},
            // },
            {
              label: t('Download all response files'),
              icon: <DownloadIcon height={16} width={16} />,
              onClick: onDownloadAllFilesClick,
            },
            {
              label: t('Delete all response files'),
              icon: <DeleteIcon height={16} width={16} />,
              onClick: onDeleteResponseFilesClick,
            },
            {
              label: t('Delete this response'),
              icon: <DeleteIcon height={16} width={16} />,
              onClick: deleteCallback,
              className: 'delete-response-action',
            },
          ]}
          meatballClassName="responses-meatball-menu"
          actionsTitle="Response Actions"
          placement="left"
        />
      ),
    },
  }
}
