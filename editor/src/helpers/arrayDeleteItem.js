import { cloneDeep } from 'lodash'

export const arrayDeleteItem = (array, index) => {
  const clonedArray = cloneDeep(array)
  const deletedItem = clonedArray.splice(index, 1)

  return [clonedArray, deletedItem]
}
