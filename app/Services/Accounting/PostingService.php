<?php
namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostingService
{
    public function createEntry(array $entry, array $lines): JournalEntry
    {
        if (empty($lines)) {
            throw ValidationException::withMessages(['journal' => 'Journal lines tidak boleh kosong.']);
        }

        $sumD = 0; $sumC = 0;

        foreach ($lines as $l) {
            if (!in_array($l['dc'] ?? null, ['D', 'C'], true)) {
                throw ValidationException::withMessages(['dc' => "Nilai dc tidak valid: '{$l['dc']}'. Harus 'D' atau 'C'."]);
            }
            $amt = (int) $l['amount'];
            if ($amt <= 0) {
                throw ValidationException::withMessages(['amount' => 'Amount jurnal harus > 0']);
            }
            if ($l['dc'] === 'D') $sumD += $amt;
            if ($l['dc'] === 'C') $sumC += $amt;
        }

        if ($sumD !== $sumC) {
            throw ValidationException::withMessages([
                'journal' => "Jurnal tidak balance. Debit=$sumD, Kredit=$sumC"
            ]);
        }

        return DB::transaction(function () use ($entry, $lines) {
            $je = JournalEntry::create($entry);
            $je->lines()->createMany($lines);
            return $je;
        });
    }

    public function voidBySource(string $sourceType, int $sourceId, string $reason): void
    {
        JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('is_voided', false)
            ->update([
                'is_voided' => true,
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);
    }

    public function accountById(int $id): Account
    {
        return Account::findOrFail($id);
    }

    public function accountByCode(string $code): Account
    {
        return Account::where('code', $code)->firstOrFail();
    }
}