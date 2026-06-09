import { TooltipContainer } from './TooltipContainer'

export default {
  title: 'General/Tooltip',
  component: TooltipContainer,
}

export const Tooltip = () => (
  <div>
    <TooltipContainer placement={'right'} tip={`This is a tool tip`}>
      <span className="title">Hover me to see a right tooltip</span>
    </TooltipContainer>
    <br />
    <div>tooltips can have other placement like:</div>
    <div>
      top, bottom, left, right top-start, top-end, bottom-start, bottom-end,
      right-start, right-end, left-start, left-end auto, auto-start, auto-end
    </div>
  </div>
)
