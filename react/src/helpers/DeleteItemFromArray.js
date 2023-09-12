export const DeleteItemFromArray = (array, index) => {
  const updatedArray = [...array]
  updatedArray.splice(index, 1)

  return updatedArray
}
