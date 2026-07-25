# ADR-0001: FormRequests reject unknown fields application-wide

## Status

Accepted

## Context

Laravel 13.4 added **FormRequest strict mode**: when enabled, any input key in a request body that is
not declared in the FormRequest's `rules()` is rejected with a `prohibited` validation error. The
flag defaults to `false`.

Without it, a client can slip extra keys (`is_admin`, `role`, `team_id`, …) into an otherwise
legitimate request. Nothing in the validator complains, and the keys travel onward to whatever reads
them — `$request->all()`, a `fill()`, an Action argument. Guarding each of those call sites
individually is a per-developer discipline that fails silently when someone forgets.

The framework decides "unknown" by comparing `Arr::dot()` of the request body against
`array_keys(rules())`. A key is allowed when it exactly matches a rule key, when it ends in
`_confirmation` and the base key is a rule, or when it matches a wildcard rule (`items.*.id`) by
regex. Only the body is inspected — query-string parameters (signed URLs, `?page=`) are not
governed.

## Decision

Enable strict mode **globally and unconditionally** — production included — from
`AppServiceProvider::boot()`:

```php
FormRequest::failOnUnknownFields();
```

An undeclared field is treated as a request the application refuses to serve, not as a
development-time warning.

## Alternatives considered

**Per-class `#[FailOnUnknownFields]`.** Rejected. The attribute is read off the concrete request
class and is *not* inherited from a base class, so a shared `BaseFormRequest` cannot carry it — every
new request has to remember the attribute, and the one that forgets is the one that ships the hole.
Global mode covers requests that do not exist yet.

**Dev-only gating (`if (! $this->app->isProduction())`).** Rejected. The attack this prevents happens
in production; a control that switches off there is not a control. It would also let a strict-mode
violation reach production undetected whenever a code path lacks test coverage.

## Consequences

- Every field a form submits must be declared in `rules()`. Fields the controller reads but does not
  validate now need a rule — `CreateSessionRequest` gained `'remember' => ['sometimes', 'boolean']`
  for exactly this reason.
- New forms fail loudly and immediately rather than silently accepting extra input, which is the
  point, but it does mean a forgotten rule surfaces as a validation error rather than a no-op.
- The flag is global, so it also governs **vendor** FormRequests — notably Fortify's. Today only
  `twoFactorAuthentication` and `passkeys` are enabled; the sole enabled Fortify FormRequest that is
  actually exercised is `TwoFactorLoginRequest`, which declares both `code` and `recovery_code`, and
  passkey flows use the base `Request` rather than a FormRequest. This is a risk we own: a future
  Fortify upgrade could add or rename a field in a flow we do not control. If that happens, rebind
  the offending request in the container to an app subclass carrying `#[FailOnUnknownFields(false)]`
  rather than disabling the flag globally.
- `_token` / `_method` are not exempt in the framework. This is safe here because the app is
  Inertia/XHR: CSRF travels in the `X-XSRF-TOKEN` header and real HTTP verbs are used, so neither
  key appears in a request body.
