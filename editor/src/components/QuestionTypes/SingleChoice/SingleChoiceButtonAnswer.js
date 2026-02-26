import ContentEditable from 'react-contenteditable'

import { Button } from 'components/UIComponents'

export const SingleChoiceButtonAnswer = ({ value }) => {
  return (
    <Button variant="outline-success" className={'me-2 button-question'}>
      <ContentEditable disabled={true} html={value} />
    </Button>
  )
}
