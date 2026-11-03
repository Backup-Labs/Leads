export interface User {
  id: number;
  name: string;
  email: string;
  avatar_url?: string;
}

export interface Organization {
  id: number;
  name: string;
  slug: string;
  currency: string;
  timezone: string;
  billing_email?: string;
  phone?: string;
  logo_url?: string;
}

export interface OrganizationSetting {
  id?: number;
  organization_id?: number;
  default_currency: string;
  default_tax_rate: number;
  stale_lead_threshold_days: number;
  quote_followup_threshold_days: number;
  high_value_threshold: number;
  quotation_default_terms?: string;
}

export interface OrganizationMember {
  id: number;
  organization_id: number;
  user_id: number;
  role: 'owner' | 'admin' | 'manager' | 'sales' | string;
  role_label?: string;
  user?: User;
}

export interface PipelineStage {
  id: number;
  name: string;
  slug: string;
  color: string;
  sort_order: number;
  is_won: boolean;
  is_lost: boolean;
  leads_count?: number;
  total_value?: number;
  leads?: Lead[];
}

export interface LeadSource {
  id: number;
  name: string;
  slug: string;
  color?: string;
}

export interface Activity {
  id: number;
  type: string;
  subject: string;
  description?: string;
  created_at: string;
  user?: User;
  lead?: Lead;
  metadata?: Record<string, any>;
}

export interface FollowUp {
  id: number;
  lead_id: number;
  title: string;
  notes?: string;
  scheduled_at: string;
  completed_at?: string;
  status: 'pending' | 'completed' | 'overdue' | 'cancelled';
  completion_notes?: string;
  lead?: Lead;
  assigned_to?: User;
}

export interface QuotationItem {
  id?: number;
  description: string;
  quantity: number;
  unit_price: number;
  total_price: number;
  sort_order?: number;
}

export interface Quotation {
  id: number;
  lead_id: number;
  quotation_number: string;
  token: string;
  customer_name: string;
  customer_email?: string;
  customer_phone?: string;
  company_name?: string;
  subtotal: number;
  discount_amount: number;
  tax_rate: number;
  tax_amount: number;
  total_amount: number;
  currency: string;
  status: 'draft' | 'sent' | 'accepted' | 'rejected' | 'expired';
  validity_date?: string;
  notes?: string;
  terms_and_conditions?: string;
  sent_at?: string;
  viewed_at?: string;
  responded_at?: string;
  created_at?: string;
  items?: QuotationItem[];
  lead?: Lead;
  created_by?: User;
  organization?: Organization;
}

export interface RecoveryAlert {
  id: number;
  alert_type: string;
  severity: 'critical' | 'warning' | 'notice';
  reason: string;
  suggested_action: string;
  potential_revenue_at_risk: number;
  lead?: Lead;
  quotation?: Quotation;
}

export interface Lead {
  id: number;
  organization_id: number;
  pipeline_stage_id: number;
  lead_source_id?: number;
  assigned_to_user_id?: number;
  customer_name: string;
  email?: string;
  phone?: string;
  company_name?: string;
  requested_service?: string;
  estimated_value: number;
  currency: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  status: 'active' | 'won' | 'lost';
  description?: string;
  lost_reason?: string;
  last_contacted_at?: string;
  converted_at?: string;
  created_at: string;
  stage?: PipelineStage;
  source?: LeadSource;
  assigned_to?: User;
  activities?: Activity[];
  follow_ups?: FollowUp[];
  quotations?: Quotation[];
  recovery_alerts?: RecoveryAlert[];
}

export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from?: number | null;
  to?: number | null;
  links: {
    url: string | null;
    label: string;
    active: boolean;
  }[];
}

export interface PageProps {
  auth: {
    user: User | null;
    currentOrganization: Organization | null;
    currentMember: {
      role: string;
      role_label: string;
    } | null;
    organizations: Organization[];
  };
  flash: {
    success?: string;
    error?: string;
    warning?: string;
  };
  [key: string]: any;
}
