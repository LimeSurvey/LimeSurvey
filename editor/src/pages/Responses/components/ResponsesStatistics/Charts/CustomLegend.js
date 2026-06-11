export const CustomLegend = ({ payload, isImage = false }) => {
  return (
    <div className="overflow-auto d-flex justify-content-center gap-2 mt-4">
      {payload.map((entry, index) => (
        <div className="d-flex align-items-center gap-1" key={index}>
          <div
            className="recharts-square"
            style={{ background: entry.color }}
          ></div>
          <div title={entry.value} className="recharts-square-value">
            {isImage ? (
              <img
                src={entry.value}
                alt={entry.value}
                className="recharts-legend-image"
              />
            ) : (
              entry.value
            )}
          </div>
        </div>
      ))}
    </div>
  )
}
