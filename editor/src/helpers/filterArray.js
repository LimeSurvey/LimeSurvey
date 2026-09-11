import { cloneDeep } from 'lodash'

export const filterArray = (_array, conditionFunction) => {
  const array = cloneDeep(_array)

  const filterResult = array.reduce(
    ({ validArray, filteredArray }, item) => {
      if (conditionFunction(item)) {
        validArray.push(item)
      } else {
        filteredArray.push(item)
      }
      return { validArray, filteredArray }
    },
    { validArray: [], filteredArray: [] } // Initialize with two empty arrays inside an object
  )

  return {
    validArray: filterResult.validArray,
    filteredArray: filterResult.filteredArray,
  }
}
