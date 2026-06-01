import { InputRange } from '../InputRange/InputRange'
import { Direction } from 'react-range'

export default {
  title: 'UIComponents/InputRange',
  component: InputRange,
}

export function Basic() {
  return <InputRange direction={Direction.Down} />
}

export function Horizontal() {
  return <InputRange labelText={'Input Range'} direction={Direction.Right} />
}
