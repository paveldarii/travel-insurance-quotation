# Travel Insurance Quotation API

This repository contains the Laravel backend for a small travel insurance quotation service.

The API allows a user to register, sign in, and create a quotation for one or more travelers. The quotation is calculated from the trip dates, each traveler’s age at the start of the trip, the configured daily rate, and the requested currency.

## What the project includes

- JWT authentication
- User registration and login
- Protected quotation endpoint
- Traveler age calculation based on date of birth
- Inclusive trip-day calculation
- Age-based pricing rules
- EUR as the base currency
- EUR, USD, and GBP quotations
- Daily exchange-rate records
- Exchange-rate snapshots saved with each quotation
- Short public quotation IDs
- Unit and feature tests

## Requirements

Before running the project, make sure the following are installed:

- PHP 8.3 or later
- Composer
- SQLite, MySQL, or PostgreSQL
- OpenSSL PHP extension
- PDO PHP extension

## Installation

Clone the repository and open the backend directory:

```bash
git clone <repository-url>
cd travel-insurance-quotation/backend
```

Install the dependencies:

```bash
composer install
```

Create the local environment file:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Generate the JWT secret:

```bash
php artisan jwt:secret
```

For local SQLite development, create the database file:

```bash
touch database/database.sqlite
```

Update the database section in `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/backend/database/database.sqlite
```

Run the migrations and seed the development data:

```bash
php artisan migrate --seed
```

Start the application:

```bash
php artisan serve
```

The API will usually be available at:

```text
http://127.0.0.1:8000/api
```

## Environment configuration

Quotation pricing is configured through environment variables.

Example:

```env
QUOTATION_BASE_CURRENCY=EUR
QUOTATION_FIXED_DAILY_RATE_MINOR=300

QUOTATION_MINIMUM_AGE=18
QUOTATION_MAXIMUM_AGE=70

QUOTATION_AGE_LOAD_18_30=6000
QUOTATION_AGE_LOAD_31_40=7000
QUOTATION_AGE_LOAD_41_50=8000
QUOTATION_AGE_LOAD_51_60=9000
QUOTATION_AGE_LOAD_61_70=10000
```

The fixed daily rate is stored in minor currency units. A value of `300` represents `EUR 3.00`.

Age loads are stored in basis points:

|   Age | Basis points | Pricing factor |
| ----: | -----------: | -------------: |
| 18–30 |         6000 |           0.60 |
| 31–40 |         7000 |           0.70 |
| 41–50 |         8000 |           0.80 |
| 51–60 |         9000 |           0.90 |
| 61–70 |        10000 |           1.00 |

The `.env` file must not be committed to source control.

After changing configuration values, clear the Laravel cache:

```bash
php artisan optimize:clear
```

## Authentication

Protected routes require a JWT bearer token.

Include the following headers:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

## API endpoints

### Register

```http
POST /api/auth/register
```

Example request:

```json
{
    "name": "Pavel Darii",
    "email": "pavel@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
}
```

### Login

```http
POST /api/auth/login
```

Example request:

```json
{
    "email": "pavel@example.com",
    "password": "Password123!"
}
```

A successful response includes a JWT access token.

### Current user

```http
GET /api/auth/me
```

This endpoint requires authentication.

### Create a quotation

```http
POST /api/quotation
```

This endpoint requires authentication.

Example request:

```json
{
    "currency_id": "EUR",
    "start_date": "2026-10-01",
    "end_date": "2026-10-30",
    "travelers": [
        {
            "full_name": "Pavel Darii",
            "date_of_birth": "1998-05-10"
        },
        {
            "full_name": "Jane Darii",
            "date_of_birth": "1991-03-15"
        }
    ]
}
```

Example response:

```json
{
    "total": "117.00",
    "currency_id": "EUR",
    "quotation_id": "7K3M9Q2X",
    "base_total": "117.00",
    "base_currency_id": "EUR",
    "quoted_on": "2026-08-01",
    "start_date": "2026-10-01",
    "end_date": "2026-10-30",
    "trip_days": 30,
    "exchange_rate": {
        "base_currency_id": "EUR",
        "quote_currency_id": "EUR",
        "rate_date": "2026-08-01",
        "rate": "1.0000000000"
    },
    "travelers": [
        {
            "full_name": "Pavel Darii",
            "date_of_birth": "1998-05-10",
            "age_at_trip_start": 28,
            "subtotal": "54.00"
        },
        {
            "full_name": "Jane Darii",
            "date_of_birth": "1991-03-15",
            "age_at_trip_start": 35,
            "subtotal": "63.00"
        }
    ]
}
```

## How the quotation is calculated

The trip length includes both the start date and the end date.

The traveler’s age is calculated on the trip start date.

The base subtotal for each traveler is:

```text
daily rate × trip days × age factor
```

For a 30-day trip:

```text
Traveler age 28: 3.00 × 30 × 0.60 = 54.00
Traveler age 35: 3.00 × 30 × 0.70 = 63.00
Total: 117.00 EUR
```

Money is stored internally in minor units to avoid floating-point rounding problems.

## Currency conversion

EUR is the base currency.

Supported quotation currencies:

- EUR
- USD
- GBP

EUR quotations use a rate of `1.0000000000`.

USD and GBP quotations require a daily exchange rate for the quotation date. The exact rate used is copied into a quotation-specific snapshot. This keeps historical quotations unchanged even when exchange rates are updated later.

The included exchange-rate seeder is intended for local development only.

Run it with:

```bash
php artisan db:seed --class="Database\\Seeders\\CurrencyExchangeRateSeeder"
```

## Public quotation IDs

Each quotation has:

- An internal numeric database ID
- A separate public quotation ID

Example public ID:

```text
7K3M9Q2X
```

The public ID contains exactly eight uppercase letters and digits. It is protected by a database unique constraint and is returned by the API instead of the internal numeric ID.

## Testing

The project includes unit and feature tests for authentication, validation, pricing, currency conversion, persistence, and public quotation IDs.

Run all tests:

```bash
php artisan test
```

Run tests with readable test names:

```bash
php artisan test --testdox
```

Stop after the first failure:

```bash
php artisan test --stop-on-failure
```

Run only quotation-related tests:

```bash
php artisan test --filter=Quotation
```

## Code quality

Format the code:

```bash
./vendor/bin/pint
```

A useful check before committing is:

```bash
./vendor/bin/pint && php artisan test && composer audit
```

## Project structure

```text
app/
├── Data/
├── Domain/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
└── Services/

config/
└── quotation.php

database/
├── factories/
├── migrations/
└── seeders/

routes/
└── api.php

tests/
├── Feature/
└── Unit/
```

The code is separated so that request validation, business rules, calculation, currency conversion, and database persistence can be tested independently.

## Security notes

- Do not commit `.env`.
- Do not commit `APP_KEY` or `JWT_SECRET`.
- Set `APP_DEBUG=false` in production.
- Use HTTPS in production.
- Store production secrets in the hosting platform or a secrets manager.
- Use real exchange-rate data in production.
- Keep internal database IDs private.
- Restrict production CORS settings.
- Avoid logging passwords, tokens, or sensitive personal data.

## License

This project was created as part of a software engineering coding challenge.
