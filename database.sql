-- Swypply Database Schema (PostgreSQL / Supabase)
-- Paste this into Supabase → SQL Editor → Run

CREATE TABLE IF NOT EXISTS users (
    id           BIGSERIAL PRIMARY KEY,
    name         VARCHAR(255)  NOT NULL,
    email        VARCHAR(255)  NOT NULL UNIQUE,
    password     VARCHAR(255)  NOT NULL,
    plan         VARCHAR(10)   NOT NULL DEFAULT 'free' CHECK (plan IN ('free','basic','pro')),
    ai_used      INTEGER       NOT NULL DEFAULT 0,
    ai_limit     INTEGER       NOT NULL DEFAULT 3,
    push_token                    VARCHAR(512),
    ai_reset_at                   TIMESTAMPTZ,
    email_verified_at             TIMESTAMPTZ,
    email_verification_code       CHAR(6),
    email_verification_expires_at TIMESTAMPTZ,
    subscription_expires_at       TIMESTAMPTZ,
    created_at                    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at                    TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS applications (
    id           BIGSERIAL PRIMARY KEY,
    user_id      BIGINT        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    job_title    VARCHAR(255)  NOT NULL,
    company      VARCHAR(255),
    job_url      TEXT,
    status       VARCHAR(20)   NOT NULL DEFAULT 'applied' CHECK (status IN ('applied','saved','rejected','interview')),
    created_at   TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_applications_user ON applications(user_id);

CREATE TABLE IF NOT EXISTS rate_limits (
    id_hash      CHAR(64)      PRIMARY KEY,
    attempts     INTEGER       NOT NULL DEFAULT 1,
    expires_at   INTEGER       NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_rate_limits_expires ON rate_limits(expires_at);

-- ── Seed: admin/pro account ───────────────────────────────────────────────────
-- Runs only if the account doesn't exist yet (safe to re-run)
INSERT INTO users (name, email, password, plan, ai_limit, email_verified_at, subscription_expires_at)
VALUES (
    'Frank',
    'fradela39@gmail.com',
    'PLACEHOLDER_SET_PASSWORD_VIA_REGISTER',
    'pro',
    9999,
    NOW(),
    NOW() + INTERVAL '365 days'
)
ON CONFLICT (email) DO UPDATE SET
    plan                    = 'pro',
    ai_limit                = 9999,
    email_verified_at       = COALESCE(users.email_verified_at, NOW()),
    subscription_expires_at = NOW() + INTERVAL '365 days';
