import { SideBarRow } from 'components/SideBar/SideBarRow'
import { PushPinIcon } from 'components/icons'

export const RowPinned = ({
  title,
  disabled = false,
  isFocused = false,
  onRowClick,
  testId,
}) => {
  return (
    <SideBarRow
      title={title}
      icon={<PushPinIcon />}
      className={disabled ? 'disabled' : ''}
      isPinned
      isFocused={isFocused}
      onRowClick={onRowClick}
      testId={testId}
    />
  )
}
