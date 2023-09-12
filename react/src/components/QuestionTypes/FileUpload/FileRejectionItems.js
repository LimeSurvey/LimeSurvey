import React from 'react'

export const FileRejectionItems = ({ fileRejections }) => (
  <ul>
    {fileRejections.map(({ file, errors }) => {
      return (
        <li key={file.path}>
          {file.path} - {file.size} bytes
          <ul>
            {errors.map((e) => (
              <li className="text-danger" key={e.code}>
                {e.message}
              </li>
            ))}
          </ul>
        </li>
      )
    })}
  </ul>
)
