# LimeSurvey Editor — Frontend Architecture

> **Source of Truth** for the React editor SPA. This document covers project
> structure, state management, core hooks, the survey update pipeline, and
> styling conventions. It is aimed at developers who are new to this codebase
> but already comfortable with React.

---

## Table of Contents

1. [Project Overview & Tech Stack](#1-project-overview--tech-stack)
2. [Prerequisites & Running the App](#2-prerequisites--running-the-app)
3. [Project Structure](#3-project-structure)
4. [State Management](#4-state-management)
5. [Core Hooks](#5-core-hooks)
6. [Buffer & Survey Update Pipeline](#6-buffer--survey-update-pipeline)
7. [Styling Strategy](#7-styling-strategy)

---

## 1. Project Overview & Tech Stack

The LimeSurvey Editor is a single-page application (SPA) that powers the survey
builder, response viewer, and sharing panel. It is embedded inside the
LimeSurvey PHP backend and communicates with a REST v2 API.

**Key characteristics:**

- **Hash-based routing** (`createHashRouter`) — the PHP backend owns the URL;
  the frontend lives entirely inside `#/...`
- **No server-side rendering** — purely client-side React
- **Demo mode** — controlled by the `REACT_APP_DEMO_MODE` environment variable;
  in demo mode the app reads local JSON fixtures instead of making API calls
- **Storybook** — component stories live in `src/sbook/`; hooks and services
  short-circuit when `process.env.STORYBOOK_DEV === 'true'`

### Key architectural choices

These are the dependencies most likely to surprise a developer coming from a
typical React stack. For the full list see `package.json`.

| Library                     | Why it matters                                                                                                                                                                                                                                                         |
| --------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **TanStack React Query v5** | Used as the **universal state store** — not just for server data. There is no Redux, Zustand, or React Context. Both server state and UI state (focused element, buffer, errors, auth…) live in the React Query cache. See [§4 State Management](#4-state-management). |
| **rsbuild**                 | The build tool. Not Create React App, not Vite. Config lives in `rsbuild.config.js`.                                                                                                                                                                                   |
| **Bootstrap 5 + SCSS**      | The primary styling system. All new UI should be built with Bootstrap utilities and component-level SCSS files under `src/themes/`. MUI Material v5 is present as a dependency but is used for only one legacy component — do not use it for new work.                 |
| **Joi**                     | Schema validation used inside the operations buffer (`OperationsBuffer.addOperation`) to validate every operation before it is queued. It is not a form validation library here.                                                                                       |

---

## 2. Prerequisites & Running the App

### Prerequisites

- **Node.js** LTS (18 or later)
- **npm** (bundled with Node.js)

### Install dependencies

```bash
npm install
```

### Development server

```bash
npm start
```

Starts rsbuild in watch mode with hot reload at `http://localhost:3000`. ESLint
runs automatically in development. The `prestart` script also collects
translation strings before the server boots.

> **Note:** The dev server runs the frontend in isolation. It does **not**
> connect to a live LimeSurvey backend unless you configure the API base URL in
> your environment. For full end-to-end development, use the production build
> (see below).

### Production build

```bash
npm run build
```

Outputs to `build/`. The `postbuild` script (`scripts/postbuild.js`) runs
automatically after the build to copy assets and generate the translation
strings manifest.

### How the build integrates with LimeSurvey CE

LimeSurvey CE loads the editor via the **ReactEditor** plugin, which redirects
the browser to the built SPA. The build output in `build/` is served directly —
**no dev server is involved in production**.

> After making any code changes, you must run `npm run build` for them to take
> effect in the CE environment. The dev server (`npm start`) is for
> frontend-only development only.

### Other useful commands

| Command                   | Purpose                                         |
| ------------------------- | ----------------------------------------------- |
| `npm test`                | Run Jest test suite (headless, demo mode)       |
| `npm run storybook`       | Start Storybook at `http://localhost:6006`      |
| `npm run eslint`          | Lint the `src/` directory                       |
| `npm run prettier:format` | Auto-format `src/` with Prettier                |
| `npm run build-demo`      | Build the standalone demo (no backend required) |

---

## 3. Project Structure

All application source lives under `src/`.

```
src/
├── App.js                  # Root component — provider nesting, version check interval
├── index.js                # ReactDOM.createRoot entry point
├── routes.js               # Route definitions (React Router v6)
├── queryClient.js          # TanStack Query client + localStorage persistence setup
├── config.js               # Static app config (site name, API base URL, timeout)
├── i18nInit.js             # i18next factory (chained LocalStorage + custom backend)
├── appInstrumentation.js   # Error monitoring / APM stub (Sentry-like in production)
│
├── components/             # ~35 UI feature components (see below)
├── hooks/                  # 30+ custom hooks — all business logic lives here
├── services/               # 13 ES6 service classes — all API calls live here
├── providers/              # React providers (PermissionsProvider, I18nextProvider)
├── helpers/                # ~60 utility functions + Buffer, constants, data, options…
├── shared/                 # Pure data helpers for sharing panel configuration
├── plugins/                # Plugin slot system (see below)
├── pages/                  # Top-level page components (Editor, Responses, SharingPanel, Errors)
├── themes/                 # All SCSS files — one file per component + global tokens
├── i18n/                   # Custom i18next backend, scripts
├── sbook/                  # Storybook stories
├── tests/                  # Shared test utilities
└── assets/                 # Static icons and images
```

### `components/`

Feature-oriented folders. Each folder typically contains an `index.js` plus
sub-components specific to that feature. Notable folders:

| Folder               | Description                                            |
| -------------------- | ------------------------------------------------------ |
| `QuestionTypes/`     | Renderers for all question type variants               |
| `SurveyStructure/`   | The left-panel tree of question groups and questions   |
| `ConditionDesigner/` | Visual condition/branching logic builder               |
| `QuestionSettings/`  | Right-panel attribute editor for a focused question    |
| `TopBar/`            | Application top navigation bar                         |
| `SideBar/`           | Collapsible side panel                                 |
| `Modals/`            | All modal dialogs                                      |
| `SurveySettings/`    | Full-screen survey settings panel                      |
| `ThemeOptions/`      | Theme picker and customisation panel                   |
| `SharingPanel/`      | Participant and publication management UI              |
| `PublishSettings/`   | Survey publish / activate workflow                     |
| `UIComponents/`      | Generic reusable UI primitives (buttons, inputs, etc.) |

### `services/`

All HTTP calls go through service classes. No component or hook reaches `axios`
directly — they use a service. See [§5 Core Hooks](#5-core-hooks) for the hooks
that instantiate each service.

| Service                | Responsibility                                                                   |
| ---------------------- | -------------------------------------------------------------------------------- |
| `RestClient`           | Thin `axios.create` wrapper; centralises auth headers and `handleAxiosError`     |
| `AuthService`          | `login`, `refresh`, `logout` via `/auth`                                         |
| `SurveyService`        | `getSurveyDetail`, `patchSurvey`, `getSurveyList`, `getSurveyQuestionsFieldname` |
| `ResponseService`      | Paginated + filtered response fetch, `patchResponses`                            |
| `StatisticsService`    | Full statistics and at-a-glance statistics                                       |
| `FileService`          | Survey image upload                                                              |
| `TranslationsService`  | Fetch translations, fetch all languages, batch-report missing keys (dev only)    |
| `UserService`          | `getUserDetail`, `getUserPermissions`                                            |
| `UserSettingsService`  | Per-user setting get/set                                                         |
| `SiteSettingsService`  | Site-wide settings                                                               |
| `VersionInfoService`   | Version check polling (used in `App.js` every 10 s to detect DB updates)         |
| `SurveyArchiveService` | Archived response sets                                                           |
| `SurveyGroupsService`  | Survey group list                                                                |

### `providers/`

| Provider              | Description                                                                                                                                                                                                                                                              |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `PermissionsProvider` | Fetches user permissions on auth token change. Derives `hasSurveyReadPermission`, `hasSurveyUpdatePermission`, `hasResponsesReadPermission`, `hasResponsesUpdatePermission` and stores them via `useAppState`. Wraps all authenticated routes.                           |
| `I18Provider`         | Wraps `react-i18next`'s `I18nextProvider`. Reads the user's preferred language from `UserService.getUserDetail`, initialises the i18next instance, stores the user detail via `useAppState(STATES.USER_DETAIL)`. Renders a loading spinner until translations are ready. |

### `plugins/`

A lightweight plugin slot system for injecting enterprise/third-party components
without modifying core code.

| File                | Role                                                                                                                                                                                              |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `pluginRegistry.js` | Exports `pluginRegistry = {}` — the map where external code registers plugins                                                                                                                     |
| `PluginManager.js`  | Singleton; reads `pluginRegistry` at construction time and exposes `getPlugin(slotName)`                                                                                                          |
| `PluginSlot.js`     | React component; renders the plugin registered for `slotName`, or a `fallback` prop if none                                                                                                       |
| `slots.js`          | `PLUGIN_SLOTS` enum — named insertion points: `TOP_BAR_RIGHT`, `SHARING_PANEL_EXTRA_MENU`, `SURVEY_SETTINGS_BLOCK_TOKENS_BOTTOM`, `SHARING_OVERVIEW_BOTTOM_LEFT`, `SHARING_OVERVIEW_BOTTOM_RIGHT` |

### App bootstrap

Provider nesting order in `App.js` (outer → inner):

```
AppErrorBoundary
  ThemeProvider (react-bootstrap, breakpoints: lg / xl)
    PersistQueryClientProvider (TanStack Query + localStorage persistence)
      I18Provider (i18next init)
        RouterProvider (hash router)
```

`App.js` also starts a `setInterval` (10 s) on mount that calls
`VersionInfoService.getVersionInfo()`. If `needsDbUpdate === true` the user is
redirected to the PHP admin panel with a native alert.

---

## 4. State Management

### The core decision: React Query as a universal store

This project uses **no Redux, no Zustand, no Recoil, and no React Context** for
shared state. Instead, **@tanstack/react-query v5 is the single store for both
server state and UI/client state**.

This works because React Query's cache is a key-value store that any component
can read and write, and `useQuery` turns every cache entry into a reactive
subscription — components re-render automatically when their cache entries
change.

### Two patterns

**1. Server state** — data that originates from the API

```js
// fetched and cached under a typed key; re-fetched on interval / window focus
const { data: survey } = useQuery({
  queryKey: [STATES.SURVEY],
  queryFn: ({ signal }) => surveyService.getSurveyDetail(id, signal),
  staleTime: Infinity,
  refetchInterval: refetchInterval,
})
```

**2. Client / UI state** — transient app state that never leaves the browser

```js
// useAppState wraps useQuery with staleTime: Infinity and a direct cache setter
const [focusedEntity, setFocusedEntity] = useAppState(
  STATES.FOCUSED_ENTITY,
  null
)
```

`useAppState(key, initValue, config?)` is the standard way to read/write any
piece of global UI state. It stores the value under `['appState', key]` in the
React Query cache.

### `queryClient.js`

| Setting      | Value                                               | Reason                                                                                 |
| ------------ | --------------------------------------------------- | -------------------------------------------------------------------------------------- |
| `staleTime`  | `Infinity`                                          | Data is never considered stale by default; re-fetches are opt-in or triggered manually |
| `cacheTime`  | 30 days                                             | Cache survives long browser sessions                                                   |
| Persistence  | `localStorage` via `createSyncStoragePersister`     | Only queries with `meta.persist: true` are written; the rest stay in memory            |
| Cache buster | `REACT_APP_RELEASE + '-' + commitHash.slice(0, 10)` | Stale localStorage cache is discarded on every new release                             |

### Global state key names (`STATES`)

All cache keys are string constants on the `STATES` object exported from
`src/helpers/constants/constants.js`. Never hard-code a key string — always
reference `STATES.*`.

| Key                                        | Purpose                                                                         |
| ------------------------------------------ | ------------------------------------------------------------------------------- |
| `STATES.SURVEY`                            | Full survey data object                                                         |
| `STATES.SURVEY_HASH`                       | `{ updateHash, refetchHash }` — random numbers incremented to signal re-renders |
| `STATES.BUFFER`                            | `OperationsBuffer` instance — pending unsaved operations                        |
| `STATES.FOCUSED_ENTITY`                    | Which survey element is currently being edited                                  |
| `STATES.AUTH`                              | JWT token, userId, expiry                                                       |
| `STATES.USER_DETAIL`                       | Logged-in user's profile                                                        |
| `STATES.USER_PERMISSIONS`                  | Raw permission objects from the API                                             |
| `STATES.ERRORS`                            | `{ [id]: { [entity]: error } }` — per-entity validation/API errors              |
| `STATES.ERROR_MESSAGES`                    | Flat string array of user-visible error messages                                |
| `STATES.SAVE_STATE` / `STATES.SAVE_STATUS` | Save in-progress / result indicator                                             |
| `STATES.IS_PATCH_SURVEY_RUNNING`           | Prevents concurrent patch requests                                              |
| `STATES.ACTIVE_LANGUAGE`                   | Currently selected editor language                                              |
| `STATES.OPERATION_FINISH_SUBSCRIPTIONS`    | Pub/sub registry for post-operation callbacks                                   |
| `STATES.SURVEY_REFRESH_REQUIRED`           | Flag: a refetch was blocked by the buffer and should run once it clears         |

---

## 5. Core Hooks

Hooks are the **business-logic layer** between services (HTTP) and the UI.
Components call hooks; hooks call services. Components never call services
directly.

### `useAppState(key, initValue?, config?)`

Generic React Query-backed state slot. The primary building block for all global
UI state.

|                |                                                                                                                                     |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**    | Read and write any app-level state value backed by the React Query cache                                                            |
| **Parameters** | `key` — string constant from `STATES`; `initValue` — default value written on first call; `config` — optional React Query overrides |
| **Returns**    | `[value, setValue]`                                                                                                                 |
| **Notes**      | Uses `staleTime: Infinity`. To persist across reloads add `meta: { persist: true }` via `config`.                                   |

---

### `useAuth()`

|                  |                                                                                                                                                                                                        |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Purpose**      | Manages JWT authentication state. Initialises auth from the `LS_AUTH_INIT` cookie on first load. Automatically refreshes the token via `AuthService` every 60 s if the token is older than 30 minutes. |
| **Parameters**   | None                                                                                                                                                                                                   |
| **Returns**      | `{ isLoggedIn, logout, restHeaders, userId, token }`                                                                                                                                                   |
| **Dependencies** | `useAuthService`, `react-cookie`, `queryClient`, `dayJsHelper`                                                                                                                                         |

---

### `useBuffer()`

|                |                                                                                                                                  |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**    | Exposes the `OperationsBuffer` instance and mutation helpers. All components that write survey changes go through `addToBuffer`. |
| **Parameters** | None                                                                                                                             |
| **Returns**    | `{ operationsBuffer, addToBuffer, clearBuffer, setBuffer }`                                                                      |

**Return values in detail:**

| Return                                            | Description                                                                                                                                                                                             |
| ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `operationsBuffer`                                | The current `OperationsBuffer` class instance (from `STATES.BUFFER` cache entry)                                                                                                                        |
| `addToBuffer(operation, updateCurrentOperation?)` | Validates the operation (joi), merges with any existing `(id, op, entity)` operation, writes the updated buffer to the cache. No-ops in Storybook mode. Also clears the matching error via `useErrors`. |
| `clearBuffer({ ready? })`                         | Clears all operations, or only "ready" / "not-ready" operations                                                                                                                                         |
| `setBuffer(data, hash)`                           | Directly replaces the buffer in the cache (used during restore/rehydration)                                                                                                                             |

---

### `useErrors()`

|                |                                                                                                                                                                                                          |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**    | Manages two error stores: a per-entity map (`STATES.ERRORS`) and a flat user-visible message list (`STATES.ERROR_MESSAGES`). Parses `SurveyService.patchSurvey` responses to extract field-level errors. |
| **Parameters** | None                                                                                                                                                                                                     |
| **Returns**    | `{ errors, errorMessages, setErrors, setErrorMessages, setErrorsFromPatchResponse, getError, removeError, clearErrors, clearErrorMessages }`                                                             |

---

### `useFocused(focused, groupIndex, questionIndex)`

|                  |                                                                                                                                                                                           |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**      | Controls which survey element (group, question, answer) is currently being edited. Syncs focus state to URL search params so the correct element is restored on page reload or deep-link. |
| **Parameters**   | `focused` — current focused entity object; `groupIndex`, `questionIndex` — position context                                                                                               |
| **Returns**      | `{ data, setFocused, unFocus, … }`                                                                                                                                                        |
| **Dependencies** | `queryClient`, `useNavigate`, `useLocation`, `lodash.cloneDeep`                                                                                                                           |

---

### `useOperationCallback()`

|                |                                                                                                                                                                                               |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**    | Pub/sub mechanism for running code after a specific buffer operation completes. Useful for triggering UI updates (e.g. refocusing a newly created question) after the patch response arrives. |
| **Parameters** | None                                                                                                                                                                                          |
| **Returns**    | `{ subscribeToOperationFinish(entity, operation, callback, once?), triggerCallbacks(operations, results) }`                                                                                   |
| **Notes**      | Subscriptions are one-time (`once: true`) by default. Persistent subscriptions must pass `once: false`.                                                                                       |

---

### `useQueryRetry({ normalFetchInterval?, idleFetchInterval?, idleTimeOut?, refetchStopTime?, detectUserIdleInterval? })`

|                  |                                                                                                                                                                                         |
| ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**      | Adapts the survey re-fetch interval based on user activity. Reduces network traffic when the browser tab is idle. Shows a SweetAlert2 error dialog after 10 consecutive failed retries. |
| **Parameters**   | All optional. Defaults: `normalFetchInterval` 60 s, `idleFetchInterval` 5 min                                                                                                           |
| **Returns**      | `{ refetchInterval, handleRetry }` — pass both to the `useQuery` config in `useSurvey`                                                                                                  |
| **Dependencies** | `react-idle-timer`, `SwalAlert`                                                                                                                                                         |

---

### `useQuestionChildren({ question, handleUpdate, surveySettings, language })`

|                  |                                                                                                                                                                                                                                          |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**      | Manages the sub-questions and answers (children) of a question. Handles create, delete, reorder, sort-order management, answer code generation, and dual-scale (scale 1 / scale 2) separation. Every mutation writes a buffer operation. |
| **Parameters**   | `question` — the parent question object; `handleUpdate` — callback to propagate question changes; `surveySettings`, `language`                                                                                                           |
| **Returns**      | `{ children, addChild, deleteChild, updateChild, moveChild, addBothScaleChildren, … }`                                                                                                                                                   |
| **Dependencies** | `useBuffer`, `queryClient`, `Entities`, `getAnswerExample`, `getNextAnswerCode`                                                                                                                                                          |

---

### `useConditionDesigner({ survey, question, focused, scid, scenarioToPatch, groupIndex, questionIndex, conditions, addToBuffer, setScid, setConditions, update, setPendingScenarioName, onNavigateBack })`

|                |                                                                                                                                                                                                                                                                        |
| -------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**    | Encapsulates all business logic for the Condition Designer: adding, removing, and updating conditions within a scenario; assembling API payloads; writing buffer operations for condition create/update/delete; and syncing the parent question with scenario changes. |
| **Parameters** | Survey and question context objects, current editing state, and callbacks provided by the ConditionDesigner UI component                                                                                                                                               |
| **Returns**    | `{ addCondition, removeCondition, updateCondition, handleSave, handleDeletedConditions, handleQuestionUpdate }`                                                                                                                                                        |

---

### `useSurvey(id)`

The central hook of the application. Every other hook that needs survey data
reads it from the cache that `useSurvey` populates.

|                |                                                                                                                                                                            |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**    | Fetches and manages the full survey structure. Coordinates the patch pipeline, blocks stale refetches while the buffer is non-empty, and signals the UI when data changes. |
| **Parameters** | `id` — survey ID (from URL params)                                                                                                                                         |
| **Returns**    | `{ survey, surveyList, update, language, surveyPatch, clearSurvey, fetchSurvey, surveyMenus, surveyHash, questionsFieldNamesMap, refetchQuestionsFieldNamesMap }`          |

**Key internals:**

| Detail                 | Description                                                                                                                                                                                                                |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `surveyPatch`          | `lodash.debounce(patchSurvey, 1000)` — batches rapid successive mutations into a single PATCH request                                                                                                                      |
| Refetch guard          | If `operationsBuffer.length > 0` or `IS_PATCH_SURVEY_RUNNING` is true when a refetch fires, the query is cancelled and `STATES.SURVEY_REFRESH_REQUIRED` is set to `true`; the refetch runs once the buffer clears          |
| `surveyHash`           | `{ updateHash, refetchHash }` — two random numbers stored in the cache. Components that need to react to survey changes use these as `useMemo` / `useEffect` dependencies instead of deep-comparing the full survey object |
| Timestamp optimisation | Works with `useSurveyRequestTimestamp` to send `GET /survey-detail/:id/ts/:timestamp`; the server returns a "not modified" sentinel instead of the full payload when nothing has changed                                   |

---

### Utility Hooks (summary)

| Hook                                                   | Purpose                                                                                     |
| ------------------------------------------------------ | ------------------------------------------------------------------------------------------- |
| `useAuthService()`                                     | Instantiates and returns `AuthService`                                                      |
| `useSurveyService()`                                   | Instantiates and returns `SurveyService` scoped to the current survey URL + auth token      |
| `useFileService()`                                     | Instantiates and returns `FileService`                                                      |
| `useTranslationsService()`                             | Instantiates and returns `TranslationsService`                                              |
| `useUserService()`                                     | Instantiates and returns `UserService`                                                      |
| `useSurveyGroupsService()`                             | Instantiates and returns `SurveyGroupsService`                                              |
| `useRestClient()`                                      | Instantiates and returns a `RestClient` (axios wrapper) scoped to survey URL + auth headers |
| `useResponses(surveyId, pagination, filters, sorting)` | Fetches paginated responses; exposes `patchMutation`                                        |
| `useStatistics(surveyId, filters)`                     | Fetches full survey statistics                                                              |
| `useStatisticsAtGlance(surveyId)`                      | Fetches summary-level statistics                                                            |
| `useSurveyArchive(sid)`                                | Fetches and filters survey archive sets                                                     |
| `useSurveyUpdatePermission(survey)`                    | Syncs `survey.hasSurveyUpdatePermission` into `useAppState`                                 |
| `useSurveyRequestTimestamp()`                          | Stores/retrieves the UTC timestamp of the last successful survey fetch per `surveyId`       |
| `useSetAllLanguages()`                                 | Fetches and de-duplicates language objects for all survey languages                         |
| `useGlobalStates(operationsBuffer, surveyHash)`        | Snapshot of all React Query cache entries as a flat object                                  |
| `useDebounce(fn, wait)`                                | `requestAnimationFrame`-loop based debounce                                                 |
| `useDebouncedCallback(callback, delay?)`               | `setTimeout`-based debounce (default 500 ms)                                                |
| `useClickOutside(ref, handler)`                        | Fires `handler` when `mousedown`/`touchstart` originates outside `ref`                      |
| `useElementClick(callback, isOutSide?)`                | Document-level click listener; triggers for click inside or outside a returned `ref`        |
| `useIsInViewport(externalRef?)`                        | `IntersectionObserver` — returns `[ref, isInView]`                                          |
| `useFeedbackForm()`                                    | Controls in-app feedback popup display logic (timing, domain checks)                        |
| `useCookieFeedbackStore()`                             | Reads/writes per-feedback-type state to browser cookies                                     |

---

## 6. Buffer & Survey Update Pipeline

Survey edits are never sent to the server immediately. Instead they flow through
an **operations buffer** — an in-memory queue that accumulates changes,
deduplicates them, and flushes them as a single PATCH request.

### How it works

```
User edits a field
       │
       ▼
component calls addToBuffer(operation)
       │
       ▼
OperationsBuffer.addOperation(operation)
  • validates with a joi schema
  • if an operation with the same (id, op, entity) exists → merge props with lodash.merge
  • else → append to operations[]
  • generates new bufferHash (signals re-renders)
       │
       ▼
Updated OperationsBuffer written to React Query cache (STATES.BUFFER)
       │
       ▼
caller passes buffer.getOperations({ ready: true }) to surveyPatch()
       │
       ▼
surveyPatch  ═══════════════════════════════
  lodash.debounce(1000 ms)
       │
       ▼
SurveyService.patchSurvey(operations)  →  PATCH /survey/:id
       │
       ▼
response parsed by useErrors.setErrorsFromPatchResponse()
useOperationCallback.triggerCallbacks(operations, results)
clearBuffer({ ready: true })
```

### Refetch guard

While any operations remain in the buffer, `useSurvey` **cancels incoming
re-fetches** (interval or window-focus) to prevent the server's stale state from
overwriting unsaved local changes. A deferred re-fetch is scheduled by setting
`STATES.SURVEY_REFRESH_REQUIRED = true`; it executes as soon as the buffer is
fully flushed.

### `OperationsBuffer` class

`src/helpers/Buffer/OperationsBuffer.js`

| Member                                                       | Description                                                                                      |
| ------------------------------------------------------------ | ------------------------------------------------------------------------------------------------ |
| `operations[]`                                               | Array of pending operation objects                                                               |
| `bufferHash`                                                 | Random string; changes on every `addOperation` call so React Query subscribers re-render         |
| `addOperation(operation, updateCurrentOperation?, restore?)` | Main entry point — validates, merges, dispatches to entity handler                               |
| `getOperations({ ready? })`                                  | Returns all operations, or only those that are "ready" (no temp IDs blocking them) / "not ready" |

Entity-specific merge logic lives in `src/helpers/Buffer/operationsHandlers/`
(one file per entity: `survey`, `questionGroup`, `question`, `answer`,
`subquestion`, `questionCondition`).

Operation shape validation schemas are in
`src/helpers/Buffer/operationsScheme/`.

### Debounce utilities

Three debounce mechanisms are used deliberately for different scenarios:

| Utility                                | Mechanism                         | Used for                                                                |
| -------------------------------------- | --------------------------------- | ----------------------------------------------------------------------- |
| `lodash.debounce` (inside `useSurvey`) | `setTimeout`, leading-edge cancel | Batching `surveyPatch` PATCH calls — 1000 ms delay                      |
| `useDebouncedCallback`                 | `setTimeout` — default 500 ms     | General UI input debouncing                                             |
| `useDebounce`                          | `requestAnimationFrame` loop      | Animation-frame-accurate debouncing for performance-critical UI updates |

---

## 7. Styling Strategy

### Technology

| Decision              | Choice                                                               |
| --------------------- | -------------------------------------------------------------------- |
| CSS methodology       | **SCSS only** — no CSS Modules, no styled-components, no Tailwind    |
| Component library CSS | Bootstrap 5 (SCSS source, not compiled) — **primary styling system** |
| Icons                 | Remixicon (class-based) + FontAwesome                                |
| Font                  | IBM Plex Sans (loaded via Google Fonts `@import` in `index.scss`)    |

### File layout

All styles live in `src/themes/`.

```
src/themes/
├── index.scss          # Master entry point — imports Bootstrap, all component SCSS files,
│                       #  and third-party CSS (Leaflet, react-date-range, FA, etc.)
├── variables.scss      # Design token overrides — colours, typography, spacing
├── customizations.scss # Bootstrap and MUI global customisations
├── utils.scss          # Shared SCSS utility mixins and helpers
└── <component>/        # One folder per component, e.g. accordion/, topbar/, modals/…
    └── <component>.scss
```

### Design tokens (`variables.scss`)

Bootstrap's Sass variable system is extended with a custom design language:

**Colour palette:**

| Token                                 | Semantic role                                                        |
| ------------------------------------- | -------------------------------------------------------------------- |
| `$grape`, `$aubergine`                | Primary brand / dark accents                                         |
| `$zucchini`, `$blueberry`, `$litschi` | Secondary accents                                                    |
| `$orange`, `$banana`, `$apple`        | Status / attention colours                                           |
| `$g-100` … `$g-900`                   | Grey scale                                                           |
| `$theme-colors` map                   | Merged into Bootstrap's `$theme-colors` for utility class generation |

**Typography:**

- Base font: IBM Plex Sans
- Root font size: 14 px
- All `rem` values scale from this base

**Shape:** `$border-radius: 4px`

### Global stylesheet load order

Inside `App.js`, stylesheets are imported in this order to control specificity
correctly:

1. `bootstrap/dist/css/bootstrap.min.css` — Bootstrap compiled utilities (for
   rapid prototyping)
2. `themes/index.scss` — the SCSS master file (imports Bootstrap SCSS source for
   variable access, then all component SCSS)
3. `remixicon/fonts/remixicon.css` — icon font

Inside `themes/index.scss`, third-party CSS is imported in this order:

- Google Fonts (IBM Plex Sans)
- `react-date-range/dist/styles.css` + theme
- `leaflet/dist/leaflet.css`
- FontAwesome (via `@fortawesome/fontawesome-free`)
- `react-loading-skeleton/dist/skeleton.css`

### MUI coexistence

MUI v5 is a dependency but is used for only **one component**. It is not the
standard — Bootstrap 5 + SCSS is. Do not use MUI when building new components.
