import 'tests/mocks'

import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { ColumnsManagement } from './ColumnsManagement'

const createColumn = ({
  id,
  header,
  isTiming = false,
  timingType,
  qid,
  questionLabel,
  visible = true,
}) => ({
  id,
  columnDef: {
    header,
    meta: {
      ...(isTiming && { columnCategory: 'timing' }),
      ...(timingType && { timingType }),
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
        timingType: 'interview_time',
        visible: false,
      }),
      createColumn({
        id: 'G1time',
        header: 'Group time: Group 1',
        isTiming: true,
        timingType: 'page_time',
        visible: false,
      }),
      createColumn({
        id: 'Q42time',
        header: 'Question time: Q3',
        isTiming: true,
        timingType: 'answer_time',
        qid: 42,
        questionLabel: {
          code: 'Q3',
          text: 'Where are you?',
        },
      }),
      createColumn({
        id: 'Q43time',
        header: 'Question time: Q4',
        isTiming: true,
        timingType: 'answer_time',
        qid: 43,
        visible: false,
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

    render(
      <ColumnsManagement
        table={{ getAllLeafColumns: () => columns }}
        saveTimings={true}
        handleOnColumnsManagementConfirm={handleConfirm}
      />
    )

    expect(screen.getAllByText('Q3')[0]).toHaveClass('column-question-code')

    const timingToggle = screen.getByRole('button', { name: 'Timings' })
    const timingInfo = screen.getByRole('button', {
      name: 'About survey timings',
    })

    expect(timingToggle).toHaveAttribute('aria-expanded', 'false')
    expect(timingInfo).toBe(screen.getByTestId('timings-info-icon'))
    expect(screen.queryByTestId('timing-columns-container')).toBeNull()

    await user.click(screen.getByRole('button', { name: 'Select all' }))
    await user.click(timingToggle)

    const timingContainer = screen.getByTestId('timing-columns-container')
    const timingCheckbox =
      within(timingContainer).getByLabelText('Total time (s)')

    expect(timingCheckbox).not.toBeChecked()
    expect(timingContainer.querySelector('.cm-drag-icon')).toBeNull()
    const questionTimingCheckbox =
      within(timingContainer).getByLabelText('Question time (s)')
    expect(questionTimingCheckbox).not.toBeDisabled()
    expect(questionTimingCheckbox).toBePartiallyChecked()
    expect(
      within(timingContainer).getByLabelText('Group time (s)')
    ).toBeVisible()
    expect(within(timingContainer).queryByText('Where are you?')).toBeNull()

    await user.click(timingCheckbox)
    await user.click(screen.getByRole('button', { name: 'Clear selection' }))
    expect(timingCheckbox).toBeChecked()

    await user.click(timingCheckbox)
    await user.click(screen.getByRole('button', { name: 'Select all' }))
    expect(timingCheckbox).not.toBeChecked()

    await user.click(timingCheckbox)
    await user.click(questionTimingCheckbox)
    await user.click(screen.getByRole('button', { name: 'Confirm' }))

    expect(handleConfirm).toHaveBeenCalledWith(
      expect.arrayContaining([
        expect.objectContaining({ id: 'interviewtime', checked: true }),
        expect.objectContaining({ id: 'Q42time', checked: true }),
        expect.objectContaining({ id: 'Q43time', checked: true }),
      ])
    )
    expect(handleConfirm.mock.calls[0][0].map(({ id }) => id)).toEqual([
      'column-select',
      'id',
      'completed',
      'dateLastAction',
      '42',
      'interviewtime',
      'G1time',
      'Q42time',
      'Q43time',
      'response-actions',
    ])
  })

  test('keeps timings visible but disabled when saving timings is off', async () => {
    const user = userEvent.setup()
    const columns = [
      createColumn({
        id: 'interviewtime',
        header: 'Total time (in s)',
        isTiming: true,
        timingType: 'interview_time',
        visible: true,
      }),
    ]

    const { rerender } = render(
      <ColumnsManagement
        table={{ getAllLeafColumns: () => columns }}
        saveTimings={false}
      />
    )

    const timingToggle = screen.getByRole('button', { name: 'Timings' })
    expect(timingToggle).toBeVisible()
    expect(
      screen.getByRole('button', { name: 'About survey timings' })
    ).toBeVisible()

    await user.click(timingToggle)

    const timingContainer = screen.getByTestId('timing-columns-container')
    const disabledTotalTimingCheckbox =
      within(timingContainer).getByLabelText('Total time (s)')
    expect(disabledTotalTimingCheckbox).toBeDisabled()
    expect(
      disabledTotalTimingCheckbox.closest('.timing-column-item')
    ).toHaveClass('disabled')
    expect(
      within(timingContainer).getByLabelText('Group time (s)')
    ).toBeDisabled()
    expect(
      within(timingContainer).getByLabelText('Question time (s)')
    ).toBeDisabled()

    rerender(
      <ColumnsManagement
        table={{ getAllLeafColumns: () => columns }}
        saveTimings={true}
      />
    )

    const totalTimingCheckbox =
      within(timingContainer).getByLabelText('Total time (s)')
    expect(totalTimingCheckbox).toBeEnabled()
    expect(totalTimingCheckbox).toBeChecked()
  })

  test('shows disabled timing checkboxes without timing metadata', async () => {
    const user = userEvent.setup()

    render(
      <ColumnsManagement
        table={{ getAllLeafColumns: () => [] }}
        saveTimings={false}
      />
    )

    expect(screen.getByRole('button', { name: 'Timings' })).toBeVisible()
    expect(
      screen.getByRole('button', { name: 'About survey timings' })
    ).toBeVisible()

    await user.click(screen.getByRole('button', { name: 'Timings' }))

    const timingContainer = screen.getByTestId('timing-columns-container')
    expect(
      within(timingContainer).getByLabelText('Total time (s)')
    ).toBeDisabled()
    expect(
      within(timingContainer).getByLabelText('Group time (s)')
    ).toBeDisabled()
    expect(
      within(timingContainer).getByLabelText('Question time (s)')
    ).toBeDisabled()
  })

  test('shows a styled question code when the question text is empty', async () => {
    const columns = [
      createColumn({
        id: '42',
        header: 'Q00',
        qid: 42,
        questionLabel: { code: 'Q00', text: '' },
      }),
    ]

    render(<ColumnsManagement table={{ getAllLeafColumns: () => columns }} />)

    expect(screen.getByText('Q00')).toHaveClass('column-question-code')
    expect(screen.getByLabelText('Q00')).toBeInTheDocument()
  })
})
