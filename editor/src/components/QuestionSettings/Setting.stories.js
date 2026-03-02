import { mockQuestion } from 'sbook/helpers/fixtures/mockQuestion'

import { Setting } from './Setting'
import { getDisplayAttributes } from './attributes/getDisplayAttributes'
import { getFileMetaDataAttributes } from './attributes/getFileMetaDataAttributes'
import { getGeneralAttributes } from './attributes/getGeneralAttributes'
import { getInputAttributes } from './attributes/getInputAttributes'
import { getLocationAttributes } from './attributes/getLocationAttributes'
import { getLogicAttributes } from './attributes/getLogicAttributes'
import { getOtherAttributes } from './attributes/getOtherAttributes'
import { getSliderAttributes } from './attributes/getSliderAttributes'
import { getStatisticsAttributes } from './attributes/getStatisticsAttributes'
import { getThemeAttributes } from './attributes/getThemeAttributes'
import { getTimerAttributes } from './attributes/getTimerAttributes'

export default {
  title: 'QuestionSettings',
  decorators: [(Story) => <Story />],
}

export const General = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="General"
        attributes={Object.values(getGeneralAttributes())}
      />
    </div>
  )
}

export const Display = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Display"
        attributes={Object.values(getDisplayAttributes)}
      />
    </div>
  )
}

export const Logic = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Logic"
        attributes={Object.values(getLogicAttributes())}
      />
    </div>
  )
}

export const Other = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Other"
        attributes={Object.values(getOtherAttributes())}
      />
    </div>
  )
}

export const Input = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Input"
        attributes={Object.values(getInputAttributes())}
      />
    </div>
  )
}

export const Statistics = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Statistics"
        attributes={Object.values(getStatisticsAttributes())}
      />
    </div>
  )
}

export const Timer = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Timer"
        attributes={Object.values(getTimerAttributes())}
      />
    </div>
  )
}

export const Location = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Location"
        attributes={Object.values(getLocationAttributes())}
      />
    </div>
  )
}

export const Slider = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Slider"
        attributes={Object.values(getSliderAttributes())}
      />
    </div>
  )
}

export const FileMetadata = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="File metadata"
        attributes={Object.values(getFileMetaDataAttributes())}
      />
    </div>
  )
}

export const DisplayThemeOptions = () => {
  return (
    <div className="w-25">
      <Setting
        question={mockQuestion()}
        handleUpdate={() => {}}
        simpleSettings={true}
        title="Display theme options"
        attributes={Object.values(getThemeAttributes())}
      />
    </div>
  )
}
