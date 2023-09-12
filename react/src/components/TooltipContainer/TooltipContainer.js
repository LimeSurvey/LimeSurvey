import { OverlayTrigger, Tooltip } from 'react-bootstrap'

export const TooltipContainer = ({ children, tip, placement = 'top' }) => {
  return (
    <OverlayTrigger
      placement={placement}
      delay={{ show: 100, hide: 100 }}
      overlay={tip ? <Tooltip>{tip}</Tooltip> : <> </>}
    >
      <span>{children}</span>
    </OverlayTrigger>
  )
}
