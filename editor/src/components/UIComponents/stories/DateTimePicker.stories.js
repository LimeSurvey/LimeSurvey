import { DateTimePickerComponent } from '../DateTimePicker/DateTimePicker'

export default {
  title: 'UIComponents/DateTimePicker',
  component: DateTimePickerComponent,
}

export const DateTimePicker = (args) => {
  return <DateTimePickerComponent {...args} />
}
