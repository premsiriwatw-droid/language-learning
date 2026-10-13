<?php

namespace App\Services\Content;

class VocabularyBatchPlanner
{
    /** Group runtime vocabulary around questions, without changing source data. */
    public function plan(array $vocabularyIds, array $requirements, int $size): array
    {
        $size = max(1, $size);
        $remaining = array_values($vocabularyIds);
        $learned = [];
        $batches = [];

        while ($remaining !== []) {
            $batch = [];

            while (count($batch) < $size) {
                $best = null;

                foreach ($requirements as $required) {
                    $needed = array_values(array_diff($required, $learned, $batch));

                    if (
                        $needed === []
                        || count(array_diff($needed, $remaining)) > 0
                        || count($needed) > $size - count($batch)
                    ) {
                        continue;
                    }

                    if ($best === null || count($needed) < count($best)) {
                        $best = $needed;
                    }
                }

                if ($best === null) {
                    break;
                }

                foreach ($remaining as $id) {
                    if (in_array($id, $best, true)) {
                        $batch[] = $id;
                    }
                }
            }

            foreach ($remaining as $id) {
                if (count($batch) >= $size) {
                    break;
                }

                if (!in_array($id, $batch, true)) {
                    $batch[] = $id;
                }
            }

            $batches[] = $batch;
            $learned = array_merge($learned, $batch);
            $remaining = array_values(array_diff($remaining, $batch));
        }

        return $batches;
    }
}
