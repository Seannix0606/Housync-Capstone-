# Security

## Secrets and environment variables

- **Never commit** `.env` or any file containing API keys, Supabase keys, Firebase keys, or other secrets.
- Use `.env.example` as a template only; copy to `.env` locally and fill in real values. `.env` is gitignored.
- If any secret was ever committed or pushed:
  1. **Rotate/revoke** that key immediately in the provider (Supabase, Google Cloud, etc.).
  2. Create new keys and update your local `.env` only.
  3. Consider using [GitHub secret scanning](https://docs.github.com/en/code-security/secret-scanning) and revoking exposed secrets.

## Reporting a vulnerability

If you find a security issue, please report it responsibly (e.g. private disclosure to the maintainers) rather than opening a public issue.
