import type { ApplicationStatus, ApplicationStatusObj } from '@/types'

/**
 * Format tanggal standar Indonesia (contoh: 3 Okt 2026)
 */
export function formatDate(dateStr?: string | null): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

/**
 * Format tanggal dan waktu standar Indonesia (contoh: 3 Okt 2026, 14:30)
 */
export function formatDateTime(dateStr?: string | null): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Format waktu relatif (contoh: 5 menit lalu, 2 jam lalu)
 */
export function formatRelativeTime(dateStr?: string | null): string {
  if (!dateStr) return '-'
  const diffSec = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
  if (diffSec < 60) return 'Baru saja'
  if (diffSec < 3600) return `${Math.floor(diffSec / 60)} mnt lalu`
  if (diffSec < 86400) return `${Math.floor(diffSec / 3600)} jam lalu`
  if (diffSec < 604800) return `${Math.floor(diffSec / 86400)} hari lalu`
  return formatDate(dateStr)
}

/**
 * Format ukuran file ke format byte/KB/MB yang mudah dibaca
 */
export function formatFileSize(bytes?: number): string {
  if (!bytes || bytes <= 0) return '0 B'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

/**
 * Label nama keputusan verifikasi penilai
 */
export function formatDecision(decision: string): string {
  const map: Record<string, string> = {
    approved: 'Disetujui',
    revision_required: 'Perlu Revisi',
    rejected: 'Ditolak',
  }
  return map[decision] ?? decision
}

/**
 * Label deskriptif tahapan status
 */
export function formatStatusName(status?: string | null): string {
  if (!status) return '-'
  const map: Record<string, string> = {
    draft: 'Draft Permohonan',
    submitted: 'Pengajuan Masuk',
    under_review: 'Proses Penilaian',
    revision_required: 'Diminta Revisi',
    approved: 'Permohonan Disetujui',
    rejected: 'Permohonan Ditolak',
  }
  return map[status] ?? status
}

/**
 * Ekstraksi nilai status murni (string) dari ApplicationStatus object atau string
 */
export function getStatusValue(status?: ApplicationStatus | ApplicationStatusObj | string | null): string {
  if (!status) return 'draft'
  if (typeof status === 'object' && 'value' in status) {
    return status.value
  }
  return String(status)
}
