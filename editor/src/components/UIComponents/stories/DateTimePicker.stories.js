import { DateTimePickerComponent } from '../DateTimePicker/DateTimePicker'

export default {
  title: 'imageWrapper/DateTimePicker',
  component: DateTimePickerComponent,
}

export const DateTimePicker = (args) => {
  return <DateTimePickerComponent {...args} />
}
