# Groundskeeper

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

2. **Configure environment variables**

   Edit `app/config/.env` with your credentials:
   ```
   GITHUB_CLIENT_ID=your_github_oauth_client_id
   GITHUB_CLIENT_SECRET=your_github_oauth_client_secret
   ENCRYPTION_KEY=your_base64_encryption_key
   OPENAI_API_KEY=your_openai_api_key
   ```

   **GitHub Personal Access Token Setup:**
   - Create a token at https://github.com/settings/tokens
   - Required scopes: `repo` (for private repos) or `public_repo` (for public repos only)
   - The app uses OAuth flow, so you'll need to create a GitHub OAuth App at https://github.com/settings/developers
   - Set Authorization callback URL to: `http://your-domain/oauth/callback`
   - Copy the Client ID and Client Secret to your `.env` file

   **Generate Encryption Key:**
   ```bash
   php -r "echo base64_encode(random_bytes(32));"
   ```

3. **Run the application**

   Using PHP's built-in server:
   ```bash
   cd public
   php -S localhost:8000
   ```

   Or configure with your preferred web server (Apache/Nginx).

4. **Access the application**

   Navigate to `http://localhost:8000` in your browser.

   The database and sessions table will be created automatically on first run.

## Usage

### First Time Setup

1. **Connect GitHub** - Click to authenticate with GitHub OAuth
2. **Add Repository** - Go to Settings and add your first repository (format: `owner/repo`)
3. **Run Initial Audit** - Click "Run Audit" to import all open bug issues
4. **Approve Areas** - Review and approve AI-discovered functional areas
5. **Analysis Complete** - View your dashboard with categorized insights

### Daily Workflow

1. **Select Repository** - Use the dropdown to switch between repositories
2. **Update & Re-analyze** - Click "Update issues and re-analyze" to sync latest changes
3. **Review Findings**:
   - Click each card (High Signal, Duplicates, etc.) to view detailed lists
   - Use bulk actions to copy URLs or open multiple issues in tabs
   - Filter by functional area to focus on specific parts of your codebase

### Dashboard Features

- **Total Issues** - Count of all open bugs synced from GitHub
- **Issue Cards** - Click to view detailed lists with AI analysis
- **Area Filtering** - Click any area in the right sidebar to filter all results
- **Bulk Actions** - Select multiple issues and copy URLs or open in browser tabs
- **Direct Links** - Every issue links back to GitHub for quick access

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

## Multi-Repository Support

Groundskeeper supports multiple repositories:
- Each repository maintains separate analysis results
- Switch between repos using the dropdown selector
- Bookmark specific repos with `?repo={id}` URL parameter
- Selection persists across sessions

## Technology Stack

- **Backend**: PHP 8 with custom lightweight framework (zero dependencies!)
- **Database**: SQLite (for data and sessions)
- **AI**: OpenAI only
  - GPT-4o-mini for area discovery and issue analysis
  - text-embedding-3-small for duplicate detection
- **Frontend**: Vanilla JavaScript, Tailwind-inspired CSS

## License

GPL 2 or greater
