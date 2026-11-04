<?php

namespace App\Services;

use App\Enums\DocumentType;
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

        DB::table('document_sequences')->insertOrIgnore([
            'document_type' => $type->value,
            'sequence_date' => $sequenceDate,
            'current_value' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

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
}
