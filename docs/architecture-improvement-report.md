# Architecture Improvement Report

An exploration of this codebase through the "deep module" lens (John Ousterhout, *A Philosophy of Software Design*): a deep module has a small interface hiding a large implementation. Deep modules are more testable and easier to navigate — you can test at the boundary instead of inside.

Three parallel surveys were run: the Laravel backend (`app/`, `routes/`, `config/`), the Inertia/React frontend seam (`resources/js/`), and the Pest test suite (`tests/`). The dominant backend pattern is a codebase decomposed into many single-method classes ("one class per verb") — clean in isolation, but it produces *shallow modules*: the interface (constructor + `handle()` signature + FormRequest + route line + policy method) is often as large as the implementation, and understanding one team operation requires opening 5–7 files.

Below are six ranked candidates for module-deepening, followed by test-suite findings and a recommendation.

---

## Candidate 1 — Team authorization: one truth table, four representations

**Cluster** (~11 files):

- `app/Enums/TeamRole.php` — source of truth: `permissions()` match
- `app/Data/TeamPermissions.php` — re-encodes the same 7 permissions as booleans
- `app/Policies/TeamPolicy.php` — 7 methods, each a thin wrapper over `hasTeamPermission`
- `app/Models/User.php` — `teamRole`, `hasTeamPermission`, `ownsTeam`, `toTeamPermissions`
- 7 FormRequests (`UpdateTeamRequest`, `DeleteTeamRequest`, `CreateTeamInvitationRequest`, `CancelTeamInvitationRequest`, `UpdateTeamMemberRequest`, `RemoveTeamMemberRequest`, `SwitchTeamRequest`) each calling `Gate::allows()`

**Why coupled:** One conceptual fact — "what can role X do" — is expressed in three parallel representations kept in lockstep: the enum's permission-string list, the DTO's seven `can*` booleans, and the policy's method names. The `'team:update'` string literal is duplicated between the policy and the DTO. Adding a single permission means editing the enum, the DTO constructor, the DTO `toArray()`, `TeamPermissions::for()`, and a policy method.

```php
// TeamPolicy.php — every method is this shape
public function update(User $user, Team $team): bool { return $user->hasTeamPermission($team, 'team:update'); }
// TeamPermissions.php — restates the same strings
canUpdateTeam: $role?->hasPermission('team:update') ?? false,
```

**Dependency category:** Shared secret — parallel representations of one truth table that must be edited together.

**Test impact:** Four parallel test files (`tests/Unit/Enums/TeamRoleTest.php`, `tests/Unit/Data/TeamPermissionsTest.php`, `tests/Unit/Policies/TeamPolicyTest.php`, parts of `tests/Unit/Models/UserTest.php`) assert the same truth table. The literal permission-string assertions in `TeamRoleTest` are change-detector tests: they pin the implementation array rather than a behavior. A single capability-object boundary test per (role × capability) replaces all of them.

**Deepening idea:** Let the policy and the DTO both derive from `TeamRole` directly (policy delegates to `$role->hasPermission()`, DTO built by iterating a canonical permission list), collapsing three representations to one queryable authorization surface for a (user, team) pair.

---

## Candidate 2 — The "current team" invariant has no owner

**Cluster:**

- `app/Models/User.php` — `current_team_id`, `currentTeam`, `isCurrentTeam`, `fallbackTeam`, `personalTeam`, `setRelation` juggling
- `app/Actions/CreateTeam.php`, `SwitchTeam.php`, `DeleteTeam.php`, `RemoveTeamMember.php`, `AcceptTeamInvitation.php`

**Why coupled:** The rule "a user's `current_team_id` must always point to a team they still belong to" is not owned by any single module — each action re-derives it. `CreateTeam`, `SwitchTeam`, and `AcceptTeamInvitation` all repeat the pair `$user->update(['current_team_id' => ...]); $user->setRelation('currentTeam', $team);`. The two removal paths implement *different* fallback policies:

```php
// DeleteTeam.php: fallback = fallbackTeam(excluding), manually unset relation if null
$fallback = $actor->fallbackTeam(excluding: $team);
$actor->update(['current_team_id' => $fallback?->id]);
// RemoveTeamMember.php: different rule — only personalTeam(), no ordered fallback
$member->update(['current_team_id' => $personalTeam?->id]);
```

**Dependency category:** Temporal/invariant coupling — every membership mutation must remember to repair the invariant.

**Test impact:** This is a real gap: nothing asserts that a removed member's `current_team_id` is repointed when the removed team was their current team (potential dangling pointer). One owned primitive gets one boundary test replacing per-action re-verification.

**Deepening idea:** A single method (e.g. `User::switchToTeam()` / `User::reassignCurrentTeamAfterLeaving()`) that owns both the DB write and the `setRelation` cache-sync, called by all five actions, with one fallback policy.

---

## Candidate 3 — Post-login redirect resolution, re-wired at three finish lines

**Cluster:**

- `app/Actions/ResolvePostLoginDestination.php`
- `app/Http/Controllers/SessionController.php` (password login)
- `app/Http/Responses/TwoFactorLoginResponse.php`
- `app/Http/Responses/PasskeyLoginResponse.php`

**Why coupled:** Three distinct auth finish-lines (password, 2FA, passkey) must converge on the same redirect. The action centralizes the *decision*, but the *fallback wiring* is copy-pasted between the two response classes:

```php
// identical in both response classes
$user = $request->user();
$fallback = $user !== null
    ? $this->resolver->handle($user)
    : route('dashboard', absolute: false);
```

`ResolvePostLoginDestination` already returns `route('dashboard')` in its else-branches, so the null-user guard duplicates the action's own default. The two response classes differ only in JSON envelope (204 vs `{redirect}` 200) yet re-implement the whole method.

**Dependency category:** Pass-through — the action is too shallow, so each caller re-adds the missing half (`redirect()->intended()` + null handling).

**Test impact:** The null-user fallback branch is never exercised, and `TwoFactorLoginResponse` has no tests at all (only the passkey response does). Deepening the resolver to own intended-URL + null handling makes one boundary test cover all three entry points, replacing near-duplicate tests in `ResolvePostLoginDestinationTest`, `PasskeyLoginResponseTest`, and `SessionControllerTest`.

**Deepening idea:** Have `ResolvePostLoginDestination` accept a nullable user (or the request) and own the null case; factor the shared response-building so the two responses shrink to just their JSON-vs-redirect difference.

---

## Candidate 4 — The Inertia prop contract is a hand-maintained twin

**Cluster:**

- Server: `app/Http/Middleware/HandleInertiaRequests.php` (`share()`), `app/Http/Controllers/TeamController.php` (inline `members`/`invitations` arrays), `app/Data/UserTeam.php`, `app/Data/TeamPermissions.php`
- Client: `resources/js/types/global.d.ts` (`sharedPageProps`), `resources/js/types/teams.ts` (`TeamMemberItem`, `PendingInvitation`), `resources/js/types/auth.ts` (`User`)

**Why coupled:** The shape returned by `share()` (`name`, `auth`, `features.teams`, `currentTeam`, `teams`, `sidebarOpen`) is mirrored by hand in `global.d.ts` with a `[key: string]: unknown` escape hatch — rename a prop server-side and nothing fails at build time. Wayfinder types the *route/action* seam but not the *prop* seam. Serialization is half-DTO, half-hand-rolled: `UserTeam`/`TeamPermissions` DTOs exist but are immediately `->toArray()`'d at three call sites (`TeamController::index`, `TeamSelectionController::show`, `HandleInertiaRequests::share`), while `TeamController::edit` builds members/invitations as inline associative arrays duplicating field-selection logic the DTOs were meant to own — stringly-typed on both ends.

**Dependency category:** Cross-boundary data coupling — an untyped seam between two type systems.

**Test impact:** The teams-*enabled* shared-prop payload is currently untested (the lazy `currentTeam`/`teams` closures are never evaluated with a logged-in multi-team user; only the disabled path is covered by `TeamsFeatureDisabledTest`). A `TeamContext::for($user)` provider plus `Arrayable` DTOs gives one testable boundary and lets `assertInertia` tests against real routes replace the hand-constructed middleware tests in `HandleInertiaRequestsTest`.

**Deepening idea:** Add `TeamMemberItem`/`PendingInvitation` DTOs matching the existing pattern, make all DTOs `Arrayable`/`JsonSerializable` so Inertia serializes them directly, and extract a `TeamContext` provider that both the middleware and controllers consume — one source of truth from which the TS types are derivable.

---

## Candidate 5 — Feature flags: two classes named `Features`, three encodings of one flag

**Cluster:**

- `app/Support/Features.php` (app flag: `teams()` reading `config('features.teams')`) vs `Laravel\Fortify\Features` (2FA/passkeys) — same class name, different systems
- `config/features.php`
- `app/Http/Middleware/EnsureFeatureEnabled.php` (hardcoded `match`)
- `app/Http/Middleware/HandleInertiaRequests.php`, `app/Actions/ResolvePostLoginDestination.php` (consumers)
- Client: `resources/js/components/app-sidebar.tsx`, `resources/js/layouts/settings/layout.tsx` — each re-reads `usePage().props.features.teams` and branches ad hoc

**Why coupled:** The middleware re-encodes the flag name a third time:

```php
// EnsureFeatureEnabled.php — the abstraction is one hardcoded case
abort_unless(match ($feature) {
    'teams' => Features::teams(),
    default => false,
}, 404);
```

Adding a second flag touches the config, the `Features` class, the middleware `match`, the shared-prop closure, the TS type, and each consuming component. Grepping `Features::` surfaces both the app and Fortify classes; only the `use` statement disambiguates.

**Dependency category:** Shallow abstraction — two indirections wrapping one config boolean.

**Test impact:** The middleware's `default => false` branch (unknown feature → 404) is untested; only `'teams'` is ever passed. A generic flag module tests gating once instead of per-feature.

**Deepening idea:** Have `EnsureFeatureEnabled` resolve `config("features.$feature")` generically (dropping the `match`), rename `App\Support\Features` (e.g. `AppFeature`) to end the collision, and add a client-side `useFeature('teams')` hook (or `<Feature>` component) to centralize the prop access.

---

## Candidate 6 — FormRequest boilerplate (7 near-identical files)

**Cluster:** The 7 `app/Http/Requests/*Request.php` files from Candidate 1.

**Why coupled:** Every one carries a copy-pasted private `team()` (and sometimes `member()`) accessor whose only job is to narrow the route binding's type — 7 files contain the exact string `must be a team model`:

```php
private function team(): Team {
    $team = $this->route('team');
    if (! $team instanceof Team) {
        throw new LogicException('The team route parameter must be a team model.');
    }
    return $team;
}
```

Three of these (`CancelTeamInvitationRequest`, `SwitchTeamRequest`, `RemoveTeamMemberRequest`) have empty `rules()` and exist *only* to host an `authorize()` one-liner — the interface (a whole class file + controller import) dwarfs the one line of real logic.

**Dependency category:** Boilerplate shallowness — interface ≈ implementation.

**Test impact:** Mostly deletion; existing controller feature tests already cover the behavior.

**Deepening idea:** A shared base request or trait providing typed `routeTeam()`/`routeMember()` accessors; move the authorization-only requests' checks to policy-mapped route middleware (`can:` middleware), deleting several classes.

---

## Test-suite findings

### Coverage in brief

The suite is healthy at the HTTP boundary: session/login (including throttling and lockout), team CRUD, invitations (create/cancel/accept, expiry, cross-team scoping), member role changes and removal, team selection, profile/password/passkey/2FA endpoints, and the full feature-flag-*off* matrix (`TeamsFeatureDisabledTest`). Unit tests cover every action, the model helpers, the DTOs, the enum, and the policy.

### Gaps

1. **Teams-enabled shared props** — the lazy `currentTeam`/`teams` closures in `HandleInertiaRequests::share()` are never evaluated with a logged-in multi-team user; `TeamPermissions` is never asserted as it reaches a page.
2. **Role-change ripple effects** — `UpdateTeamMemberTest` asserts only the pivot flip; the affected user's resulting permissions/policy verdicts/shared props are tested in separate silos that never meet.
3. **Null-user login fallback** — the `$user !== null ? ... : route('dashboard')` branch in both login response classes is unexercised, and `TwoFactorLoginResponse` has no tests.
4. **`EnsureFeatureEnabled` default branch** — unknown feature → 404 is never hit.
5. **Current-team repointing after removal** — nothing asserts `current_team_id` is reassigned when a member is removed from (or leaves) their current team.
6. **Invitation edges** — accepting when already a member (`firstOrCreate` semantics), invitations to a personal team, expiry exactly at the boundary.

### Tests poking at internals

- `TeamRoleTest` + `TeamPermissionsTest` + `TeamPolicyTest` are three views of one truth table (see Candidate 1).
- `HandleInertiaRequestsTest` news up the middleware directly and asserts the returned array; `assertInertia` against a real route is the actual boundary.
- Action unit tests mirror their controller tests almost line-for-line (e.g. `AcceptTeamInvitationTest` vs the acceptance controller test) — a second maintenance point with little added coverage.

### Fixture duplication signalling a missing module

`resolve(CreateTeam::class)->handle($owner, 'Acme')` appears across **22 test files**, and `$team->memberships()->create(['user_id' => ..., 'role' => TeamRole::...])` appears **20+ times** (8× in `TeamPolicyTest` alone). The recurring arrangement — create user → create team → attach a second user at a role → act — has no owning module. Notably there is no `AddTeamMember` action (only `Update`/`Remove`); adding one plus a `Team::factory()->withMember($user, $role)` state would collapse most of this setup.

---

## Recommendation

- **Candidate 1** is the biggest structural win: it touches the most files, carries the most duplicated string literals, and collapsing it lets permission behavior be tested against one source of truth instead of four parallel test files. It combines naturally with **Candidate 6** (the FormRequests are the outermost layer of the same authorization smear).
- **Candidate 2** hides an actual latent bug (dangling `current_team_id` after removal) and should be fixed regardless of the larger refactors.
- **Candidates 4 + 5** combine well as a "typed frontend contract" effort: a `TeamContext` provider, `Arrayable` DTOs, a generic feature-flag module, and a `useFeature` hook together close the untyped Inertia seam.
- **Candidate 3** is small and self-contained — a good warm-up refactor that also closes the untested 2FA-response gap.
