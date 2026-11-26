import classNames from 'classnames'
import { TooltipContainer } from 'components'
import { DragIcon } from 'components/icons'
import { getTooltipMessages } from 'helpers/options'

// Drag icon of an answer option or subquestion, greyed out with the active survey tooltip when the child cannot be reordered
export const ChildDragIcon = ({ isLocked = false, className = '' }) => {
  const icon = (
    <DragIcon className={classNames(className, { disabled: isLocked })} />
  )

  if (!isLocked) {
    return icon
  }

  return (
    <TooltipContainer showTip={true} tip={getTooltipMessages().ACTIVE_DISABLED}>
      {icon}
    </TooltipContainer>
  )
}
