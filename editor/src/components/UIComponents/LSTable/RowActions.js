import React from 'react'
import PropTypes from 'prop-types'

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

RowActions.propTypes = {
  actions: PropTypes.arrayOf(
    PropTypes.shape({
      label: PropTypes.string.isRequired,
      onClick: PropTypes.func.isRequired,
      danger: PropTypes.bool,
      icon: PropTypes.node,
      className: PropTypes.string,
    })
  ),
  placement: PropTypes.string,
  testId: PropTypes.string,
}
