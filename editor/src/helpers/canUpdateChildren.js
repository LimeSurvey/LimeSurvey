import { Entities } from './Buffer'

// Whether children of this type can be added, removed, recoded and reordered: On an active survey only answer options can, subquestion codes are response columns
export const canUpdateChildren = (entityType, isSurveyActive) =>
  !isSurveyActive || entityType === Entities.answer
