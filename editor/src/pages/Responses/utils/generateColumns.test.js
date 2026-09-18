import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { getSiteUrl } from 'helpers'
import { generateColumns } from './generateColumns'

test('the quota exit column links the quota ID and shows its name on hover', async () => {
  const quotaColumn = generateColumns(
    {},
    { sid: 123, saveQuotaExit: true }
  ).find(({ id }) => id === 'quotaExit')
  const QuotaCell = quotaColumn.cell

  render(
    <QuotaCell
      getValue={() => 42}
      row={{ original: { quotaExitName: 'Young respondents' } }}
    />
  )

  const link = screen.getByRole('link', { name: '42' })
  expect(link).toHaveAttribute(
    'href',
    getSiteUrl('/quotas/editQuota/surveyid/123?quota_id=42')
  )
  expect(link).toHaveAttribute('target', '_blank')

  fireEvent.mouseOver(link)
  expect(await screen.findByRole('tooltip')).toHaveTextContent(
    'Young respondents'
  )
})
