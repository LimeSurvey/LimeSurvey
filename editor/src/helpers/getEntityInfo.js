import { EntitiesType } from './Buffer'

export const getEntityInfo = (id, survey, entityType) => {
  for (let i = 0; i < survey.questionGroups.length; i++) {
    const group = survey.questionGroups[i]
    if (group.gid === id && entityType === EntitiesType.group) {
      return { [entityType]: group, groupIndex: i }
    }

    if (entityType !== EntitiesType.group) {
      for (let j = 0; j < group.questions.length; j++) {
        const question = group.questions[j]
        if (question.qid === id && entityType === EntitiesType.question) {
          return { [entityType]: question, groupIndex: i, questionIndex: j }
        }

        if (entityType === EntitiesType.answer) {
          for (let k = 0; k < question.answers.length; k++) {
            const answer = question.answers[k]
            if (answer.aid === id) {
              return {
                [entityType]: answer,
                groupIndex: i,
                questionIndex: j,
                answerIndex: k,
              }
            }
          }
        }

        if (entityType === EntitiesType.subquestion) {
          for (let k = 0; k < question.subquestions?.length; k++) {
            const subquestion = question.subquestions[k]
            if (subquestion.qid === id) {
              return {
                [entityType]: subquestion,
                groupIndex: i,
                questionIndex: j,
                subquestionIndex: k,
              }
            }
          }
        }

        if (entityType === EntitiesType.condition) {
          for (let k = 0; k < question.scenarios?.length; k++) {
            for (let l = 0; l < question.scenarios[k].conditions?.length; l++) {
              const condition = question.scenarios[k].conditions[l]
              if (condition.cid === id) {
                return {
                  [entityType]: condition,
                  groupIndex: i,
                  questionIndex: j,
                  scenarioIndex: k,
                  conditionIndex: l,
                }
              }
            }
          }
        }
      }
    }
  }

  return {}
}
