export async function equationTests(step, canvas) {
  await step(`Should have answer the question`, async () => {
    canvas.getByTestId('equation')
  })
}
