import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { SideBarRow } from './SideBarRow'

let mockShowCode = true
jest.mock('hooks/useSurvey', () => ({
  useSurvey: () => ({
    survey: {
      ...require('helpers/data/survey-detail.json').survey,
      showQNumCode: { showCode: mockShowCode },
    },
    update: () => {},
  }),
}))

const menuItems = [{ type: 'item', label: 'Delete', onClick: () => {} }]

describe('SideBarRow', () => {
  beforeEach(() => {
    mockShowCode = true
  })

  test('shows the question code badge when question codes are enabled', async () => {
    await renderWithProviders(
      <SideBarRow title="Q" code="Q006" menuItems={menuItems} />
    )
    expect(await screen.findByTestId('question-code-badge')).toHaveTextContent(
      'Q006'
    )
  })

  test('hides the question code badge when question codes are disabled', async () => {
    mockShowCode = false
    await renderWithProviders(
      <SideBarRow title="Q" code="Q006" menuItems={menuItems} />
    )
    await screen.findByText('Q')
    expect(screen.queryByTestId('question-code-badge')).toBeNull()
  })

  test('pinned rows have no badge and no menu', async () => {
    await renderWithProviders(<SideBarRow title="Welcome" />)
    await screen.findByText('Welcome')
    expect(screen.queryByTestId('question-code-badge')).toBeNull()
    expect(screen.queryByRole('menu')).toBeNull()
  })

  test('exposes selection through aria-current', async () => {
    await renderWithProviders(
      <SideBarRow title="Welcome" testId="row" isFocused />
    )
    const row = await screen.findByTestId('row')
    expect(row).toHaveAttribute('aria-current', 'true')
    expect(row).toHaveClass('structure-item-selected')
  })

  test('pinned row inside a selected core wrapper is marked current', async () => {
    await renderWithProviders(
      <div className="focus-bg-purple">
        <SideBarRow title="Welcome" testId="row" />
      </div>
    )
    expect(await screen.findByTestId('row')).toHaveAttribute(
      'aria-current',
      'true'
    )
  })

  test('is not marked current when not selected', async () => {
    await renderWithProviders(<SideBarRow title="Welcome" testId="row" />)
    const row = await screen.findByTestId('row')
    expect(row).not.toHaveAttribute('aria-current')
  })

  test('activates on click and Enter', async () => {
    const onRowClick = jest.fn()
    await renderWithProviders(
      <SideBarRow title="End" testId="row" onRowClick={onRowClick} />
    )
    const row = await screen.findByTestId('row')
    await userEvent.click(row)
    expect(onRowClick).toHaveBeenCalledTimes(1)
    row.focus()
    await userEvent.keyboard('{Enter}')
    expect(onRowClick).toHaveBeenCalledTimes(2)
  })

  test('group arrow exposes aria-expanded and does not activate the row', async () => {
    const onRowClick = jest.fn()
    await renderWithProviders(
      <SideBarRow
        title="Group"
        isQuestionGroup
        onRowClick={onRowClick}
        menuItems={menuItems}
      />
    )
    const arrow = await screen.findByTestId('sidebar-row-toggler-button')
    expect(arrow).toHaveAttribute('aria-expanded', 'false')
    await userEvent.click(arrow)
    expect(arrow).toHaveAttribute('aria-expanded', 'true')
    expect(onRowClick).not.toHaveBeenCalled()
  })

  test('menu toggle opens the menu without activating the row', async () => {
    const onRowClick = jest.fn()
    await renderWithProviders(
      <SideBarRow
        title="Q"
        code="Q1"
        onRowClick={onRowClick}
        menuItems={menuItems}
        menuToggleId="menu-toggle"
      />
    )
    const toggle = await screen.findByRole('menu', { name: 'Question menu' })
    await userEvent.click(toggle)
    expect(await screen.findByText('Delete')).toBeInTheDocument()
    expect(onRowClick).not.toHaveBeenCalled()
  })
})
