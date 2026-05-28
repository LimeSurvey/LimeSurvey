import { Table } from 'react-bootstrap'

export const StatisticsTable = ({ data = [] }) => {
  const total = data.reduce((sum, item) => sum + (Number(item.value) || 0), 0)

  return (
    <Table className="table statistics-table">
      <thead>
        <tr>
          <th>{t('Answer')}</th>
          <th className="text-end">{t('Responses')}</th>
          <th className="text-end">{t('Percentage')}</th>
        </tr>
      </thead>
      <tbody>
        {data.map((item, index) => (
          <tr key={`statistics-table-row-${index}`}>
            <td>
              <span
                className="statistics-table-swatch"
                style={{ backgroundColor: item.fill }}
              />
              {item.title}
            </td>
            <td className="text-end">{item.value}</td>
            <td className="text-end">{item.percentage}%</td>
          </tr>
        ))}
      </tbody>
      <tfoot>
        <tr>
          <td>{t('Total')}</td>
          <td className="text-end">{total}</td>
          <td className="text-end">100%</td>
        </tr>
      </tfoot>
    </Table>
  )
}
