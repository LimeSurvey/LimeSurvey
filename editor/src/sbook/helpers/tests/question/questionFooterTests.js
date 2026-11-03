export async function questionFooterTests(step, canvas) {
  await step(`Should find question footer with 2 button spans`, async () => {
    canvas.getByTestId(`question-footer-copy-icon`)
    canvas.getByTestId(`question-footer-delete-icon`)
  })
}
