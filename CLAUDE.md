# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MTV.PJH.IS is a minimal PHP music blog for live performances. Static Markdown content, no database, no build tools.

## Development Commands

```bash
# Local development server
php -S localhost:8888 router.php

# Deploy via git-ftp (configured in .git/config)
git ftp push
```

## Architecture

**Routing:** Apache `.htaccess` in production, `router.php` for local dev server.

**Content:** Markdown files with YAML-style frontmatter in `htdocs/data/posts/`:
```markdown
Tags: Artist, Genre
Date: YYYY-MM-DD

# Post Title
Content with https://youtu.be/ID auto-embedded
```

**Draft convention:** Prefix filename with `_` to hide from public.

**Key files:**
- `htdocs/includes/functions.php` - Core utilities (post loading, slug generation, YouTube embed conversion)
- `htdocs/includes/config.php` - Site constants
- `htdocs/includes/Parsedown.php` - Markdown parser library

**Pages:** `index.php` (home), `post.php` (single), `archives.php`, `tagged.php`, `about.php`

## Skills

**MTV Post Creator** - Triggered by "post", "add a post", "new post" + YouTube URL:
1. Creates post markdown in `htdocs/data/posts/`
2. Commits and deploys via `git ftp push`
3. Archives video to `downloaded_videos/` with metadata

## Conventions

- Use `e($string)` helper for HTML escaping all output
- URL slugs are Unicode-safe via `mb_strtolower()` and regex normalization
- Post filenames: `YYYY-MM-DD-slug-title.md`
- Parsedown runs in unsafe mode (content is trusted)
