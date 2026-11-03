import { Entities } from './Entities'
import { Operations } from './Operations'

export const createBufferOperation = (id) => {
  const entities = {
    survey: () => entity(id, Entities.survey),
    question: () => entity(id, Entities.question),
    questionAttribute: () => entity(id, Entities.questionAttribute),
    answer: () => entity(id, Entities.answer),
    languageSetting: () => entity(id, Entities.languageSetting),
    questionGroup: () => entity(id, Entities.questionGroup),
    questionGroupL10n: () => entity(id, Entities.questionGroupL10n),
    questionGroupReorder: () => entity(id, Entities.questionGroupReorder),
    questionL10n: () => entity(id, Entities.questionL10n),
    subquestion: () => entity(id, Entities.subquestion),
    surveyStatus: () => entity(id, Entities.surveyStatus),
    questionCondition: () => entity(id, Entities.questionCondition),
    importResponses: () => entity(id, Entities.importResponses),
    themeSettings: () => entity(id, Entities.themeSettings),
    response: () => entity(id, Entities.response),
    responseFile: () => entity(id, Entities.responseFile),
    accessMode: () => entity(id, Entities.accessMode),
  }

  return entities
}

export const entity = (id, entity, props = {}) => {
  return {
    create: (props) => operation(id, entity, Operations.create, props),
    update: (props) => operation(id, entity, Operations.update, props),
    delete: () => operation(id, entity, Operations.delete, props),
  }
}

export const operation = (id, entity, op, props = {}) => {
  const newOperation = {
    id,
    entity,
    op,
    props,
    error: false,
  }

  if (op === Operations.delete) {
    delete newOperation.props
  }

  return newOperation
}
