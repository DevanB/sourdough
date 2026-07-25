# Context

Glossary of the vocabulary this project uses. Terms here are the canonical spelling — prefer them
over synonyms in code, tests, issues, and commit messages.

## Glossary

**Unknown field** — a request input key that is not declared in the handling FormRequest's `rules()`.
Unknown fields are rejected application-wide with a `prohibited` validation error. A key ending in
`_confirmation` whose base key is a rule (`password_confirmation` alongside `password`) is not
unknown. Only the request body is considered; query-string parameters are not.

**Strict form request** — the application-wide posture, adopted in [ADR-0001](docs/adr/0001-formrequest-strict-mode.md),
that every FormRequest rejects unknown fields. It is always on, production included, and applies to
vendor FormRequests as well as the app's own.
