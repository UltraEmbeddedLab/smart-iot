# Smart IoT Cloud

A modern IoT Cloud platform built with Laravel, demonstrating the capabilities of PHP for IoT applications.

## About

Smart IoT is an open-source project that showcases how PHP and Laravel can be effectively used to build IoT cloud platforms. It provides a foundation for connecting, managing, and monitoring IoT devices at scale.

This project demonstrates:

- **PHP for IoT**: Leveraging PHP's capabilities for real-time IoT data processing
- **Laravel Framework**: Modern, elegant PHP framework for robust backend development
- **Livewire**: Full-stack framework for dynamic interfaces without leaving PHP
- **Scalable Architecture**: Designed to handle multiple devices and data streams

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 22+
- SQLite (default) or MySQL/PostgreSQL

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

The application will be available at `http://localhost:8000`.

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

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4+
- **Frontend**: Livewire 4, Flux UI, Tailwind CSS 4
- **Testing**: Pest 4, PHPUnit 12
- **Static Analysis**: Larastan (PHPStan for Laravel)
- **Code Style**: Laravel Pint

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
