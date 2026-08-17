import React from 'react'
import {
  Tooltip,
  RadarChart as RechartsRadarChart,
  PolarGrid,
  PolarAngleAxis,
  Radar,
  ResponsiveContainer,
} from 'recharts'
import {
  COLORS,
  CustomTooltip,
  TruncatedTick,
  getMetricDataKey,
  VALUE_TYPE,
} from '../ChartsUtils'

const RADAR_IMAGE_SIZE = 72

export const RadarChart = ({
  data,
  isImage = false,
  valueType = VALUE_TYPE.PERCENTAGE,
}) => {
  return (
    <ResponsiveContainer width="100%" minHeight={400} height="100%">
      <RechartsRadarChart
        data={data}
        // Leave room outside the plot for the image ticks.
        outerRadius={isImage ? '62%' : '80%'}
      >
        <PolarGrid />
        <PolarAngleAxis
          dataKey="title"
          tick={(props) => {
            const item = isImage
              ? data.find((row) => row.title === props.payload?.value)
              : null
            return (
              <TruncatedTick
                {...props}
                dy={4}
                isImage={isImage}
                item={item}
                imageWidth={RADAR_IMAGE_SIZE}
              />
            )
          }}
        />
        <Radar
          dataKey={getMetricDataKey(valueType)}
          stroke={COLORS[1]}
          fill={COLORS[1]}
          fillOpacity={0.6}
        />
        <Tooltip content={<CustomTooltip />} />
      </RechartsRadarChart>
    </ResponsiveContainer>
  )
}
