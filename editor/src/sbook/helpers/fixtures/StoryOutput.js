export const StoryOutput = ({ output }) => {
  return (
    <div className="mt-3">
      <h3>
        <b>Value</b>: <span data-testid="output">{output}</span>
      </h3>
    </div>
  )
}
