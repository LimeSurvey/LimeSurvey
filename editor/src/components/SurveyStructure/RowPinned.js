import { SideBarRow } from 'components/SideBar/SideBarRow'
import { PushPinIcon } from 'components/icons'

export const RowPinned = ({
  title,
  disabled = false,
  isFocused = false,
  onRowClick,
}) => {
  return (
    <SideBarRow
      title={title}
      icon={<PushPinIcon />}
      className={disabled ? 'disabled' : ''}
      isFocused={isFocused}
      onRowClick={onRowClick}
    />
  )
}
