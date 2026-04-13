import { useRef, useState } from 'react'
import { DateRangePicker as ReactDateRangePicker } from 'react-date-range'
import { format } from 'date-fns'

import { useElementClick } from 'hooks'
import { Button } from '../Buttons'

export const DateRangePicker = ({
  startDate = null,
  endDate = null,
  minDate = new Date('2000-04-17T15:30'),
  maxDate = new Date('2050-04-17T15:30'),
  onUpdate = () => {},
  dateFormat = 'yyyy-MM-dd',
}) => {
  const [range, setRange] = useState([
    {
      startDate,
      endDate,
      key: 'selection',
    },
  ])
  const [tempRange, setTempRange] = useState([
    {
      startDate,
      endDate,
      key: 'selection',
    },
  ])

  const [showPicker, setShowPicker] = useState(false)
  const triggerRef = useRef(null)
  const [pickerPosition, setPickerPosition] = useState({ top: 0, left: 0 })
  const handleClickInside = () => {
    setShowPicker(false)
  }

  const ref = useElementClick(handleClickInside)

  const openPicker = () => {
    const rect = triggerRef.current.getBoundingClientRect()
    const positionOffset = 120

    setPickerPosition({
      top: rect.bottom - positionOffset,
      left: rect.left - positionOffset,
    })
    setShowPicker(true)
  }

  const handleConfirm = () => {
    setRange(tempRange)
    const startDate = format(tempRange[0]?.startDate, dateFormat)
    const endDate = format(tempRange[0]?.endDate, dateFormat)

    onUpdate([{ startDate, endDate }])
    setShowPicker(false)
  }

  const handleCancel = () => {
    setTempRange(range)
    setShowPicker(false)
  }

  const handleClear = () => {
    setRange([
      {
        startDate: null,
        endDate: null,
        key: 'selection',
      },
    ])

    setShowPicker(false)
    onUpdate([
      {
        startDate: null,
        endDate: null,
        key: 'selection',
      },
    ])
  }

  return (
    <div ref={triggerRef} className="date-range-picker">
      <div className="date-range-placeholder" onClick={openPicker}>
        {range[0]?.startDate && format(range[0]?.startDate, dateFormat)} —{' '}
        {range[0]?.endDate && format(range[0]?.endDate, dateFormat)}
      </div>
      {showPicker && (
        <div
          className="date-range-container"
          style={{
            top: pickerPosition.top,
            left: pickerPosition.left,
          }}
        >
          <div ref={ref} className="date-range-component">
            <ReactDateRangePicker
              editableDateInputs={true}
              onChange={(item) => {
                setTempRange([item.selection])
              }}
              ranges={tempRange}
              minDate={minDate}
              maxDate={maxDate}
              scroll={{ enabled: true }}
            />
            <div className="action-buttons">
              <Button variant="outline-secondary" onClick={handleClear}>
                {t('Clear')}
              </Button>
              <Button variant="outline-secondary" onClick={handleCancel}>
                {t('Cancel')}
              </Button>
              <Button variant="outline-info" onClick={handleConfirm}>
                {t('Confirm')}
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
