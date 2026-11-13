-- Generated schema for the interior service application.
CREATE TABLE IF NOT EXISTS users (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  email varchar(254) NOT NULL UNIQUE,
  password_hash varchar(255) NOT NULL,
  full_name varchar(150) NOT NULL,
  phone varchar(20),
  is_active TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id int PRIMARY KEY AUTO_INCREMENT,
  code varchar(30) NOT NULL UNIQUE,
  name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id bigint NOT NULL,
  role_id int NOT NULL,
  PRIMARY KEY (user_id,role_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS apartments (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  customer_id bigint NOT NULL,
  name varchar(150) NOT NULL,
  address varchar(500) NOT NULL,
  area_m2 decimal(10,2) NOT NULL,
  notes text,
  created_at DATETIME NOT NULL,
  CHECK (area_m2 > 0),
  FOREIGN KEY (customer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
  id int PRIMARY KEY AUTO_INCREMENT,
  code varchar(30) NOT NULL UNIQUE,
  name varchar(150) NOT NULL,
  service_type varchar(20) NOT NULL,
  description text,
  reference_price decimal(18,0),
  is_active TINYINT NOT NULL DEFAULT 1,
  CHECK (service_type IN ('DESIGN','INSTALLATION','MAINTENANCE','PACKAGE')),
  CHECK (reference_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_requests (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  code varchar(30) NOT NULL UNIQUE,
  customer_id bigint NOT NULL,
  apartment_id bigint NOT NULL,
  service_id int NOT NULL,
  consultant_id bigint,
  parent_request_id bigint,
  description text NOT NULL,
  budget decimal(18,0),
  preferred_date date,
  status varchar(20) NOT NULL DEFAULT 'NEW',
  cancellation_reason text,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CHECK (budget >= 0),
  CHECK (status IN ('NEW','RECEIVED','QUOTED','APPROVED','IN_PROGRESS','COMPLETED','CLOSED','REJECTED','CANCELLED')),
  FOREIGN KEY (customer_id) REFERENCES users(id),
  FOREIGN KEY (apartment_id) REFERENCES apartments(id),
  FOREIGN KEY (service_id) REFERENCES services(id),
  FOREIGN KEY (consultant_id) REFERENCES users(id),
  FOREIGN KEY (parent_request_id) REFERENCES service_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS surveys (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  request_id bigint NOT NULL,
  surveyor_id bigint NOT NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'PENDING',
  notes text,
  result text,
  customer_confirmed_at DATETIME,
  created_by bigint NOT NULL,
  created_at DATETIME NOT NULL,
  CHECK (ends_at > starts_at),
  CHECK (status IN ('PENDING','CONFIRMED','DONE','CANCELLED')),
  FOREIGN KEY (request_id) REFERENCES service_requests(id),
  FOREIGN KEY (surveyor_id) REFERENCES users(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotations (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  request_id bigint NOT NULL,
  version int NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'DRAFT',
  notes text,
  customer_feedback text,
  created_by bigint NOT NULL,
  created_at DATETIME NOT NULL,
  sent_at DATETIME,
  responded_by bigint,
  responded_at DATETIME,
  UNIQUE (request_id,version),
  CHECK (version > 0),
  CHECK (status IN ('DRAFT','SENT','ACCEPTED','REJECTED')),
  FOREIGN KEY (request_id) REFERENCES service_requests(id),
  FOREIGN KEY (created_by) REFERENCES users(id),
  FOREIGN KEY (responded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_items (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  quotation_id bigint NOT NULL,
  item_name varchar(200) NOT NULL,
  unit varchar(30) NOT NULL,
  quantity decimal(12,2) NOT NULL,
  unit_price decimal(18,0) NOT NULL,
  CHECK (quantity > 0),
  CHECK (unit_price >= 0),
  FOREIGN KEY (quotation_id) REFERENCES quotations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_orders (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  request_id bigint NOT NULL,
  assignee_id bigint NOT NULL,
  assigned_by bigint NOT NULL,
  work_type varchar(20) NOT NULL,
  title varchar(200) NOT NULL,
  description text,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'ASSIGNED',
  created_at DATETIME NOT NULL,
  CHECK (ends_at > starts_at),
  CHECK (work_type IN ('DESIGN','INSTALLATION','MAINTENANCE')),
  CHECK (status IN ('ASSIGNED','IN_PROGRESS','REPORTED','DONE','CANCELLED')),
  FOREIGN KEY (request_id) REFERENCES service_requests(id),
  FOREIGN KEY (assignee_id) REFERENCES users(id),
  FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS design_versions (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  work_order_id bigint NOT NULL,
  version int NOT NULL,
  description text,
  status varchar(30) NOT NULL DEFAULT 'SUBMITTED',
  submitted_by bigint NOT NULL,
  submitted_at DATETIME NOT NULL,
  reviewed_by bigint,
  reviewed_at DATETIME,
  feedback text,
  UNIQUE (work_order_id,version),
  CHECK (version > 0),
  CHECK (status IN ('SUBMITTED','APPROVED','REVISION_REQUESTED')),
  FOREIGN KEY (work_order_id) REFERENCES work_orders(id),
  FOREIGN KEY (submitted_by) REFERENCES users(id),
  FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_updates (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  work_order_id bigint NOT NULL,
  author_id bigint NOT NULL,
  event_type varchar(30) NOT NULL,
  progress_percent int,
  content text NOT NULL,
  created_at DATETIME NOT NULL,
  CHECK (progress_percent BETWEEN 0 AND 100),
  FOREIGN KEY (work_order_id) REFERENCES work_orders(id),
  FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  request_id bigint NOT NULL,
  quotation_id bigint NOT NULL,
  receipt_code varchar(50) NOT NULL UNIQUE,
  amount decimal(18,0) NOT NULL,
  method varchar(20) NOT NULL,
  status varchar(10) NOT NULL DEFAULT 'POSTED',
  paid_at DATETIME NOT NULL,
  recorded_by bigint NOT NULL,
  created_at DATETIME NOT NULL,
  voided_by bigint,
  voided_at DATETIME,
  void_reason text,
  CHECK (amount > 0),
  CHECK (method IN ('CASH','BANK_TRANSFER')),
  CHECK (status IN ('POSTED','VOID')),
  FOREIGN KEY (request_id) REFERENCES service_requests(id),
  FOREIGN KEY (quotation_id) REFERENCES quotations(id),
  FOREIGN KEY (recorded_by) REFERENCES users(id),
  FOREIGN KEY (voided_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attachments (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  request_id bigint NOT NULL,
  design_version_id bigint,
  work_update_id bigint,
  uploaded_by bigint NOT NULL,
  original_name varchar(255) NOT NULL,
  storage_key varchar(500) NOT NULL UNIQUE,
  mime_type varchar(100) NOT NULL,
  size_bytes bigint NOT NULL,
  uploaded_at DATETIME NOT NULL,
  CHECK (size_bytes > 0 AND size_bytes <= 10485760),
  CHECK (design_version_id IS NULL OR work_update_id IS NULL),
  FOREIGN KEY (request_id) REFERENCES service_requests(id),
  FOREIGN KEY (design_version_id) REFERENCES design_versions(id),
  FOREIGN KEY (work_update_id) REFERENCES work_updates(id),
  FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id bigint PRIMARY KEY AUTO_INCREMENT,
  actor_id bigint NOT NULL,
  request_id bigint,
  entity_type varchar(50) NOT NULL,
  entity_id bigint NOT NULL,
  action varchar(50) NOT NULL,
  old_status varchar(30),
  new_status varchar(30),
  reason text,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (actor_id) REFERENCES users(id),
  FOREIGN KEY (request_id) REFERENCES service_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (attempt_key varchar(64) PRIMARY KEY, failures int NOT NULL, last_attempt bigint NOT NULL) ENGINE=InnoDB;

ALTER TABLE quotations ADD COLUMN sent_request_key bigint GENERATED ALWAYS AS (CASE WHEN status='SENT' THEN request_id ELSE NULL END) STORED UNIQUE, ADD COLUMN accepted_request_key bigint GENERATED ALWAYS AS (CASE WHEN status='ACCEPTED' THEN request_id ELSE NULL END) STORED UNIQUE;

ALTER TABLE design_versions ADD COLUMN approved_work_key bigint GENERATED ALWAYS AS (CASE WHEN status='APPROVED' THEN work_order_id ELSE NULL END) STORED UNIQUE;

CREATE INDEX idx_service_requests_customer_id ON service_requests(customer_id,created_at);

CREATE INDEX idx_service_requests_consultant_id ON service_requests(consultant_id,status);

CREATE INDEX idx_work_orders_assignee_id ON work_orders(assignee_id,starts_at);

CREATE INDEX idx_surveys_surveyor_id ON surveys(surveyor_id,starts_at);

CREATE INDEX idx_payments_request_id ON payments(request_id,status);
CREATE TABLE IF NOT EXISTS quotation_specs (
 quotation_id bigint PRIMARY KEY,
 data_json LONGTEXT NOT NULL,
 updated_at DATETIME NOT NULL,
 FOREIGN KEY (quotation_id) REFERENCES quotations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS quotation_attachments (
 attachment_id bigint PRIMARY KEY,
 quotation_id bigint NOT NULL,
 purpose varchar(20) NOT NULL,
 description varchar(500) NOT NULL,
 FOREIGN KEY (quotation_id) REFERENCES quotations(id),
 FOREIGN KEY (attachment_id) REFERENCES attachments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commerce extension
CREATE TABLE IF NOT EXISTS app_migrations (name varchar(80) PRIMARY KEY, applied_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_categories (id bigint PRIMARY KEY AUTO_INCREMENT, name varchar(120) NOT NULL UNIQUE, description TEXT NOT NULL, is_active int NOT NULL DEFAULT 1) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS material_suppliers (id bigint PRIMARY KEY AUTO_INCREMENT, name varchar(120) NOT NULL UNIQUE, description TEXT NOT NULL, is_active int NOT NULL DEFAULT 1) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_variants (id bigint PRIMARY KEY AUTO_INCREMENT, service_id int NOT NULL, category_id bigint NOT NULL, supplier_id bigint, name varchar(150) NOT NULL, description TEXT NOT NULL, unit varchar(40) NOT NULL, unit_price bigint NOT NULL, is_active int NOT NULL DEFAULT 1, FOREIGN KEY(service_id) REFERENCES services(id), FOREIGN KEY(category_id) REFERENCES service_categories(id), FOREIGN KEY(supplier_id) REFERENCES material_suppliers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS membership_tiers (id bigint PRIMARY KEY AUTO_INCREMENT, name varchar(100) NOT NULL UNIQUE, min_spend bigint NOT NULL UNIQUE, description TEXT NOT NULL, is_active int NOT NULL DEFAULT 1) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS campaigns (id bigint PRIMARY KEY AUTO_INCREMENT, name varchar(150) NOT NULL, service_id int, discount_kind varchar(10) NOT NULL, discount_value bigint NOT NULL, max_discount bigint NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, is_active int NOT NULL DEFAULT 1, FOREIGN KEY(service_id) REFERENCES services(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vouchers (id bigint PRIMARY KEY AUTO_INCREMENT, code varchar(40) NOT NULL UNIQUE, description TEXT NOT NULL, discount_kind varchar(10) NOT NULL, discount_value bigint NOT NULL, min_total bigint NOT NULL DEFAULT 0, max_discount bigint NOT NULL, max_uses int NOT NULL, per_user_limit int NOT NULL, required_tier_id bigint, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, is_active int NOT NULL DEFAULT 1, FOREIGN KEY(required_tier_id) REFERENCES membership_tiers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_vouchers (user_id bigint NOT NULL, voucher_id bigint NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(user_id,voucher_id), FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(voucher_id) REFERENCES vouchers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cart_items (id bigint PRIMARY KEY AUTO_INCREMENT, user_id bigint NOT NULL, variant_id bigint NOT NULL, quantity int NOT NULL, is_selected int NOT NULL DEFAULT 1, UNIQUE(user_id,variant_id), FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(variant_id) REFERENCES service_variants(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS checkout_batches (id bigint PRIMARY KEY AUTO_INCREMENT, customer_id bigint NOT NULL, apartment_id bigint NOT NULL, token_hash varchar(64) NOT NULL UNIQUE, code varchar(40) NOT NULL UNIQUE, voucher_code varchar(40), estimated_total bigint NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(apartment_id) REFERENCES apartments(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS request_orders (request_id bigint PRIMARY KEY, batch_id bigint NOT NULL, variant_id bigint NOT NULL, quantity int NOT NULL, estimated_subtotal bigint NOT NULL, campaign_id bigint, voucher_code varchar(40), FOREIGN KEY(request_id) REFERENCES service_requests(id), FOREIGN KEY(batch_id) REFERENCES checkout_batches(id), FOREIGN KEY(variant_id) REFERENCES service_variants(id), FOREIGN KEY(campaign_id) REFERENCES campaigns(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_adjustments (quotation_id bigint PRIMARY KEY, campaign_id bigint, voucher_id bigint, campaign_discount bigint NOT NULL DEFAULT 0, voucher_discount bigint NOT NULL DEFAULT 0, label varchar(500) NOT NULL, FOREIGN KEY(quotation_id) REFERENCES quotations(id), FOREIGN KEY(campaign_id) REFERENCES campaigns(id), FOREIGN KEY(voucher_id) REFERENCES vouchers(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voucher_redemptions (id bigint PRIMARY KEY AUTO_INCREMENT, voucher_id bigint NOT NULL, customer_id bigint NOT NULL, quotation_id bigint NOT NULL UNIQUE, status varchar(12) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY(voucher_id) REFERENCES vouchers(id), FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(quotation_id) REFERENCES quotations(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_reviews (id bigint PRIMARY KEY AUTO_INCREMENT, request_id bigint NOT NULL UNIQUE, customer_id bigint NOT NULL, service_id int NOT NULL, rating int NOT NULL, content TEXT NOT NULL, status varchar(12) NOT NULL DEFAULT 'PUBLISHED', reply TEXT, replied_by bigint, replied_at DATETIME, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(request_id) REFERENCES service_requests(id), FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(service_id) REFERENCES services(id), FOREIGN KEY(replied_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS review_images (review_id bigint PRIMARY KEY, storage_key varchar(100) NOT NULL UNIQUE, mime_type varchar(50) NOT NULL, size_bytes bigint NOT NULL, FOREIGN KEY(review_id) REFERENCES service_reviews(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_notifications (id bigint PRIMARY KEY AUTO_INCREMENT, user_id bigint NOT NULL, event_key varchar(120) NOT NULL, title varchar(180) NOT NULL, body TEXT NOT NULL, target_page varchar(60) NOT NULL, target_id bigint, created_at DATETIME NOT NULL, read_at DATETIME, UNIQUE(user_id,event_key), FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (id bigint PRIMARY KEY AUTO_INCREMENT, user_id bigint, request_key varchar(64) NOT NULL, token_hash varchar(64) UNIQUE, expires_at DATETIME NOT NULL, used_at DATETIME, created_at DATETIME NOT NULL, FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consultation_leads (id bigint PRIMARY KEY AUTO_INCREMENT, reference_code varchar(40) NOT NULL UNIQUE, full_name varchar(150) NOT NULL, email varchar(254) NOT NULL, phone varchar(30) NOT NULL, address varchar(500) NOT NULL, service_id int NOT NULL, content TEXT NOT NULL, preferred_date varchar(10), status varchar(15) NOT NULL DEFAULT 'NEW', staff_note TEXT, handled_by bigint, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(service_id) REFERENCES services(id), FOREIGN KEY(handled_by) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_intents (id bigint PRIMARY KEY AUTO_INCREMENT, request_id bigint NOT NULL, customer_id bigint NOT NULL, reference_code varchar(60) NOT NULL UNIQUE, amount bigint NOT NULL, provider varchar(20) NOT NULL, status varchar(20) NOT NULL DEFAULT 'PENDING', provider_txn varchar(100), payment_id bigint UNIQUE, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(request_id) REFERENCES service_requests(id), FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(payment_id) REFERENCES payments(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
