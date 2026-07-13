import React from 'react'

import { Select } from 'components'

// Select preset for the filter modal. Uses fixed menu positioning so the
// options menu escapes the scrollable modal and flips near the viewport edge —
// options are never clipped, even for rows at the bottom of the modal.
export const FilterSelect = (props) => <Select menuPosition="fixed" {...props} />
