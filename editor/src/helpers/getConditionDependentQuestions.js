/**
 * Returns the questions whose conditions use the given question as source (cqid).
 * The question's own conditions are ignored.
 */
export const getConditionDependentQuestions = (survey, qid) => {
  if (!survey?.questionGroups || qid === undefined || qid === null) return []

  const sourceQid = String(qid)

  return survey.questionGroups
    .flatMap((group) => group?.questions || [])
    .filter(
      (question) =>
        question &&
        String(question.qid) !== sourceQid &&
        (question.scenarios || []).some((scenario) =>
          (scenario.conditions || []).some(
            (condition) => String(condition.cqid) === sourceQid
          )
        )
    )
}
