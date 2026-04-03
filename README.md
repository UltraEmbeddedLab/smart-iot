# Smart IoT Cloud

A modern IoT Cloud platform built with Laravel, demonstrating the capabilities of PHP for IoT applications.

## About

Smart IoT is an open-source platform that showcases how PHP and Laravel can be effectively used to build IoT cloud platforms. It provides a foundation for connecting, managing, and monitoring IoT devices with real-time data processing over MQTT.

### Key Features

- **Device Management** — Register, provision, and monitor IoT devices with automatic UUID and secret key generation
- **MQTT Communication** — Full MQTT v5.0 support with Last Will Testament, topic-based routing, and bidirectional data flow
- **Cloud Variables** — 17 IoT-specific types (temperature, humidity, voltage, etc.) that sync between device firmware and the cloud
- **Triggers & Automation** — Rule-based automation that watches cloud variables and fires actions (email, webhook) with configurable cooldowns
- **Customizable Dashboards** — Grid-based dashboards with widgets (Value, Switch, LED, Gauge, Slider, Chart) bound to cloud variables
- **Device Provisioning API** — Secure REST API for device onboarding with MQTT credential generation
- **Firmware Integration** — Auto-generated C++ variable declarations for embedding in device sketches

## Requirements

- PHP 8.4 or higher
- Composer
- Node.js 22+
- SQLite (default) or MySQL/PostgreSQL
- MQTT Broker (e.g., Mosquitto) for device communication

## Installation

1. Clone the repository:

```bash
git clone https://github.com/UltraEmbeddedLab/smart-iot.git
cd smart-iot
```

2. Install dependencies and set up the project:

```bash
composer setup
```

This will:
- Install PHP dependencies
- Create the `.env` file
- Generate the application key
- Run database migrations
- Install Node.js dependencies
- Build frontend assets

3. Start the development server:

```bash
composer dev
```

This starts Laravel server, queue worker, and Vite dev server concurrently.

If using [Laravel Herd](https://herd.laravel.com), the application is automatically available at `https://smart-iot.test`.

## API

All API routes are prefixed with `/api/v1`.

| Method | Endpoint              | Description                                                  |
|--------|-----------------------|--------------------------------------------------------------|
| `POST` | `/provision`          | Device provisioning (returns MQTT credentials and topic map) |
| `POST` | `/heartbeat`          | Device heartbeat (requires device auth)                      |
| `GET`  | `/config/{device_id}` | Fetch device configuration (requires device auth)            |

### Provisioning Flow

1. Create a device in the web UI — a UUID and secret key are generated (shown once)
2. The physical device calls `POST /api/v1/provision` with `device_id` + `secret_key`
3. On success, receives MQTT connection config, topic map, and cloud variable declarations
4. Device connects to the MQTT broker and begins data exchange

### MQTT Topics

| Topic                           | Direction       | Purpose                       |
|---------------------------------|-----------------|-------------------------------|
| `smartiot/{thingUuid}/data/out` | Device -> Cloud | Sensor data from device       |
| `smartiot/{thingUuid}/data/in`  | Cloud -> Device | Values pushed to device       |
| `smartiot/{deviceId}/cmd/down`  | Cloud -> Device | Commands to device            |
| `smartiot/{deviceId}/status`    | Device -> Cloud | Online/offline presence (LWT) |

## Development

### Running Tests

```bash
php artisan test
```

Or with Pest directly:

```bash
composer pest
```

### Code Quality

Run all quality checks (formatting, static analysis, tests):

```bash
composer check
```

Individual commands:

```bash
# Format code with Pint
composer fix

# Run static analysis with PHPStan
composer stan

# Run tests
composer pest
```

### MQTT Listener

```bash
php artisan mqtt:listen
```

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4+
- **Frontend**: Livewire 4, Flux UI, Tailwind CSS 4
- **Communication**: MQTT v5.0 (via php-iot)
- **Testing**: Pest 4, PHPUnit 12
- **Static Analysis**: Larastan (PHPStan for Laravel)
- **Code Style**: Laravel Pint
- **CI/CD**: GitHub Actions (tests, linter, static analysis, CodeQL)

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Credits

Developed by [UltraEmbeddedLab](https://github.com/UltraEmbeddedLab)
