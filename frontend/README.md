# Travel Insurance Quotation Frontend

This is the Angular frontend for the travel insurance quotation application.

It allows users to register, sign in, create new travel insurance quotations, view previous quotations, and open each quotation to review its details.

## Features

- User registration
- User login
- JWT-based authentication
- Protected application routes
- Session storage for the authenticated user
- Create a new quotation
- Choose EUR, USD, or GBP
- Add one or more travelers
- Prevent submission with zero travelers
- Validate trip dates
- Validate traveler names and dates of birth
- Validate traveler age at the trip start date
- Display previous quotations
- Open a quotation by its public ID
- Loading, empty, and error states
- Responsive layout for desktop and mobile

## Technology

- Angular
- TypeScript
- Standalone components
- Reactive Forms
- Angular Router
- Angular HttpClient
- Signals
- SCSS
- Vitest

## Requirements

Before running the project, make sure the following are installed:

- Node.js 20.19 or later
- npm
- Angular CLI
- The Laravel backend running locally

## Installation

From the repository root:

```bash
cd frontend
npm install
```

## Development server

Start the Laravel backend first:

```bash
cd backend
php artisan serve
```

Then start Angular in a second terminal:

```bash
cd frontend
npm start
```

The application will normally be available at:

```text
http://localhost:4200
```

The frontend sends API requests through the Angular development proxy to:

```text
http://127.0.0.1:8000/api
```

## Development proxy

The project uses `proxy.conf.json` so the frontend can call `/api` without hardcoding the backend host in every service.

Example:

```json
{
  "/api": {
    "target": "http://127.0.0.1:8000",
    "secure": false,
    "changeOrigin": true
  }
}
```

The `start` script in `package.json` should include the proxy:

```json
"start": "ng serve --proxy-config proxy.conf.json"
```

## Application routes

Public routes:

```text
/login
/register
```

Authenticated routes:

```text
/quotes
/quotes/new
/quotes/:quotationId
```

## Authentication

After a successful login or registration, the backend returns a JWT access token.

The frontend stores the token and user information in `sessionStorage`.

Authenticated API requests include:

```http
Authorization: Bearer <token>
```

The authentication interceptor adds this header automatically.

The route guard prevents unauthenticated users from opening protected pages.

The guest guard prevents authenticated users from returning to the login or registration pages.

When the backend returns `401 Unauthorized`, the frontend clears the session and redirects the user to the login page.

## Main user flow

1. Register or sign in.
2. Open the quotation history page.
3. Create a new quotation.
4. Choose the payment currency.
5. Enter the trip start and end dates.
6. Add one or more travelers.
7. Submit the form.
8. Review the created quotation.
9. Return later to view previous quotations.

## Creating a quotation

The new quotation form includes:

- Payment currency
- Trip start date
- Trip end date
- Traveler full name
- Traveler date of birth

The form always starts with one traveler.

A quotation cannot be submitted when:

- There are no travelers
- A traveler name is missing
- A traveler date of birth is missing
- The traveler is under 18
- The traveler is over 70
- The end date is before the start date
- The currency is missing
- A request is already in progress

The backend remains the final source of truth for validation.

## API endpoints used by the frontend

### Register

```http
POST /api/auth/register
```

### Login

```http
POST /api/auth/login
```

### Current user

```http
GET /api/auth/me
```

### Create quotation

```http
POST /api/quotation
```

### List quotations

```http
GET /api/quotations
```

### View quotation details

```http
GET /api/quotations/{quotationId}
```

## Project structure

```text
src/app/
├── core/
│   ├── api/
│   ├── auth/
│   ├── guards/
│   ├── interceptors/
│   └── layout/
├── features/
│   ├── auth/
│   │   ├── login/
│   │   └── register/
│   └── quotations/
│       ├── quotation-create/
│       ├── quotation-detail/
│       └── quotation-list/
├── shared/
│   ├── models/
│   └── validators/
├── app.config.ts
├── app.routes.ts
└── app.ts
```

## Important files

### `core/api`

Contains services that communicate with the Laravel API.

Examples:

```text
auth-api.service.ts
quotation-api.service.ts
```

### `core/auth`

Contains authentication state and session storage logic.

### `core/guards`

Contains route guards for authenticated and guest users.

### `core/interceptors`

Contains the JWT interceptor.

### `features/auth`

Contains the login and registration pages.

### `features/quotations`

Contains the quotation history, quotation creation, and quotation detail pages.

### `shared/models`

Contains the TypeScript interfaces used by the frontend.

### `shared/validators`

Contains reusable form validation logic.

## Testing

Run the test suite:

```bash
npm test -- --run
```

Run tests in watch mode:

```bash
npm test
```

The tests should cover:

- Login validation
- Registration validation
- Authentication storage
- JWT interceptor
- Route guards
- Quotation form validation
- Traveler management
- Quotation API services
- Quotation list states
- Quotation detail states

## Production build

Create a production build:

```bash
npm run build
```

The compiled application will be placed in the Angular output directory under:

```text
dist/
```

## Code quality

Before committing frontend changes, run:

```bash
npm test -- --run
npm run build
```

A useful commit workflow is:

```bash
git add frontend
git commit -m "Describe the frontend change"
```

## Security notes

- Do not store passwords in the browser.
- Do not log JWT tokens.
- Do not trust client-side route guards as the main security boundary.
- The backend must verify that every quotation belongs to the authenticated user.
- Use HTTPS in production.
- Consider replacing browser storage with secure `HttpOnly` cookies for a production system.
- Keep Angular and npm dependencies up to date.
- Do not display raw server HTML.
- Do not expose internal numeric quotation IDs.

## Accessibility

The application should maintain:

- Visible form labels
- Keyboard navigation
- Visible focus states
- Error messages next to the related fields
- `aria-invalid` for invalid inputs
- Loading and error states announced to assistive technology
- Sufficient color contrast
- Responsive layouts for small screens

## Current status

The frontend currently supports:

- Registration
- Login
- Protected routes
- New quotation requests
- Quotation history
- Quotation details

The next steps are to continue improving automated tests, accessibility, and production deployment configuration.
