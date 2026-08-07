export function mockEmptyQuestion(questionTypeInfo) {
  return {
    gid: Math.floor(Math.random() * 10),
    qid: Math.floor(Math.random() * 10),
    sid: Math.floor(Math.random() * 10),
    questionThemeName: questionTypeInfo.theme,
    type: questionTypeInfo.type,
    attributes: {},
  }
}
