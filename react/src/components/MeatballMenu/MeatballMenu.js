import React, { useState } from 'react'
import { Button, OverlayTrigger } from 'react-bootstrap'
import { useAppState } from 'hooks'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { SmallThreeDotIcon } from 'components/icons'

export const MeatballMenu = ({
  duplicateText,
  deleteText,
  handleDuplicate,
  handleDelete,
}) => {
  const [showMeatballMenu, setShowMeatballMenu] = useState(false)
  const [isSurveyActive] = useAppState('isSurveyActive', false)

  const onDuplicate = () => {
    setShowMeatballMenu(false)
    handleDuplicate()
  }

  const onDelete = () => {
    setShowMeatballMenu(false)
    handleDelete()
  }

  const meatballMenu = (
    <div className="meatball-menu ps-3 bg-white">
      <TooltipContainer
        tip={isSurveyActive && 'Disabled while survey is published.'}
      >
        <Button
          disabled={isSurveyActive}
          variant="layout"
          onClick={onDuplicate}
        >
          {duplicateText}
        </Button>
      </TooltipContainer>
      <TooltipContainer
        tip={isSurveyActive && 'Disabled while survey is published.'}
      >
        <Button disabled={isSurveyActive} variant="layout" onClick={onDelete}>
          {deleteText}
        </Button>
      </TooltipContainer>
    </div>
  )

  return (
    <OverlayTrigger
      overlay={meatballMenu}
      trigger="click"
      placement="right"
      show={showMeatballMenu}
      onToggle={(show) => {
        setShowMeatballMenu(show)
      }}
      offset={[6, 22]}
      rootClose
    >
      <Button variant="outline" className="p-0">
        <SmallThreeDotIcon />
      </Button>
    </OverlayTrigger>
  )
}
