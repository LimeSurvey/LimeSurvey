import { CONDITION_ACTIONS } from 'helpers/Buffer/operationsHandlers'

export const getApiOperationActions = () => {
  return {
    CONDITION: {
      CREATE: CONDITION_ACTIONS.CREATE,
      UPDATE: CONDITION_ACTIONS.UPDATE,
      DELETE: CONDITION_ACTIONS.DELETE,
    },
    CONDITION_SCRIPT: {
      UPDATE: CONDITION_ACTIONS.SCRIPT,
    },
    SCENARIO: {
      DELETE: CONDITION_ACTIONS.DELETE_SCENARIO,
    },
  }
}
