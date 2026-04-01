import { DragDropContext } from 'react-beautiful-dnd'

import { useSurvey } from 'hooks'
import { RowQuestionGroup as RowQuestionGroupComponent } from '../RowQuestionGroup'

export default {
  title: 'General/RowQuestionGroup',
  component: RowQuestionGroupComponent,
  args: { survey: {}, update: () => {} },
}

export const RowQuestionGroup = () => {
  const { survey } = useSurvey()

  return (
    <DragDropContext onDragEnd={() => {}} onDragUpdate={() => {}}>
      <RowQuestionGroupComponent
        questionGroup={survey.questionGroups[0]}
        language={survey.language}
        onTitleClick={() => {}}
      />
    </DragDropContext>
  )
}
