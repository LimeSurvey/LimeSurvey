import { expect, waitFor } from '@storybook/test'
import { userEvent, within } from '@storybook/test'
import { ContentEditor as ContentEditorComponent } from '../ContentEditor/ContentEditor'

export default {
  title: 'UIComponents/ContentEditor',
  component: ContentEditorComponent,
}

export const ContentEditor = () => {
  return (
    <ContentEditorComponent
      testId="content-editor"
      placeholder="Enter your comment"
    />
  )
}

ContentEditor.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('content-editor'))

  await step('Should have the value "random text 123"', async () => {
    const contentEditor = getByTestId('content-editor')
    await userEvent.type(contentEditor, 'random text 123', {})

    await expect(contentEditor.innerText).toBe('random text 123')
  })
}
