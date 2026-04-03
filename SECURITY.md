# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in Smart IoT Cloud, please report it responsibly.

**Do not open a public issue for security vulnerabilities.**

Instead, please email security concerns to the maintainers via the repository's [Security Advisories](https://github.com/UltraEmbeddedLab/smart-iot/security/advisories/new) page on GitHub.

### What to Include

- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Response Timeline

- **Acknowledgment**: Within 48 hours
- **Initial assessment**: Within 1 week
- **Fix and disclosure**: Coordinated with the reporter

## Supported Versions

| Version | Supported |
|---------|-----------|
| Latest  | Yes       |

## Security Best Practices

When deploying Smart IoT Cloud:

- Never commit `.env` files or API keys to version control
- Rotate MQTT broker credentials regularly
- Use TLS for MQTT connections (`MQTT_SCHEME=tls`)
- Keep `APP_DEBUG=false` in production
- Use strong `APP_KEY` values (generated via `php artisan key:generate`)
- Enable rate limiting on API endpoints
- Use HTTPS in production
