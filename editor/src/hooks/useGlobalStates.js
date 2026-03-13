import { useMemo } from 'react'
import { queryClient } from 'queryClient'

/**
 * Custom hook to transform query client data into a global states object
 * @param {Object} operationsBuffer - The operations buffer object
 * @param {Object} surveyHash - The survey hash object with updateHash and refetchHash
 * @returns {Object} - The transformed global states object
 */
export const useGlobalStates = (operationsBuffer, surveyHash) => {
  return useMemo(() => {
    const statesArray = queryClient.getQueriesData().map((state) => {
      const key = state[0]
      const value = state[1]

      // incase of an appState the key is stored in the second index.
      return {
        [key[1] ? key[1] : key[0]]: value,
      }
    })

    // the reason why we want the states as an object is for easier access.
    // example: statesObject[STATES.SURVEY] is easier than statesArray.find((state) => state.key === STATES.SURVEY)
    const statesObject = Object.assign({}, ...statesArray)

    return statesObject
  }, [
    queryClient,
    operationsBuffer.bufferHash,
    surveyHash.updateHash,
    surveyHash.refetchHash,
  ])
}
