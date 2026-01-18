# Groundskeeper

<img width="2934" height="1679" alt="image" src="https://github.com/user-attachments/assets/820e3949-d104-4f1f-9641-a421259766b3" />

**AI-powered GitHub issue triage tool** that helps teams manage large bug backlogs by automatically identifying high-priority issues, duplicates, cleanup candidates, and more.

## What It Does

Groundskeeper syncs your GitHub repository's bug issues and uses AI to analyze them:

- **High Signal Issues** - Identifies valuable, actionable issues worth prioritizing
- **Duplicate Detection** - Finds semantically similar issues using AI embeddings
- **Cleanup Candidates** - Flags stale or invalid issues that should be closed
- **Missing Info** - Detects issues lacking critical information (steps to reproduce, error logs, etc.)
- **Label Suggestions** - Recommends labels from your repository's existing label set
- **Area Categorization** - Auto-organizes issues into functional areas

## Requirements

- PHP 8.0+ (with SQLite support - usually enabled by default)
- GitHub Personal Access Token
- OpenAI API key

That's it! No additional dependencies needed.

## Installation

1. **Clone the repository**
   ```bash
   git clone git@github.com:davemart-in/groundskeeper.git
   cd groundskeeper
   ```

2. **Configure paths**

   Edit `app/config/define.php` and update these values to match your setup:

   ```php
   // Update ROOTPATH to your installation directory
   define('ROOTPATH', '/path/to/groundskeeper/');

   // Update BASEURL to your local development URL
   define('BASEURL', 'http://localhost:8000/');
   ```

   **Optional: Use a custom local domain**

   If you prefer using `groundskeeper.dev` instead of `localhost`, add this to your hosts file:

   ```bash
   # On macOS/Linux: /etc/hosts
   # On Windows: C:\Windows\System32\drivers\etc\hosts
   127.0.0.1 groundskeeper.dev
   ```

   Then update `BASEURL` accordingly:
   ```php
   define('BASEURL', 'http://groundskeeper.dev:8000/');
   ```

3. **Configure environment variables**

   **GitHub Personal Access Token Setup:**
   - Create a token at https://github.com/settings/tokens
   - Required scopes: `public_repo` (for public repos only)

   Create `app/config/.env` with your credentials:
   ```
   GITHUB_PERSONAL_ACCESS_TOKEN=your_github_personal_access_token
   OPENAI_API_KEY=your_openai_api_key
   ```

4. **Run the application**

   Using PHP's built-in server:
   ```bash
   cd public
   php -S localhost:8000
   ```

   Or configure with your preferred web server (Apache/Nginx).

5. **Access the application**

   Navigate to your configured `BASEURL` in your browser (e.g., `http://localhost:8000`).

   The database and sessions table will be created automatically on first run.

## Usage

### First Time Setup

1. **Add Repository** - Go to Settings and add your first repository (format: `owner/repo`)
2. **Run Initial Audit** - Click "Run Audit" button on the dashboard to import all open bug issues
3. **Approve Areas** - Review and approve AI-discovered functional areas
4. **Analysis Complete** - View your dashboard with categorized insights

### Daily Workflow

1. **Select Repository** - Use the dropdown to switch between repositories
2. **Update & Re-analyze** - Click "Update issues and re-analyze" to sync latest changes
3. **Review Findings**:
   - Click each card (High Signal, Duplicates, etc.) to view detailed lists
   - Use bulk actions to copy URLs or open multiple issues in tabs
   - Filter by functional area to focus on specific parts of your codebase


## How It Works

1. **Sync Phase** - Fetches open issues with your configured bug label from GitHub
2. **Area Discovery** - GPT-4o-mini analyzes issue titles/descriptions to suggest functional areas
3. **Analysis Phase** - Processes issues in batches (5 at a time) with GPT-4o-mini:
   - Categorizes by functional area
   - Flags high-signal issues (impact, urgency, engagement)
   - Identifies cleanup candidates
   - Detects missing information
   - Suggests relevant labels
4. **Duplicate Detection** - Uses OpenAI text-embedding-3-small to find semantically similar issues
5. **Results** - Stores analysis per repository for fast dashboard loading

## Technology Stack

- **Backend**: PHP 8 with custom lightweight framework (zero dependencies!)
- **Database**: SQLite (for data and sessions)
- **AI**: OpenAI only
  - GPT-4o-mini for area discovery and issue analysis
  - text-embedding-3-small for duplicate detection
- **Frontend**: HTML, Vanilla JavaScript, CSS

## License

GPL 2 or greater
