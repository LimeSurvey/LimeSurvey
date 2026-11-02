import Joi from 'joi'

import { Operations, Entities } from 'helpers'
import {
  questionAttributeUpdateJoi,
  questionCreateJoi,
  questionDeleteJoi,
  questionL10nUpdateJoi,
  questionUpdateJoi,
} from './operationsScheme/question'
import { questionGroupUpdateJoi } from './operationsScheme/questionGroup/questionGroupUpdateJoi'
import { answerUpdateJoi } from './operationsScheme/answer/answerUpdateJoi'
import { subquestionUpdateJoi } from './operationsScheme/subQuestion/subquestionUpdateJoi'
import { questionGroupL10nUpdateJoi } from './operationsScheme/questionGroup/questionGroupL10nUpdateJoi'
import { surveyUpdateJoi } from './operationsScheme/survey/surveyUpdateJoi'
import { surveyStatusUpdateJoi } from './operationsScheme/survey/surveyStatusUpdateJoi'
import { questionGroupCreateJoi } from './operationsScheme/questionGroup/questionGroupCreateJoi'
import { answerCreateJoi } from './operationsScheme/answer/answerCreateJoi'
import { subquestionCreateJoi } from './operationsScheme/subQuestion/subquestionCreateJoi'
import { questionGroupDeleteJoi } from './operationsScheme/questionGroup/questionGroupDeleteJoi'
import { answerDeleteJoi } from './operationsScheme/answer/answerDeleteJoi'
import { subquestionDeleteJoi } from './operationsScheme/subQuestion/subquestionDeleteJoi'
import { languageSettingUpdateJoi } from './operationsScheme/survey/languageSettingUpdateJoi'
import { surveyImportResponsesJoi } from './operationsScheme/survey/surveyImportResponsesJoi'
import {
  questionConditionCreateJoi,
  questionConditionUpdateJoi,
  questionConditionDeleteJoi,
} from './operationsScheme/questionCondition'
import { themeSettingUpdateJoi } from './operationsScheme/survey/themeSettingUpdateJoi'
import { accessModeUpdateJoi } from './operationsScheme/survey/accessModeUpdate.Joi'

export const getOperationScheme = (operation, entity) => {
  const schemeMap = {
    [Operations.update]: {
      [Entities.question]: questionUpdateJoi,
      [Entities.questionGroup]: questionGroupUpdateJoi,
      [Entities.questionAttribute]: questionAttributeUpdateJoi,
      [Entities.answer]: answerUpdateJoi,
      [Entities.subquestion]: subquestionUpdateJoi,
      [Entities.questionL10n]: questionL10nUpdateJoi,
      [Entities.questionGroupL10n]: questionGroupL10nUpdateJoi,
      [Entities.survey]: surveyUpdateJoi,
      [Entities.surveyStatus]: surveyStatusUpdateJoi,
      [Entities.languageSetting]: languageSettingUpdateJoi,
      [Entities.importResponses]: surveyImportResponsesJoi,
      [Entities.questionCondition]: questionConditionUpdateJoi,
      [Entities.themeSettings]: themeSettingUpdateJoi,
      [Entities.accessMode]: accessModeUpdateJoi,
    },
    [Operations.create]: {
      [Entities.question]: questionCreateJoi,
      [Entities.questionGroup]: questionGroupCreateJoi,
      [Entities.answer]: answerCreateJoi,
      [Entities.subquestion]: subquestionCreateJoi,
      [Entities.questionCondition]: questionConditionCreateJoi,
    },
    [Operations.delete]: {
      [Entities.question]: questionDeleteJoi,
      [Entities.questionGroup]: questionGroupDeleteJoi,
      [Entities.answer]: answerDeleteJoi,
      [Entities.subquestion]: subquestionDeleteJoi,
      [Entities.questionCondition]: questionConditionDeleteJoi,
    },
  }

  const possibleOperationSchemes = schemeMap[operation]
  if (!possibleOperationSchemes) {
    throw new Error(`Unsupported operation: ${operation}`)
  }

  const scheme = possibleOperationSchemes?.[entity]
  return scheme?.keys({ error: Joi.any().optional() })
}
