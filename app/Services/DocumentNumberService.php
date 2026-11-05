<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\Purchase;
use App\Models\Sale;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate a daily document number within an open database transaction.
     */
    public function next(DocumentType $type, DateTimeInterface $date): string
    {
        $sequenceDate = $date->format('Y-m-d');
        $timestamp = now();

        $sequenceQuery = DB::table('document_sequences')
            ->where('document_type', $type->value)
            ->where('sequence_date', $sequenceDate);

        if (! $sequenceQuery->exists()) {
            DB::table('document_sequences')->insertOrIgnore([
                'document_type' => $type->value,
                'sequence_date' => $sequenceDate,
                'current_value' => $this->existingSequenceValue($type, $sequenceDate),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        $sequence = DB::table('document_sequences')
            ->where('document_type', $type->value)
            ->where('sequence_date', $sequenceDate)
            ->lockForUpdate()
            ->first();

        $nextValue = ((int) $sequence->current_value) + 1;

        DB::table('document_sequences')
            ->where('id', $sequence->id)
            ->update([
                'current_value' => $nextValue,
                'updated_at' => $timestamp,
            ]);

        return sprintf('%s-%s-%04d', $type->prefix(), $date->format('Ymd'), $nextValue);
    }

    private function existingSequenceValue(DocumentType $type, string $sequenceDate): int
    {
        $numbers = match ($type) {
            DocumentType::Purchase => Purchase::query()
                ->whereDate('purchase_date', $sequenceDate)
                ->pluck('purchase_number'),
            DocumentType::Sale => Sale::query()
                ->whereDate('sale_date', $sequenceDate)
                ->pluck('sale_number'),
        };

        $pattern = '/^'.preg_quote($type->prefix(), '/').'-'.str_replace('-', '', $sequenceDate).'-(\d+)$/D';
        $highestValue = 0;

        foreach ($numbers as $number) {
            if (preg_match($pattern, $number, $matches) === 1) {
                $highestValue = max($highestValue, (int) $matches[1]);
            }
        }

        return $highestValue;
    }
}
