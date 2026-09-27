# Progress profile integration

This feature owns the frontend profile, private avatar uploads, and the new
`lesson_progress` table. It does not modify User/Auth core, content models,
existing migrations, or quiz answer checking.

## Routes

- `GET /profile` (`profile`): authenticated profile dashboard.
- `POST /profile/upload` (`profile.photo.upload`): authenticated, CSRF-protected
  image upload. JPEG/PNG/WebP, at most 2 MiB and 4096 x 4096 pixels.
- `GET /profile/photo` (`profile.photo`): serves only the current user's image
  from the private local disk with no-store/nosniff headers. No storage symlink
  is needed. A new image replaces the previous file.

The existing GET learning-step route uses `RememberLearningVisit`. It records
only authenticated, successfully rendered steps using the original controller's
view data. Opening a step, following a completion URL, or guessing a URL does
not award completion, stars, or XP. Guests keep existing learning behavior.

## Database

Run the additive migration after the existing users/lessons migrations:

```sh
php artisan migrate --path=database/migrations/2026_09_24_000001_create_lesson_progress_table.php
```

Each `(user_id, lesson_id)` has a unique row. Visits update the last opened step;
completion and rewards are preserved when revisiting. Progress percentage is
completed lessons divided by all currently available lessons in the database.
The resume card shows the user's most recently opened lesson and step.
If the progress migration has not been applied, the profile renders without
invented progress and explains that progress recording is unavailable.

## Backend dependencies still pending

1. The trusted lesson/quiz backend must verify that the full lesson is complete
   before calling the following service with team-approved reward values:

   ```php
   app(\App\Services\Progress\LearningProgress::class)
       ->complete($user, $lesson, $xp, $stars);
   ```

   Do not pass reward values from request input. The first completion records
   the supplied rewards; repeated completion cannot grant duplicate rewards.
   No public completion/reward endpoint is provided. No arbitrary reward formula
   is defined here, and no existing quiz controller has been changed.

2. HSK is displayed from the Auth-owned `hsk_level` attribute when available.
   Otherwise the UI says the level has not been specified. No default HSK 1,
   proficiency claim, automatic level calculation, or User schema change is made.

3. Lesson ordering/unlock rules require the team's learning architecture and a
   verified completion integration. This profile change does not enforce locks
   or modify course/lesson data or learning authorization.

## Team integration notes

The working branch is `feature/progress-frontend`. Its locally cached
`origin/main` contains subsequent frontend/content changes, so integrate these
small route changes carefully when the team approves merging. No automatic
pull, merge, commit, push, or dependency installation is part of this change.
The existing uncommitted edit in `FrontendController.php` is left untouched.

The layout now has one HTML document and one content region. The previous
hard-coded header streak/XP counters are removed. Learning and profile links
point to real routes. Profile-specific CSS/JS is local and responsive; the
existing Tailwind/Alpine dependencies used by other views are preserved.

## Checks

```sh
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
php artisan route:list --path=profile -v
```

`ProgressProfileTest` covers authentication, empty states, private avatar access,
invalid uploads, user isolation, saved step navigation, forged finish URLs,
idempotent rewards, missing migration handling, and escaped profile text.

## Verification in this workspace

- Full suite: 47 tests passed, 341 assertions (PHP 8.3.33, the available runtime).
  The requested PHP 8.4 runtime was not available for this verification.
- Profile routes all retain `web` and `auth` middleware; Blade views compile.
- ContentMigrationTest was adjusted with explicit user approval to select its
  two content migrations by path. No content migration or model was changed.
- Desktop and 390px mobile previews use synthetic display data outside the app
  database. Existing user records were not populated with sample rewards.
- The SQLite database was backed up before applying only the new Progress
  migration. The avatar feature uses the existing private local storage disk.
