import { shouldRenderImage } from '../ChartsUtils'

export const CustomLegend = ({ payload, isImage = false }) => {
  return (
    <div className="responses-statistics-legend">
      {payload.map((entry, index) => (
        <div className="responses-statistics-legend-item" key={index}>
          <div
            className="recharts-square"
            style={{ background: entry.color }}
          ></div>
          <div
            title={entry.value}
            className="responses-statistics-legend-value"
          >
            {shouldRenderImage(isImage, entry.payload) ? (
              <img
                src={entry.value}
                alt={entry.value}
                className="responses-statistics-legend-image"
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
