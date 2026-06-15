import { shouldRenderImage } from '../ChartsUtils'

export const CustomLegend = ({ payload, isImage = false }) => {
  return (
    <div className="recharts-custom-legend">
      {payload.map((entry, index) => (
        <div className="recharts-custom-legend-item" key={index}>
          <div
            className="recharts-square"
            style={{ background: entry.color }}
          ></div>
          <div title={entry.value} className="recharts-square-value">
            {shouldRenderImage(isImage, entry.payload) ? (
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
