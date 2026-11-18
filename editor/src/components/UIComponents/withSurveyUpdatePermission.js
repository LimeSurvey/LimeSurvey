import { useContext } from 'react'
import { QueryClientContext } from '@tanstack/react-query'

// Direct imports: The hooks and helpers barrels reach the components barrel.
import { useAppState } from 'hooks/useAppState'
import { STATES } from 'helpers/constants/constants'

/**
 * Wraps a field so it receives the survey update permission from the app state
 * as its hasSurveyUpdatePermission prop. A hasSurveyUpdatePermission prop passed
 * by the caller wins over the app state. The field decides itself whether the
 * permission locks it (usually through noPermissionDisabled).
 *
 * The app state needs a QueryClientProvider. Outside one, the field renders
 * with its own default. The choice depends only on the provider, which never
 * changes for a mounted field, so the field is never remounted.
 *
 * @param {Function} Field The field component to wrap.
 * @param {string} name The name of the wrapped component. Some callers
 *   identify components by their name, so each wrapper needs its own.
 * @returns {Function} The wrapped component.
 */
export const withSurveyUpdatePermission = (Field, name) => {
  const FieldWithPermission = (props) => {
    const [hasSurveyUpdatePermission] = useAppState(
      STATES.HAS_SURVEY_UPDATE_PERMISSION
    )

    return (
      <Field
        {...props}
        hasSurveyUpdatePermission={
          props.hasSurveyUpdatePermission ?? hasSurveyUpdatePermission
        }
      />
    )
  }

  const WrappedField = (props) => {
    const hasQueryClient = !!useContext(QueryClientContext)

    return hasQueryClient ? (
      <FieldWithPermission {...props} />
    ) : (
      <Field {...props} />
    )
  }

  Object.defineProperty(WrappedField, 'name', { value: name })
  WrappedField.displayName = name

  return WrappedField
}
