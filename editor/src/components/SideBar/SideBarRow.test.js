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
    await renderWithProviders(<SideBarRow title="Welcome" code="" isPinned />)
    await screen.findByText('Welcome')
    expect(screen.queryByTestId('question-code-badge')).toBeNull()
    expect(screen.queryByRole('button', { name: 'Question menu' })).toBeNull()
  })

  test('exposes selection through aria-current', async () => {
    await renderWithProviders(
      <SideBarRow title="Welcome" testId="row" isPinned isFocused />
    )
    const row = await screen.findByTestId('row')
    expect(row).toHaveAttribute('aria-current', 'true')
    expect(row).toHaveClass('structure-item-selected')
  })

  test('is not marked current when not selected', async () => {
    await renderWithProviders(
      <SideBarRow title="Welcome" testId="row" isPinned />
    )
    const row = await screen.findByTestId('row')
    expect(row).not.toHaveAttribute('aria-current')
  })

  test('activates on click and Enter', async () => {
    const onRowClick = jest.fn()
    await renderWithProviders(
      <SideBarRow title="End" testId="row" isPinned onRowClick={onRowClick} />
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
    const onTitleClick = jest.fn()
    await renderWithProviders(
      <SideBarRow
        title="Group"
        isQuestionGroup
        onRowClick={onRowClick}
        onTitleClick={onTitleClick}
        menuItems={menuItems}
      />
    )
    const arrow = await screen.findByTestId('sidebar-row-toggler-button')
    expect(arrow).toHaveAttribute('aria-expanded', 'false')
    await userEvent.click(arrow)
    expect(arrow).toHaveAttribute('aria-expanded', 'true')
    expect(onRowClick).not.toHaveBeenCalled()
    expect(onTitleClick).not.toHaveBeenCalled()
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
    const toggle = await screen.findByRole('button', { name: 'Question menu' })
    expect(toggle).toHaveAttribute('aria-haspopup', 'menu')
    await userEvent.click(toggle)
    expect(await screen.findByText('Delete')).toBeInTheDocument()
    expect(onRowClick).not.toHaveBeenCalled()
  })

  test('a question with an undefined code is still a question row', async () => {
    await renderWithProviders(
      <SideBarRow
        title="Q"
        code={undefined}
        testId="row"
        menuItems={menuItems}
      />
    )
    const row = await screen.findByTestId('row')
    expect(row).toHaveClass('structure-item-question')
    expect(row).not.toHaveAttribute('role')
  })

  test('rows stay keyboard-reachable when dragging is disabled', async () => {
    const onRowClick = jest.fn()
    await renderWithProviders(
      <SideBarRow
        title="Q"
        code="Q1"
        provided={{ dragHandleProps: null }}
        onRowClick={onRowClick}
      />
    )
    const main = document.querySelector('.structure-item-main')
    expect(main).toHaveAttribute('tabindex', '0')
    expect(main).toHaveStyle({ cursor: 'pointer' })
    main.focus()
    await userEvent.keyboard('{Enter}')
    expect(onRowClick).toHaveBeenCalledTimes(1)
  })

  test('the group test id sits on the row element', async () => {
    await renderWithProviders(
      <SideBarRow title="Group" isQuestionGroup testId="group-row" />
    )
    expect(await screen.findByTestId('group-row')).toHaveClass(
      'structure-item-group'
    )
  })

  test('opening a menu on another row closes the first one', async () => {
    await renderWithProviders(
      <>
        <SideBarRow title="A" code="A1" menuItems={menuItems} />
        <SideBarRow
          title="B"
          code="B1"
          menuItems={[{ type: 'item', label: 'Other', onClick: () => {} }]}
        />
      </>
    )
    const [a, b] = await screen.findAllByRole('button', {
      name: 'Question menu',
    })
    await userEvent.click(a)
    expect(a).toHaveAttribute('aria-expanded', 'true')
    await userEvent.click(b)
    expect(b).toHaveAttribute('aria-expanded', 'true')
    expect(a).toHaveAttribute('aria-expanded', 'false')
  })
})
