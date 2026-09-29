import { ContentEditor as ContentEditorComponent } from '../ContentEditor/ContentEditor'

export default {
  title: 'imageWrapper/ContentEditor',
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
