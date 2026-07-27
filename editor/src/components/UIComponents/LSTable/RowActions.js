import React from 'react'

import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'

export const RowActions = ({
  actions = [],
  placement = 'left',
  testId = 'row-actions',
}) => {
  const items = actions.map((action) => ({
    label: action.label,
    onClick: action.onClick,
    icon: action.icon,
    className: action.danger ? 'meat-ball-delete-button' : action.className,
  }))

  return (
    <MeatballMenu
      shouldDisableIfSurveyActive={false}
      items={items}
      meatballClassName="ls-table-row-actions"
      placement={placement}
      testId={testId}
    />
  )
}
