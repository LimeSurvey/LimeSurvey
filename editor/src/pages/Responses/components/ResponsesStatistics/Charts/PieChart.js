import React, { useLayoutEffect, useRef, useState } from 'react'
import {
  Cell,
  PieChart as RechartsPieChart,
  Pie,
  ResponsiveContainer,
  Tooltip,
} from 'recharts'

import {
  COLORS,
  CustomTooltip,
  VALUE_TYPE,
  getDisplayMetric,
  shouldRenderImage,
} from '../ChartsUtils'

// Label image frame: a fixed white box with a border, the image inset by a
// uniform padding so every label keeps the same footprint regardless of the
// image's aspect ratio (an SVG <image> can't take CSS border/padding).
const LABEL_IMAGE_WIDTH = 56
const LABEL_IMAGE_HEIGHT = 40
const LABEL_IMAGE_PADDING = 6
const LABEL_IMAGE_BORDER = '#d3d5da' // $g-400

// Answer-name row: capped width with CSS ellipsis (rendered via foreignObject
// since an SVG <text> can't truncate). Height fits the $font-size-xl line.
const LABEL_MAX_WIDTH = 160
const LABEL_NAME_HEIGHT = 32

const RADIAN = Math.PI / 180

// Answer-name label. Exposes the full text as a native hover tooltip only when
// it is actually truncated (overflows the capped width), so hovering a
// shortened label reveals the full text.
const LabelName = ({ label, justify }) => {
  const ref = useRef(null)
  const [isTruncated, setIsTruncated] = useState(false)

  useLayoutEffect(() => {
    const el = ref.current
    if (el) setIsTruncated(el.scrollWidth > el.clientWidth)
  }, [label])

  return (
    <div
      className="responses-statistics-pie-label-name-wrap"
      style={{ justifyContent: justify }}
    >
      <div
        ref={ref}
        className="responses-statistics-pie-label-name"
        title={isTruncated ? label : undefined}
      >
        {label}
      </div>
    </div>
  )
}

const renderActiveShapeNew = ({
  cx,
  cy,
  midAngle,
  outerRadius,
  fill,
  percent,
  payload,
  name,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
  yOffset = 0,
}) => {
  const cos = Math.cos(-RADIAN * (midAngle ?? 1))
  const sin = Math.sin(-RADIAN * (midAngle ?? 1))
  const isRight = cos >= 0
  const showImage = shouldRenderImage(isImage, payload)

  const r = outerRadius ?? 0
  // Image labels are a taller stack (code + image + metric), so push them
  // further out radially; otherwise the top (code) row of a bottom slice's
  // label reaches back into the pie.
  const elbow = r + 20 + (showImage ? 18 : 0)
  const sx = (cx ?? 0) + r * cos
  const sy = (cy ?? 0) + r * sin
  const mx = (cx ?? 0) + elbow * cos
  const my = (cy ?? 0) + elbow * sin + yOffset
  const ex = mx + (isRight ? 26 : -26)
  const ey = my

  // Data rows carry the answer code in `key`; synthetic rows (other, comment,
  // NoAnswer) have no real code, so their labels get no code row
  const key = payload?.key
  const isOther = payload?.isOther ?? key === 'other'
  const id =
    payload?.id ?? (['other', 'comment', 'NoAnswer'].includes(key) ? null : key)
  // Middle row is the answer label; `name` is recharts' nameKey value, with
  // `payload.title` as the reliable fallback.
  const label = payload?.title ?? name ?? ''
  const displayMetric = getDisplayMetric(payload, valueType, percent)

  // Block width is estimated from the widest row so its near edge stays clear
  // of the connector dot.
  const estimatedWidth = Math.max(
    `${id ?? ''}`.length * 7,
    showImage ? LABEL_IMAGE_WIDTH : LABEL_MAX_WIDTH,
    displayMetric.length * 7
  )
  const centerX = ex + (isRight ? 1 : -1) * (10 + estimatedWidth / 2)

  // Text rows anchor to the block edge nearest the dot and flow outward, so
  // short labels hug the dot on both sides (instead of pinning to the far edge
  // and leaving a gap on the left). Image labels stay centered on the block.
  const labelAnchor = showImage ? 'middle' : isRight ? 'start' : 'end'
  const labelX = showImage
    ? centerX
    : centerX + (isRight ? -1 : 1) * (LABEL_MAX_WIDTH / 2)

  // Image label is sourced from the row title (the image URL); recharts does
  // not reliably pass `name` to the label renderer.
  const imageUrl = payload?.title ?? name
  const imageFrameX = centerX - LABEL_IMAGE_WIDTH / 2
  const imageFrameY = (id ? ey - 2 : ey - 8) - LABEL_IMAGE_HEIGHT / 2

  return (
    <g>
      <path
        d={`M${sx},${sy} L${mx},${my} L${ex},${ey}`}
        stroke={fill}
        strokeWidth={2.5}
        fill="none"
        strokeDasharray={isOther ? '3 3' : undefined}
      />
      <circle cx={ex} cy={ey} r={5} fill={fill} />

      {id && (
        <text
          x={labelX}
          y={showImage ? imageFrameY - 6 : ey - 18}
          textAnchor={labelAnchor}
          className="responses-statistics-pie-label-id"
        >
          <tspan>{id}</tspan>
          {isOther && <tspan fontStyle="italic"> - Other</tspan>}
        </text>
      )}

      {showImage ? (
        <g>
          <rect
            x={imageFrameX}
            y={imageFrameY}
            width={LABEL_IMAGE_WIDTH}
            height={LABEL_IMAGE_HEIGHT}
            rx={4}
            fill="#FFFFFF"
            stroke={LABEL_IMAGE_BORDER}
            strokeWidth={1}
          />
          <image
            href={imageUrl}
            xlinkHref={imageUrl}
            x={imageFrameX + LABEL_IMAGE_PADDING}
            y={imageFrameY + LABEL_IMAGE_PADDING}
            width={LABEL_IMAGE_WIDTH - LABEL_IMAGE_PADDING * 2}
            height={LABEL_IMAGE_HEIGHT - LABEL_IMAGE_PADDING * 2}
            preserveAspectRatio="xMidYMid meet"
          >
            <title>{imageUrl}</title>
          </image>
        </g>
      ) : (
        <foreignObject
          x={centerX - LABEL_MAX_WIDTH / 2}
          y={(id ? ey + 6 : ey) - 24}
          width={LABEL_MAX_WIDTH}
          height={LABEL_NAME_HEIGHT}
        >
          <LabelName
            label={label}
            justify={isRight ? 'flex-start' : 'flex-end'}
          />
        </foreignObject>
      )}

      <text
        x={labelX}
        y={(showImage ? ey + 6 : ey) + (id ? 24 : 18)}
        textAnchor={labelAnchor}
        className="responses-statistics-pie-label-metric"
      >
        {displayMetric}
      </text>
    </g>
  )
}

// Vertical label-block footprint (id + value/image + metric rows)
const LABEL_MIN_GAP = 58

// Zero (or tiny) slices share the same midAngle, so their labels land on the
// same point. Recompute every slice's label anchor with the same angle math
// recharts uses and push down any label that would overlap the one above it
// on the same side of the pie.
const computeLabelYOffsets = (data, cy, outerRadius) => {
  const total = data.reduce((sum, entry) => sum + (entry.value || 0), 0) || 1
  let startAngle = 0
  const anchors = data.map((entry, index) => {
    const span = ((entry.value || 0) / total) * 360
    const midAngle = startAngle + span / 2
    startAngle += span
    return {
      index,
      isRight: Math.cos(-RADIAN * midAngle) >= 0,
      ey: cy + (outerRadius + 20) * Math.sin(-RADIAN * midAngle),
    }
  })

  const offsets = new Array(data.length).fill(0)
  ;[true, false].forEach((side) => {
    anchors
      .filter((anchor) => anchor.isRight === side)
      .sort((a, b) => a.ey - b.ey)
      .reduce((minY, anchor) => {
        const y = Math.max(anchor.ey, minY)
        offsets[anchor.index] = y - anchor.ey
        return y + LABEL_MIN_GAP
      }, -Infinity)
  })
  return offsets
}

export const PieChart = ({
  data,
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
}) => {
  const renderLabel = (props) => {
    const offsets = computeLabelYOffsets(data, props.cy, props.outerRadius)
    return renderActiveShapeNew({
      ...props,
      valueType,
      isImage,
      yOffset: offsets[props.index] ?? 0,
    })
  }

  return (
    <div className="responses-statistics-pie-chart">
      <ResponsiveContainer width="100%" height={400}>
        <RechartsPieChart
          margin={{ top: 30, right: 160, bottom: 40, left: 160 }}
        >
          <Pie
            data={data}
            cx="50%"
            cy="50%"
            dataKey="value"
            nameKey="title"
            label={renderLabel}
            labelLine={false}
            outerRadius="80%"
            animationBegin={0}
            animationDuration={600}
            fill="#8884d8"
          >
            {data.map((_, index) => (
              <Cell
                key={`peie-cell-${index}`}
                fill={COLORS[index % COLORS.length]}
              />
            ))}
          </Pie>
          <Tooltip cursor={{ fill: '#eeeff7' }} content={CustomTooltip} />
        </RechartsPieChart>
      </ResponsiveContainer>
    </div>
  )
}
