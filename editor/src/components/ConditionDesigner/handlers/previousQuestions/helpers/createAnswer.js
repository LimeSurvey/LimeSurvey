export const createAnswer = (cAnswers, value, label, fieldname) => {
  cAnswers.push({
    cfieldname: fieldname,
    value,
    label,
  })
}
