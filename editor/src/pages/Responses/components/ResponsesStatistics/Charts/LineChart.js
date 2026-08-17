import React from 'react'
import {
  Tooltip,
  LineChart as RechartsLineChart,
  CartesianGrid,
  XAxis,
  YAxis,
  Legend,
  Line,
  ResponsiveContainer,
} from 'recharts'
import {
  BAR_MAX_SIZE,
  CustomTooltip,
  TruncatedTick,
  getLabelInterval,
  getMetricDataKey,
  VALUE_TYPE,
} from '../ChartsUtils'

const CustomizedLabel = ({ x, y, stroke, value, isPercentage }) => {
  return (
    <text x={x} y={y} dy={-4} fill={stroke} fontSize={10} textAnchor="middle">
      {isPercentage ? `${Math.round(value)}%` : value}
    </text>
  )
}

export const LineChart = ({
  data,
  isImage = false,
  valueType = VALUE_TYPE.PERCENTAGE,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  return (
    <ResponsiveContainer width="100%" minHeight={500} height="100%">
      <RechartsLineChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis
          dataKey="title"
          height={isImage ? BAR_MAX_SIZE + 24 : undefined}
          interval={getLabelInterval(data.length)}
          tick={(props) => {
            const item = isImage
              ? data.find((row) => row.title === props.payload?.value)
              : null
            return <TruncatedTick {...props} isImage={isImage} item={item} />
          }}
        />
        <YAxis unit={isPercentage ? '%' : undefined} />
        <Tooltip content={<CustomTooltip />} />
        <Legend />
        <Line
          type="monotone"
          dataKey={getMetricDataKey(valueType)}
          stroke="#8884d8"
          activeDot={{ r: 8 }}
          label={<CustomizedLabel isPercentage={isPercentage} />}
        />
      </RechartsLineChart>
    </ResponsiveContainer>
  )
}
