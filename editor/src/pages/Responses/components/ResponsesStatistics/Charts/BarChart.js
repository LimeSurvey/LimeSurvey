import React from 'react'
import classNames from 'classnames'
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Cell,
  LabelList,
  ReferenceLine,
  ResponsiveContainer,
} from 'recharts'

import {
  BAR_MAX_SIZE,
  COLORS,
  CustomTooltip,
  TruncatedTick,
  formatMetricValue,
  getBarChartMinWidth,
  getLabelInterval,
  getMetricDataKey,
  VALUE_TYPE,
} from '../ChartsUtils'
import { ORIENTATION } from '../ChartOrientationMenu'

const HORIZONTAL_ROW_HEIGHT = 50
const HORIZONTAL_IMAGE_SIZE = 64
const HORIZONTAL_IMAGE_ROW_HEIGHT = HORIZONTAL_IMAGE_SIZE + 16
const HORIZONTAL_AXIS_HEIGHT = 30
const HORIZONTAL_LABEL_WIDTH = 150
const HORIZONTAL_BAR_MAX_SIZE = 32

export const BarChart = ({
  data,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
  hasComments = false,
  onViewComments,
  scrollable = true,
  orientation = ORIENTATION.VERTICAL,
}) => {
  const isHorizontal = orientation === ORIENTATION.HORIZONTAL
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE
  const dataKey = getMetricDataKey(valueType)
  const chartMinWidth =
    scrollable && !isHorizontal ? getBarChartMinWidth(data.length) : undefined
  const formatBarValue = (value) =>
    value == null || Number(value) === 0
      ? ''
      : formatMetricValue(value, valueType)
  const rowHeight = isImage
    ? HORIZONTAL_IMAGE_ROW_HEIGHT
    : HORIZONTAL_ROW_HEIGHT
  const chartHeight = isHorizontal
    ? Math.max(data.length, 1) * rowHeight + HORIZONTAL_AXIS_HEIGHT
    : 400

  const findImageItem = (props) =>
    isImage ? data.find((row) => row.title === props.payload?.value) : null

  const metricAxisProps = {
    unit: isPercentage ? '%' : undefined,
  }

  return (
    <div
      className={classNames('responses-statistics-bar-chart-viewport', {
        'responses-statistics-bar-chart-viewport--scrollable': chartMinWidth,
      })}
      tabIndex={chartMinWidth ? 0 : undefined}
      aria-label={chartMinWidth ? t('Scrollable chart') : undefined}
    >
      <div
        className={classNames('responses-statistics-bar-chart', {
          'responses-statistics-bar-chart--clickable': hasComments,
        })}
        style={chartMinWidth ? { minWidth: chartMinWidth } : undefined}
      >
        <ResponsiveContainer width="100%" height={chartHeight}>
          <RechartsBarChart
            data={data}
            layout={isHorizontal ? 'vertical' : 'horizontal'}
            margin={
              isHorizontal
                ? { top: 0, right: 48, left: 0, bottom: 0 }
                : { top: 20, right: 20 }
            }
          >
            {isHorizontal ? (
              <>
                <CartesianGrid strokeDasharray="3 3" horizontal={false} />
                <XAxis
                  type="number"
                  orientation="top"
                  height={HORIZONTAL_AXIS_HEIGHT}
                  {...metricAxisProps}
                />
                <YAxis
                  type="category"
                  dataKey="title"
                  width={
                    isImage
                      ? HORIZONTAL_IMAGE_SIZE + 16
                      : HORIZONTAL_LABEL_WIDTH
                  }
                  interval={0}
                  tickLine={false}
                  tick={(props) => (
                    <TruncatedTick
                      {...props}
                      textAnchor="end"
                      dy={4}
                      isImage={isImage}
                      item={findImageItem(props)}
                      imageWidth={HORIZONTAL_IMAGE_SIZE}
                      imageOffset={{
                        x: -HORIZONTAL_IMAGE_SIZE,
                        y: -HORIZONTAL_IMAGE_SIZE / 2,
                      }}
                    />
                  )}
                />
                <ReferenceLine x={0} stroke="#6e748c" />
              </>
            ) : (
              <>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis
                  dataKey="title"
                  height={isImage ? BAR_MAX_SIZE + 24 : 40}
                  interval={getLabelInterval(data.length)}
                  tick={(props) => (
                    <TruncatedTick
                      {...props}
                      isImage={isImage}
                      item={findImageItem(props)}
                      imageWidth={BAR_MAX_SIZE}
                    />
                  )}
                />
                <YAxis {...metricAxisProps} />
              </>
            )}
            <Tooltip
              cursor={{ fill: '#eeeff7' }}
              content={<CustomTooltip showCommentsHint={hasComments} />}
            />
            <Bar
              maxBarSize={isHorizontal ? HORIZONTAL_BAR_MAX_SIZE : BAR_MAX_SIZE}
              dataKey={dataKey}
              nameKey="title"
              data={data}
              onClick={
                hasComments
                  ? (entry) => onViewComments?.(entry?.key)
                  : undefined
              }
            >
              {data.map((_, index) => {
                return (
                  <Cell
                    key={`bar-chart-${index}`}
                    fill={COLORS[index % COLORS.length]}
                  />
                )
              })}
              <LabelList
                dataKey={dataKey}
                position={isHorizontal ? 'right' : 'top'}
                offset={8}
                formatter={formatBarValue}
                className="responses-statistics-chart-labels"
              />
            </Bar>
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>
    </div>
  )
}
