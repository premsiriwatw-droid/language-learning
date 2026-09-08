# Chinese MVP content

Run `php artisan db:seed --class=ChineseContentSeeder` after the learning-structure owner has populated lessons. `DatabaseSeeder` also calls it after its existing user seed.

No learning-structure seeder/factory is currently provided, and the inspected local SQLite database did not have a lessons table. Consequently, these are proposed lesson mappings, not verified existing lesson titles:

| Language name | Lesson title | Vocabulary | Exercises / questions |
| --- | --- | --- | --- |
| Chinese | Greetings | 6 | 4 / 4 |
| Chinese | Self Introduction | 6 | 4 / 4 |
| Chinese | Numbers | 8 | 4 / 4 |

Each lesson has one multiple-choice, fill-blank, listening, and image-choice question. Total: 20 vocabulary items, 12 exercises, 12 questions, 30 answers. Vocabulary includes Mandarin, tone-marked pinyin, Thai meanings, and optional examples. Pinyin uses dictionary tones; neutral syllables are unmarked.

## Lesson ownership and matching

The seeder traverses `Lesson -> Unit -> Course -> Language` and matches the exact language name and lesson title. There are no slugs or unique title constraints. Missing matches are reported and skipped. Multiple matches anywhere in the Chinese hierarchy are reported and skipped; no arbitrary course/unit is chosen. No numeric IDs or hierarchy creation are used.

Coordinate the three titles and language name with Person 2. If their actual titles differ, update the dataset keys to those exact titles. If titles repeat across courses/units, agree on exact parent titles and extend the lookup before seeding those lessons. Fresh migrations with no lesson data safely seed no Chinese content. Test-only fixtures exercise the complete dataset without introducing production hierarchy factories.

## Repeat runs

Content uses parent-scoped keys: vocabulary word; exercise type plus `Chinese MVP: <type>` title; question text; answer text. Repeating an unchanged dataset updates these records without duplication or deleting unrelated content. These keys identify seeder-managed content; manually authored records with identical keys would also be updated. Renaming keys or changing answer sets requires an explicit data maintenance decision; this seeder does not remove old rows. It is intended for sequential development seeding, not concurrent execution.

Rerun `ChineseContentSeeder` directly. The existing `DatabaseSeeder` user creation is unchanged and is not itself rerunnable because it uses a fixed unique email.

## Media handoff

Only these references are stored; no files are supplied:

- `audio/chinese/greetings/ni-hao.mp3`: spoken 你好
- `audio/chinese/self-introduction/lao-shi.mp3`: spoken 老师
- `audio/chinese/numbers/wu.mp3`: spoken 五
- `images/chinese/greetings/goodbye.jpg`: people waving goodbye
- `images/chinese/self-introduction/teacher.jpg`: a teacher teaching
- `images/chinese/numbers/three-apples.jpg`: exactly three apples

Coordinate the storage disk and asset availability with the Quiz owner (Person 4). Paths are storage-relative. Until assets are supplied, listening/image questions cannot provide their intended media experience. The runtime should handle missing assets. Exercise types remain strings; answers and correctness use the existing Answer model. No runtime, validation, scoring, uploads, or external services are implemented.
