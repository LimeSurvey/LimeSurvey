import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { ColumnsManagement } from './ColumnsManagement'

const createColumn = ({
  id,
  header,
  isTiming = false,
  qid,
  questionLabel,
  visible = true,
}) => ({
  id,
  columnDef: {
    header,
    meta: {
      ...(isTiming && { columnCategory: 'timing' }),
      ...(qid != null && { qid }),
      ...(questionLabel && { questionLabel }),
    },
  },
  getIsVisible: () => visible,
})

describe('ColumnsManagement', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  test('groups timings without drag controls and appends them on confirm', async () => {
    const user = userEvent.setup()
    const handleConfirm = jest.fn()
    const columns = [
      createColumn({ id: 'column-select', header: 'Select' }),
      createColumn({ id: 'id', header: 'ID' }),
      createColumn({ id: 'completed', header: 'Completed' }),
      createColumn({
        id: 'interviewtime',
        header: 'Total time (in s)',
        isTiming: true,
        visible: false,
      }),
      createColumn({
        id: 'Q42time',
        header: 'Question time: Q3',
        isTiming: true,
        qid: 42,
        questionLabel: {
          code: 'Q3',
          text: 'Where are you?',
        },
      }),
      createColumn({ id: 'dateLastAction', header: 'Date of last action' }),
      createColumn({
        id: '42',
        header: 'Where are you?',
        qid: 42,
        questionLabel: {
          code: 'Q3',
          text: 'Where are you?',
        },
      }),
      createColumn({ id: 'response-actions', header: 'Actions' }),
    ]

    await renderWithProviders(
      <ColumnsManagement
        table={{ getAllLeafColumns: () => columns }}
        handleOnColumnsManagementConfirm={handleConfirm}
      />
    )

    expect(screen.getAllByText('Q3')[0]).toHaveClass('column-question-code')

    const timingToggle = screen.getByRole('button', { name: 'Timings' })

    expect(timingToggle).toHaveAttribute('aria-expanded', 'false')
    expect(screen.getByTestId('timings-info-icon')).toBeInTheDocument()
    expect(screen.queryByTestId('timing-columns-container')).toBeNull()

    await user.click(screen.getByRole('button', { name: 'Select all' }))
    await user.click(screen.getByLabelText('Q3 Where are you?'))
    await user.click(timingToggle)

    const timingContainer = screen.getByTestId('timing-columns-container')
    const timingCheckbox =
      within(timingContainer).getByLabelText('Total time (in s)')

    expect(timingCheckbox).not.toBeChecked()
    expect(timingContainer.querySelector('.cm-drag-icon')).toBeNull()
    const questionTimingCheckbox = within(timingContainer).getByLabelText(
      'Question time: Q3 Where are you?'
    )
    expect(within(timingContainer).getByText('Q3')).toHaveClass(
      'column-question-code'
    )
    expect(questionTimingCheckbox).not.toBeDisabled()
    expect(questionTimingCheckbox).not.toBeChecked()

    await user.click(timingCheckbox)
    await user.click(screen.getByRole('button', { name: 'Confirm' }))

    expect(handleConfirm).toHaveBeenCalledWith(
      expect.arrayContaining([
        expect.objectContaining({ id: 'interviewtime', checked: true }),
      ])
    )
    expect(handleConfirm.mock.calls[0][0].map(({ id }) => id)).toEqual([
      'column-select',
      'id',
      'completed',
      'dateLastAction',
      '42',
      'interviewtime',
      'Q42time',
      'response-actions',
    ])
  })
})
