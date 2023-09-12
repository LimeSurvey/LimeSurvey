export const FileExceedItems = ({ minFiles, maxFiles, exceededMaxFiles }) => (
  <ul>
    {exceededMaxFiles ? (
      <li className="text-danger">
        You should not upload more than {maxFiles} files.
      </li>
    ) : (
      <li className="text-danger">
        You should upload more than {minFiles} files.
      </li>
    )}
  </ul>
)
