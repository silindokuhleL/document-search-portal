export interface Document {
  id: number;
  filename: string;
  original_filename: string;
  file_path: string;
  file_size: number;
  file_type: string;
  content_text?: string;
  created_at: string;
  updated_at?: string;
}

export interface DocumentListResponse {
  documents: Document[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export interface SearchResult {
  id: number;
  filename: string;
  original_filename: string;
  file_size: number;
  file_type: string;
  created_at: string;
  relevance: number;
  preview: string;
}

export interface SearchResponse {
  results: SearchResult[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
  searchTime: number;
}
