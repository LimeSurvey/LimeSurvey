# TEMP PLAN: Plugin Capability Discovery + Generic Invoke for RemoteControl

## Session Goal
Design and implement a safe RemoteControl extension model so external agents (via MCP/API) can:
1. Discover plugin-exposed API capabilities.
2. Execute only explicitly allowlisted plugin actions via a generic RPC call.

This must be done with strong security defaults (deny-by-default).

## Why This Is Needed
Current RemoteControl API is mostly fixed to methods in `remotecontrol_handle`.
Plugins can participate only where core already dispatches plugin events (example: export types), but cannot cleanly expose arbitrary plugin functionality for API-only/MCP agents.

## Proposed API Surface (Core)
Add two new RemoteControl methods:

1. `list_plugin_api(sessionKey, pluginName = null)`
- Returns capability manifests for callable plugin actions.
- Discovery output should be filtered by caller permissions.

2. `call_plugin_api(sessionKey, plugin, action, payload, context = {})`
- Executes one declared plugin action.
- Returns structured result or structured error.

## Plugin Event Contract (Core -> Plugins)
Add two plugin events:

1. `listPluginApiActions`
- Plugin returns declared actions and metadata only.
- Metadata draft:
  - `plugin`
  - `action`
  - `description`
  - `version`
  - `inputSchema` (JSON Schema-like)
  - `outputSchema`
  - `requiredPermissions`
  - `rateLimitKey` (optional)

2. `callPluginApiAction`
- Core dispatches to target plugin/action only.
- Plugin validates payload + permissions and returns result.

## Security Requirements (Non-Negotiable)
1. Explicit action registration only (no reflection-based method execution).
2. Deny-by-default for undeclared plugin/action.
3. Per-action permission checks in plugin handler.
4. Strict payload validation (schema; reject unknown fields where possible).
5. Discovery must not leak actions caller cannot execute.
6. Add payload size and timeout guardrails.
7. Audit log every call: actor, plugin/action, outcome, duration.
8. No filesystem/shell/raw SQL passthrough from generic endpoint.

## Initial Scope (MVP)
1. Core discovery and generic invoke methods.
2. Event contracts + one internal sample plugin implementation.
3. Basic schema validation path (at least required fields/type checks).
4. Permission-filtered discovery.
5. Error model with stable codes (`invalid_session`, `no_permission`, `unknown_action`, `invalid_payload`, `plugin_error`).

## Out of Scope (for first pass)
1. Async job orchestration.
2. Streaming responses.
3. Cross-plugin transaction semantics.
4. Auto-introspection of existing plugin routes/methods.

## Likely Files to Touch
1. `application/helpers/remotecontrol/remotecontrol_handle.php`
- add `list_plugin_api`, `call_plugin_api` methods.

2. `application/controllers/admin/RemoteControl.php`
- likely no behavioral change needed, but verify method publication/docs output.

3. Plugin event consumers/producers (new event names)
- plugin manager dispatch points from `remotecontrol_handle`.

4. Potential docs/tests under:
- `tests/unit/helpers/remotecontrol/*`
- RemoteControl API documentation location used in project.

## Suggested Execution Order (Next Session)
1. Add method stubs + session checks in `remotecontrol_handle`.
2. Implement `list_plugin_api` event dispatch + permission-filtered result normalization.
3. Implement `call_plugin_api` routing and strict target matching.
4. Add validation/error mapping.
5. Add unit tests for:
   - invalid session
   - unknown plugin/action
   - permission denied
   - valid discovery
   - valid invocation
6. Add one example plugin action in a controlled plugin for testability.

## Key Risks
1. Turning generic invoke into an implicit RCE surface.
2. Data leakage via discovery endpoint.
3. Inconsistent plugin payload validation.
4. Backward compatibility issues if error responses are inconsistent.

## Risk Mitigations
1. Strict allowlist manifest.
2. Central response normalization in core.
3. Mandatory plugin-side permission gate template.
4. Tests for forbidden and malformed calls first.

## Open Design Questions
1. Should `pluginName` filter in discovery be exact match only?
2. Should `call_plugin_api` include optional `surveyId` in context for central pre-checks?
3. Should schema validation be minimal custom validator or full JSON Schema implementation?
4. Where to persist audit logs (existing LS logging/event infra vs dedicated table)?

## Branch + Base
- Working branch: `feature/plugin-api-discovery-invoke`
- Based on: `upstream/develop-major`
- Fork remote branch: `origin/feature/plugin-api-discovery-invoke`

## Quick Start Commands (Next Session)
```bash
git checkout feature/plugin-api-discovery-invoke
git fetch upstream develop-major
git rebase upstream/develop-major
```
