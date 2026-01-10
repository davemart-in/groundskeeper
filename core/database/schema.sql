-- Groundskeeper SQLite Schema
-- This file serves as reference for the database structure

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    github_id INTEGER UNIQUE,
    github_username TEXT NOT NULL,
    github_access_token TEXT,
    avatar_url TEXT,
    access_mode TEXT NOT NULL DEFAULT 'readonly',
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_github_id ON users(github_id);

CREATE TABLE IF NOT EXISTS repositories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    owner TEXT NOT NULL,
    name TEXT NOT NULL,
    full_name TEXT NOT NULL,
    bug_label TEXT DEFAULT 'type: bug',
    priority_labels TEXT,
    last_synced_at INTEGER,
    last_audited_at INTEGER,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id, owner, name)
);

CREATE TABLE IF NOT EXISTS issues (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    repository_id INTEGER NOT NULL,
    github_issue_id INTEGER NOT NULL,
    issue_number INTEGER NOT NULL,
    title TEXT NOT NULL,
    body TEXT,
    state TEXT NOT NULL,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL,
    closed_at INTEGER,
    author TEXT,
    labels TEXT,
    url TEXT,
    assignees TEXT,
    milestone TEXT,
    comments_count INTEGER DEFAULT 0,
    reactions_total INTEGER DEFAULT 0,
    is_locked INTEGER DEFAULT 0,
    label_colors TEXT,
    last_activity_at INTEGER,
    area_id INTEGER,
    is_high_signal INTEGER DEFAULT 0,
    is_cleanup_candidate INTEGER DEFAULT 0,
    is_missing_context INTEGER DEFAULT 0,
    missing_elements TEXT,
    is_missing_labels INTEGER DEFAULT 0,
    suggested_labels TEXT,
    summary TEXT,
    embedding TEXT,
    analyzed_at INTEGER,
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    UNIQUE(repository_id, github_issue_id)
);

CREATE INDEX IF NOT EXISTS idx_repository_id ON issues(repository_id);
CREATE INDEX IF NOT EXISTS idx_github_issue_id ON issues(github_issue_id);

CREATE TABLE IF NOT EXISTS areas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    repository_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    created_at INTEGER NOT NULL,
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
    UNIQUE(repository_id, name)
);

CREATE INDEX IF NOT EXISTS idx_area_repository_id ON areas(repository_id);

CREATE TABLE IF NOT EXISTS analysis_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    repository_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    total_issues INTEGER NOT NULL DEFAULT 0,
    processed_issues INTEGER NOT NULL DEFAULT 0,
    current_step TEXT,
    error_message TEXT,
    started_at INTEGER,
    completed_at INTEGER,
    created_at INTEGER NOT NULL,
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_analysis_job_repository ON analysis_jobs(repository_id);
CREATE INDEX IF NOT EXISTS idx_analysis_job_status ON analysis_jobs(status);
