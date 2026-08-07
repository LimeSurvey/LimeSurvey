import { useState } from 'react'
import { AlignButtons as AlignButtonsComponent } from '../../Buttons/AlignButtons'

export default {
  title: 'UIComponents/Button/AlignButtons',
  component: AlignButtonsComponent,
}

export const AlignButtons = () => {
  const [value, setValue] = useState('right')

  return (
    <>
      <p className="d-none" data-testid="output">
        {value}
      </p>
      <AlignButtonsComponent
        labelText="Aligned Buttons"
        value={value}
        update={(value) => setValue(value)}
      />
    </>
  )
}
