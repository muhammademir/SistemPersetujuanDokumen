export interface User {
  id: number
  name: string
  email: string
  role: 'pemohon' | 'penilai'
  roles?: string[]
  is_active?: boolean
  created_at?: string
  updated_at?: string
}

export type ApplicationStatus = 'draft' | 'submitted' | 'under_review' | 'revision_required' | 'approved' | 'rejected'

export interface ApplicationStatusObj {
  value: ApplicationStatus
  label: string
}

export interface ApplicationDocument {
  id: number
  original_name: string
  mime_type: string
  size_bytes: number
  revision_number: number
  uploaded_at: string
}

export interface ApplicationReview {
  id: number
  decision: string
  note: string
  revision_number: number
  reviewed_at: string
  reviewer?: string
}

export interface StatusLog {
  id: number
  from_status: string | null
  to_status: string
  note: string
  actor?: string
  created_at: string
}

export interface Application {
  id: number
  code: string
  title: string
  description: string
  document_type: string
  status: ApplicationStatusObj
  revision_count: number
  submitted_at: string | null
  decided_at: string | null
  created_at: string
  updated_at: string
  applicant?: {
    id: number
    name: string
    email: string
  }
  reviewer?: {
    id: number
    name: string
  }
  documents_count?: number
  documents?: ApplicationDocument[]
  reviews?: ApplicationReview[]
  status_logs?: StatusLog[]
}

// Keep backward compat alias
export type Document = Application

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  role: 'pemohon' | 'penilai'
}

export interface ApiResponse<T = any> {
  data: T
  message?: string
  status: boolean
}

export interface DashboardStats {
  total: number
  draft: number
  submitted: number
  under_review: number
  revision_required: number
  approved: number
  rejected: number
}

export interface DashboardSummary {
  by_status: Record<string, number>
  monthly: { period: string; total: number }[]
  avg_processing_days: number | null
}

export const STATUS_LABELS: Record<ApplicationStatus, string> = {
  draft: 'Draft',
  submitted: 'Menunggu Verifikasi',
  under_review: 'Sedang Dinilai',
  revision_required: 'Perlu Revisi',
  approved: 'Disetujui',
  rejected: 'Ditolak',
}

export const STATUS_COLORS: Record<ApplicationStatus, string> = {
  draft: 'stone',
  submitted: 'warning',
  under_review: 'info',
  revision_required: 'orange',
  approved: 'success',
  rejected: 'error',
}
