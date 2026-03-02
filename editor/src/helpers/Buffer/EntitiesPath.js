import { EntitiesType } from './EntitiesType'

export const EntitiesPath = {
  survey: `${EntitiesType.survey}.$property`,
  languageSetting: `${EntitiesType.survey}.languageSettings.$language`,
  question: `${EntitiesType.question}.$attributeName`,
  questionAttribute: `${EntitiesType.question}.attributes.$attributeName`,
  questionGroupL10n: `${EntitiesType.groupName}.l10ns.$language`,
  questionL10n: `${EntitiesType.question}.l10ns.$language`,
  answer: `${EntitiesType.question}.answers`,
  subquestion: `${EntitiesType.question}.subquestions`,
  questionGroup: 'questionGroup', // todo: update it to use a variable.
  questionGroupReorder: 'questionGroupReorder', // todo: update it to use a variable.
  questionCondition: 'questionCondition',
  accessMode: 'accessMode',
}
