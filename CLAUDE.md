# Blog template

This is a shared template used by multiple sites.

## Dependent sites

When this template is updated, the following sites need to be synchronised:

- `/Users/ph/Documents/www/mtv.pjh.is`
- `/Users/ph/Documents/www/sun.pjh.is`

## Post-update workflow

After making changes to this template:

1. **Push to GitHub** - Commit and push the template changes
2. **Pull to dependent sites** - Pull the changes into each site listed above
3. **Deploy** - When the user agrees to deploy, run both `git push` AND `git ftp push` for each site
