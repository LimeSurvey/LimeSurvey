import dayjs from 'dayjs'
import { Form } from 'react-bootstrap'

import { DateTimePickerComponent } from 'components/UIComponents'

const startOf2000 = dayjs('2000-01-01T00:00:00.000')
const endOf2049 = dayjs('2049-12-31T23:59:59.999')

export const PublicationAccessSettings = ({ survey, handleUpdate }) => {
  return (
    <div className="mt-5 p-4 bg-white">
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">Start date time</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <DateTimePickerComponent
            value={survey.startDateTime}
            labelText=""
            minDate={startOf2000}
            maxDate={survey.endDateTime}
            handleOnChange={(value) => {
              handleUpdate({
                startDateTime: dayjs(value),
              })
            }}
          />
        </div>
      </div>

      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">End date time</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <DateTimePickerComponent
            value={survey.endDateTime}
            minDate={survey.startDateTime}
            maxDate={endOf2049}
            labelText=""
            handleOnChange={(value) => {
              handleUpdate({
                endDateTime: value,
              })
            }}
          />
        </div>
      </div>
    </div>
  )
}
