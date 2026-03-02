import { userEvent, waitFor, within, expect } from '@storybook/test'

import { LeftSideBar as LeftSideBarComponent } from './LeftSideBar'
import { getSurveyPanels } from 'helpers/options'

const meta = {
  title: 'Page/Editor/LeftSideBar',
  component: LeftSideBarComponent,
  argTypes: {
    structurePanel: {
      name: 'Toggle structure panel',
      control: { type: 'boolean' },
    },
    settingsPanel: {
      name: 'Toggle settings panel',
      control: { type: 'boolean' },
    },
  },
}

export default meta

export const LeftSideBar = () => {
  return <LeftSideBarComponent />
}

LeftSideBar.play = async ({ canvasElement, step }) => {
  const { getByTestId, queryByTestId, findByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('left-sidebar'))

  await step('Should be able to toggle the structure panel', async () => {
    const closeButton = await queryByTestId('btn-close-structure')

    if (closeButton) {
      await userEvent.click(closeButton)
      await expect(
        await queryByTestId('editor-structure-panel')
      ).not.toBeInTheDocument()

      await userEvent.click(
        await getByTestId(`btn-${getSurveyPanels().structure.panel}-open`)
      )

      await expect(
        await findByTestId('editor-structure-panel')
      ).toBeInTheDocument()
    } else {
      await userEvent.click(
        await getByTestId(`btn-${getSurveyPanels().structure.panel}-open`)
      )
      await expect(
        await findByTestId('editor-structure-panel')
      ).toBeInTheDocument()

      await userEvent.click(await queryByTestId('btn-close-structure'))
      await expect(
        await queryByTestId('editor-structure-panel')
      ).not.toBeInTheDocument()
    }
  })
}
