import { ToggleButtons } from 'components'
import { useEffect, useState } from 'react'

export const ExportPopupHTML = ({ exportOptions }) => {
  const [options, setOptions] = useState({
    exportFormat: '0',
    dataView: '0',
    onlyCompleted: '0',
  })

  useEffect(() => {
    exportOptions.current = {
      exportFormat: '0',
      dataView: '0',
      onlyCompleted: '0',
    }
  }, [])

  const handleExportFormatChange = (value) => {
    setOptions({ ...options, exportFormat: value })
    exportOptions.current = { ...exportOptions.current, exportFormat: value }
  }

  const handleExportDataViewChange = (value) => {
    setOptions({ ...options, dataView: value })
    exportOptions.current = { ...exportOptions.current, dataView: value }
  }

  const handleExportOnlyCompletedChange = (value) => {
    setOptions({ ...options, onlyCompleted: value })
    exportOptions.current = { ...exportOptions.current, onlyCompleted: value }
  }

  return (
    <div className="responses-export">
      <h2 className="title">{t('Export survey responses')}</h2>
      <hr />
      <div className="export-option ">
        <h6>{t('Export format')}</h6>
        <div>
          <ToggleButtons
            toggleOptions={[
              { name: t('PDF'), value: '0' },
              { name: t('CSV'), value: '1' },
              { name: t('XLS'), value: '2' },
            ]}
            id={`responses-export-format`}
            className="condition-toggle"
            value={options.exportFormat}
            onChange={handleExportFormatChange}
          />
        </div>
      </div>
      <div className="export-option ">
        <h6>{t('Data view')}</h6>
        <div>
          <ToggleButtons
            toggleOptions={[
              { name: t('Filtered Data'), value: '0' },
              { name: t('All Data'), value: '1' },
            ]}
            id={`responses-export-data`}
            className="condition-toggle"
            value={options.dataView}
            onChange={handleExportDataViewChange}
          />
        </div>
      </div>
      <div className="export-option ">
        <h6>{t('Only completed')}</h6>
        <div>
          <ToggleButtons
            toggleOptions={[
              { name: t('Yes'), value: '0' },
              { name: t('No'), value: '1' },
            ]}
            id={`responses-export-completed`}
            className="condition-toggle"
            value={options.onlyCompleted}
            onChange={handleExportOnlyCompletedChange}
          />
        </div>
      </div>
      <hr />
    </div>
  )
}
