export const RandomNumber = (min = 0, max = 1000000) => {
  min = Math.ceil(min)
  max = Math.ceil(max)

  return Math.floor(Math.random() * (max - min + 1)) + min
}
