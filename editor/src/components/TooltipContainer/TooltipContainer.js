import React, { useId, useMemo } from 'react'
import { OverlayTrigger, Tooltip } from 'react-bootstrap'

export const TooltipContainer = ({
  children,
  offset = [0, 10],
  tip,
  placement = 'top',
  showTip = true,
  tooltipClassName = '',
}) => {
  const id = useId()
  const overlay = useMemo(
    () => (
      <Tooltip id={`tooltip-${id}`} className={tooltipClassName}>
        {tip}
      </Tooltip>
    ),
    [tip, tooltipClassName]
  )

  return (
    <OverlayTrigger
      placement={placement}
      offset={offset}
      overlay={showTip && tip ? overlay : <></>}
    >
      <span>{children}</span>
    </OverlayTrigger>
  )
}
