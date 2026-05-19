# Skyway - AI Coding Guidelines

You are an expert Laravel engineer. Produce concise, production-ready code and minimal prose.

---

## Project Context

Skyway is a flight management REST API. It exposes three endpoints for creating, updating, and retrieving flights. A flight contains one or more legs, each with one or more segments.

**Domain characteristics:**
- Pure JSON API - no frontend, no Blade views, no Livewire.
- All API endpoints are protected by a static `Api-Key` header.
- Flight updates are processed asynchronously via a Redis-backed queue (Horizon).
- Update requests are idempotent - duplicate requests with the same `Idempotency-Key` are ignored.
- Transactional integrity is required: a flight and all its legs/segments are written atomically.

---

## Tech Stack

| Layer | Choice |
|---|---|
| PHP | 8.3+ |
| Framework | Laravel 13.x |
| Database | MySQL |
| Cache / Queue | Redis + Laravel Horizon |
| Authentication | Static `Api-Key` header via middleware |
| Testing | Pest 4 + PHPUnit 12 |
| Formatter | Laravel Pint |
| AI assistance | Laravel Boost |
| Local dev | Laravel Sail (Docker) - serves `http://localhost` |

---

## Architecture

### Folder Structure

```
app/
├── Models/          Eloquent persistence models only
├── Actions/         Business workflows, one subfolder per primary model
│   ├── Flight/      CreateFlight, UpdateFlight
│   └── ...
├── Http/
│   ├── Controllers/ Thin: validate, dispatch, transform
│   ├── Requests/    FormRequest validation
│   ├── Resources/   Eloquent API Resources
│   └── Middleware/  ApiKeyMiddleware, IdempotencyMiddleware
└── Jobs/            Queued jobs (e.g. UpdateFlightJob)
```

### Cardinal Rules

1. **`app/Models/` contains Eloquent classes only.**
2. **Business logic lives in `app/Actions/<Model>/`.** Models are persistence, not behavior.
3. **Controllers stay thin.** Pattern: `validate -> dispatch Action or Job -> transform response`. Maximum ~25 lines.
   - Validation is always via `FormRequest`. Never call `$request->validate([...])` inline.
   - Response shaping is always via `JsonResource` or `ResourceCollection`. Never return raw models or arrays.
4. **Queue jobs dispatch Actions.** A Job is responsible for one unit of async work; it delegates business logic to the relevant Action.

### Action Pattern

```php
final class CreateFlightAction
{
    public function execute(CreateFlightData $flightData): Flight
    {
        // single responsibility
    }
}
```

One Action = one workflow = one `execute()` method (or `__invoke()`).

---

## Code Quality Constraints (Hard Limits)

- Every PHP file MUST start with `declare(strict_types=1);`
- Each method MUST NOT have more than **3 return statements**.
- Cognitive Complexity MUST NOT exceed **15** per method.
- Avoid deep nesting (max 3 levels of indentation in a method).
- Files MUST be focused. Classes longer than ~300 lines should be reviewed for a split.
- No inline comments except required docblocks such as `@throws`.
- Use named identifiers that explain intent. No `$data`, `$result`, `$obj`.
- Use `final` on all classes unless inheritance is genuinely required.
- Prefer constructor promotion, typed properties, and `readonly` where it fits.
- Use strict comparison (`===`, `!==`) always.

---

## Testing

- **Pest 4** is the primary test framework.
- Write tests for every behavior change: happy path + key edge cases.
- Use factories for model creation in tests.
- Feature tests cover full HTTP request/response cycles including auth headers.

### Running Tests

```bash
php artisan test --compact
php artisan test --compact --filter=CreateFlightTest
```

---

## Git Workflow

### Commit Message Convention

Follows **Conventional Commits**. Prefix with a type:

| Type | Use for |
|---|---|
| `feat` | New feature |
| `fix` | Bug fix |
| `refactor` | Code change with no behavior change |
| `chore` | Tooling, config, dependencies |
| `docs` | Documentation only |
| `test` | Adding or updating tests |

Format: `<type>: <imperative short description in lowercase>`

### Commit Authorization Rules

**NEVER run `git commit` or `git push` without a separate, explicit instruction for each operation.**

- "commit et" means commit only. It does NOT mean push.
- "push et" or "pushla" means push only. It does NOT mean commit anything new.
- These are two distinct, separate authorizations. Never chain them silently.

### Commit Attribution Rules

When committing on behalf of the user, **DO NOT add self-attribution or AI signatures**:

- **NEVER** add `Co-Authored-By: Claude` or any AI co-author footer.
- **NEVER** add tool watermarks like "Generated with Claude Code".
- Commits MUST appear authored by the human developer only.

---

## Post-Pull Checklist

After pulling new changes:

1. `composer install` (if `composer.lock` changed)
2. `php artisan migrate` (if new migrations exist)
3. `php artisan queue:restart` (if queue code changed)

---

## Security

- Validate all inputs at the boundary via `FormRequest`.
- The `Api-Key` header check must happen before any business logic.
- Idempotency keys must be stored and checked atomically to prevent race conditions.
- Never log secrets, API keys, or full request payloads.

---

## Output Contract (Quick Checklist)

Before considering work done, verify:

- [ ] `declare(strict_types=1);` at the top of every PHP file
- [ ] Action pattern: controllers thin, logic in Actions
- [ ] Maximum 3 return statements per method
- [ ] Cognitive Complexity 15 or below per method
- [ ] No inline comments except required `@throws` docblocks
- [ ] All Pint rules satisfied (`vendor/bin/pint --dirty` produces no diff)
- [ ] Pest tests cover the change (happy path + at least one edge case)
- [ ] No em-dashes or en-dashes in any text output

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
