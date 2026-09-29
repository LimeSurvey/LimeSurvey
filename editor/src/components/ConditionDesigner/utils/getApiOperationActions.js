export const getApiOperationActions = () => {
  return {
    CONDITION: {
      CREATE: 'insertCondition',
      UPDATE: 'updateCondition',
      DELETE: 'deleteCondition',
    },
    CONDITION_SCRIPT: {
      UPDATE: 'conditionScript',
    },
    SCENARIO: {
      DELETE: 'deleteScenario',
    },
  }
}
