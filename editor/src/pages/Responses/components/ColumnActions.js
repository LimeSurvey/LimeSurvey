import classNames from 'classnames'
import { ArrowDownIcon } from 'components/icons'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'

export const ColumnActions = ({
  handleColumnPin = () => {},
  handleColumnFilter = () => {},
  header,
}) => {
  const columnIsPinned = header.column.getIsPinned()
  return (
    <MeatballMenu
      shouldDisableIfSurveyActive={false}
      items={[
        {
          label: t('Filter column'),
          icon: <div className={classNames('ri-filter-line')}></div>,
          onClick: () => handleColumnFilter(header),
        },
        {
          label: columnIsPinned ? t('Unpin column') : t('Pin column'),
          icon: (
            <div
              className={classNames('pin-icon', {
                'ri-pushpin-2-line': !header.column.getIsPinned(),
                'ri-unpin-line': header.column.getIsPinned(),
              })}
            ></div>
          ),
          onClick: () => handleColumnPin(header),
        },
      ]}
      meatballClassName="column-meatball-menu"
      actionsTitle="Column Actions"
      placement="top"
      TogglerIcon={ArrowDownIcon}
    />
  )
}
