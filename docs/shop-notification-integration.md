# Shop, Cosmetics & Notification integration

Owner: Chotikan. Based on main after PR #50. Verification, enrollment policies and analytics calculations retain their existing owners.

## Setup

```sh
composer install
php artisan migrate
php artisan db:seed --class=ShopItemSeeder
php artisan test
```

The migration is additive. Run it before serving the new views. The catalog seeder is repeatable and preserves edited prices/availability. ShopItemSeeder is also included in DatabaseSeeder. Do not run `migrate:fresh` against a database you want to keep.

## Star contract

`App\Services\Shop\StarWallet::summary(User)` returns earned/spent/balance. Earned stars use the existing **completed** lesson_progress rows plus StarRewardService bonuses. Historical XP and stars remain untouched. Profile shows both lifetime stars and spendable balance.

`ShopService::purchase(User, itemId)` is the only debit API. It locks the user before reading balance (same lock order as lesson completion/quest claims), locks the catalog item, takes the server-side price, then creates purchase + inventory in one retried transaction. Unique user/item constraints are a second safeguard. All five types are permanent, non-consumable items; repeat purchases return the existing item without a second debit. Price snapshots do not change with catalog prices. Keep source reward rows if you want lifetime credit preserved: deleting old rewards lowers earned balance, and remaining spendable balance is clamped to zero until earnings cover historical spending.

Different items in one category may be owned; only one is equipped per category. Only the owner can equip, unlisting an item does not revoke ownership, and appearance keys resolve through bundled CSS/emoji in config/cosmetics.php. There is no user-supplied HTML/CSS.

## Notification contract

Use Laravel Notifications and its `notifications` table throughout the team. HubNotification uses a database channel with deterministic IDs per recipient/topic/eventKey. Retrying the same event does not create duplicates or mark a read message unread again. Events dispatch after commit, so rolled-back business operations do not send notifications. Delivery is synchronous database-only; a queue worker is not required.

Currently wired:

| Producer | Integration | Destination |
| --- | --- | --- |
| First lesson completion crossing a level | PlayerLeveledUp → LevelNotificationListener | /profile |
| DailyQuest completed_at changes | NotificationQuestObserver | /daily-quests |
| TeacherApplication status changes | NotificationTeacherApplicationObserver | /teacher/application |
| EnrollmentStatusChanged | EnrollmentNotificationListener | Learner: /teacher-courses/{id}; rejection: /teacher-courses/my with reason in notification. Owner: /teacher/courses/{id}/enrollments for null/rejected → pending requests. |

Enrollment events now carry a generated notificationEventId. Re-dispatch the same event object/serialized ID for retries; new transitions, even within the same second after a rejection and re-request, get new IDs. The observer bridge deliberately lives in the notification module. Direct query-builder updates bypass Eloquent observers; future domain services should dispatch explicit events instead of adding notifications to controllers.

Assignment, recommendation, teacher reply, certificate and achievement producers are not yet implemented on this main. Their enum topics and tested receiving contract are ready. The owning team supplies authorized recipients, stable event keys, and a local destination, for example from its event listener:

```php
use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;

UserNotificationRequested::dispatch(
    $learner->id,
    NotificationTopic::AssignmentNew,
    'assignment:'.$assignment->id.':published:'.$revision,
    'มีงานใหม่ในคอร์สของคุณ',
    $assignment->title,
    '/teacher-courses/'.$course->id,
);
```

One call per authorized recipient; the owning module must not send private data to non-members. Reuse an event key when retrying; use a new revision for a distinct occurrence. Notifications accept only local paths and Blade escapes text. No public endpoint can manufacture events/rewards. GET leaves read state unchanged; read/unread actions require authenticated CSRF-protected PATCH requests scoped to the current user's relation.

## Local demo users

Set `APP_ENV=local` and a password of at least 12 characters as `DEMO_PASSWORD` in your local environment, then:

```sh
php artisan db:seed --class=IntegrationDemoSeeder
```

| Email | State |
| --- | --- |
| learner@demo.langlearn.test | Learner, 100 starter stars, welcome notification |
| pending-teacher@demo.langlearn.test | Pending application, private demo document |
| verified-teacher@demo.langlearn.test | Approved teacher fixture, private demo document |
| admin@demo.langlearn.test | Admin |

This explicit seeder refuses production and never runs from DatabaseSeeder. Rerunning preserves existing accounts/passwords/application states/purchases and does not add stars again. Demo documents are marked DEMO-ONLY. Learning content is seeded separately using the project's content seeders.

## Before merge

- [ ] Update branch from main; review shared files: routes/web.php, profile, layout, bootstrap/providers.php and LearningProgress.
- [ ] Run `composer install`, migrations on a disposable database, then ShopItemSeeder twice; check all ten catalog items and no duplicate migration names.
- [ ] Run `php artisan test` (full suite) and `php artisan test --filter='ShopFlowTest|NotificationHubTest|CrossModuleNotificationTest|IntegrationSmokeTest'`.
- [ ] Run the separate SQLite race suite: `vendor/bin/phpunit -c phpunit.concurrency.xml`. It starts two PHP processes against a disposable database; verify different-item overspending and duplicate-item requests.
- [ ] Verify authenticated routes and existing placement/quests/teacher/dashboard routes with `php artisan route:list --except-vendor`.
- [ ] Buy from two tabs with only enough stars for one item; verify no overdraft, one charge per permanent item, and unchanged XP.
- [ ] Verify all five cosmetics, replacement, unequip, and another user's inventory/notification IDs returning 404.
- [ ] Verify notification after successful level/quest/review/enrollment changes; rollback and replay must not duplicate or leak messages.
- [ ] Check desktop + mobile Shop/Inventory/Profile/Notification pages, empty states and keyboard focus.
- [ ] Confirm no .env, real database, uploaded private document, or local QA adapter is committed.

## Before demo

- [ ] Create a disposable local DB; seed learning content, catalog and explicit demo users. Test all four logins.
- [ ] Show learner completion → quest → stars → purchase → equip → profile → unread/read notification.
- [ ] Show pending teacher decision and enrollment approval/rejection reaching the correct account.
- [ ] Label assignment/recommendation/reply/certificate as integration-ready until their owning modules ship actual producers.
- [ ] Re-run smoke tests after merging each teammate's branch. Check teacher ownership/enrollment tests and dashboard formula tests remain green.
