/**
 * Path of the LimeSurvey installation the editor runs in, without trailing slash ('' for a root install).
 *
 * The editor page is served by EditorController under the administration URL prefix
 * (/index.php/admin/editor, or /admin/editor with URL rewriting). The static entry point
 * /editor/ is still recognised, the development server uses it.
 */
export const getBasePath = () => {
  const path = window.location.pathname
  const adminMatch = path.match(
    /^(.*?)(?:\/index\.php)?\/admin\/editor(?:\/.*)?$/
  )
  if (adminMatch) {
    return adminMatch[1]
  }
  return path.split('/editor')[0]
}

export default getBasePath
