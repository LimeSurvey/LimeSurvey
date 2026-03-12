// Function to emulate pausing between interactions
export function sleep(ms = 100, scale = 1) {
  return new Promise((resolve) => setTimeout(resolve, ms * scale))
}
