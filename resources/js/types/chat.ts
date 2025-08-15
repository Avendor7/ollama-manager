export interface MessageType {
  id?: number;
  chat_session_id?: number;
  content: string;
  role: string;
  token_count?: number;
  metadata?: any;
  created_at?: string;
  updated_at?: string;
}

export interface ChatSession {
  id: number;
  title: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Model {
  name: string;
  description?: string;
  size: number;
  modifiedAt: string;
  digest: string;
  details?: {
    format: string;
    family: string;
    parameterSize: string;
    quantizationLevel: string;
    families: string[];
    parentModel: string;
  };
}

export interface ModelList {
  models: Model[];
}
