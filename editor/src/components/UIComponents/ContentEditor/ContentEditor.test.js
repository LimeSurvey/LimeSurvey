// Import shared mocks
import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { renderWithProviders } from 'tests/testUtils'
import { ContentEditor } from './ContentEditor'

jest.mock('./TinyMCE/TinyMCE', () => {
  const React = require('react')

  return {
    TinyMCE: ({ testId = 'content-editor' }) =>
      React.createElement('div', {
        'data-testid': `${testId}-tinymce`,
      }),
  }
})

describe('ContentEditor', () => {
  test('Should have the value "random text 123"', async () => {
    await renderWithProviders(
      <ContentEditor testId="content-editor" placeholder="Enter your comment" />
    )

    const contentEditor = screen.getByTestId('content-editor')

    await userEvent.type(contentEditor, 'random text 123')

    expect(contentEditor.innerHTML).toBe('random text 123')
  })

  test('Should render TinyMCE when toolbar is enabled', async () => {
    await renderWithProviders(
      <ContentEditor
        testId="content-editor"
        placeholder="Enter your comment"
        showToolbar={true}
      />
    )

    expect(screen.getByTestId('content-editor-tinymce')).toBeInTheDocument()
  })
})
