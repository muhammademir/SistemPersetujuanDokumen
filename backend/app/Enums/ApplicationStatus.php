<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft             = 'draft';
    case Submitted         = 'submitted';
    case UnderReview       = 'under_review';
    case RevisionRequired  = 'revision_required';
    case Approved          = 'approved';
    case Rejected          = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft            => 'Draft',
            self::Submitted        => 'Menunggu Verifikasi',
            self::UnderReview      => 'Sedang Dinilai',
            self::RevisionRequired => 'Perlu Revisi',
            self::Approved         => 'Disetujui',
            self::Rejected         => 'Ditolak',
        };
    }

    /** Transisi status yang diizinkan — inti dari workflow approval */
    public function canTransitionTo(self $next): bool
    {
        return in_array($next, match ($this) {
            self::Draft            => [self::Submitted],
            self::Submitted        => [self::UnderReview, self::Rejected],
            self::UnderReview      => [self::Approved, self::Rejected, self::RevisionRequired],
            self::RevisionRequired => [self::Submitted],
            self::Approved,
            self::Rejected         => [],
        }, true);
    }
}
