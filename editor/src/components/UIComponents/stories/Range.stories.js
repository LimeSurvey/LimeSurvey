import { waitFor, within } from '@storybook/test'
import { Range as RangeComponent } from '../Range/Range'
import { expect } from '@storybook/test'

export default {
  title: 'UIComponents/Range',
  component: RangeComponent,
}

export const Range = () => {
  return <RangeComponent />
}

Range.play = async ({ canvasElement, step }) => {
  const { getByTestId, getByRole } = within(canvasElement)
  await waitFor(() => getByTestId('range'), { timeout: 10000 })
  const container = getByTestId('range')
  const slider = getByRole('slider')

  await step('Should render Range correctly', async () => {
    await expect(container).toBeInTheDocument()
    await expect(slider).toBeInTheDocument()
  })
}
