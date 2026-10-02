export interface User {
  id: number
  name: string
  email: string
  role: 'pemohon' | 'penilai'
  created_at?: string
  updated_at?: string
}

export interface Document {
  id: number
  title: string
  description: string
  file_path: string
  file_name: string
  status: DocumentStatus
  user_id: number
  user?: User
  reviews?: Review[]
  created_at: string
  updated_at: string
}

export type DocumentStatus = 'draft' | 'pending' | 'approved' | 'revision' | 'rejected'

export interface Review {
  id: number
  document_id: number
  reviewer_id: number
  reviewer?: User
  status: 'approved' | 'revision' | 'rejected'
  notes: string
  created_at: string
  updated_at: string
}

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
  pending: number
  approved: number
  revision: number
  rejected: number
}
