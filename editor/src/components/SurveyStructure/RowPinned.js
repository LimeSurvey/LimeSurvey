import { SideBarRow } from 'components/SideBar/SideBarRow'
import { PushPinIcon } from 'components/icons'

export const RowPinned = ({ title, disabled = false }) => {
  return (
    <SideBarRow
      title={title}
      icon={<PushPinIcon />}
      style={{ marginLeft: 20 }}
      className={disabled ? 'disabled' : ''}
    />
  )
}
