# BIBLION - Book Management API (needs some refactors)

BIBLION is a RESTful API for managing books and bookshelves. It allows users to create and manage their book collections, organize books into shelves, and share their reading lists with others.

## Features

- User authentication using Laravel Sanctum
- Book management (CRUD operations)
- Bookshelf management
- Public and private bookshelves
- Search functionality for books
- Soft deletes for data integrity

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Laravel 11.0

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/biblion.git
cd biblion
```

2. Install dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biblion
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run migrations and seed the database:
```bash
php artisan migrate --seed
```

## API Documentation

### Authentication Endpoints

#### Register a new user
```
POST /api/v1/register
{
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
}
```

#### Login
```
POST /api/v1/login
{
    "email": "string",
    "password": "string"
}
```

#### Logout
```
POST /api/v1/logout
Header: Authorization: Bearer {token}
```

### Book Endpoints

#### List all books
```
GET /api/v1/books
Optional Query Parameters:
- search: Search term for title, author, or ISBN
- page: Page number for pagination
```

#### Get a specific book
```
GET /api/v1/books/{id}
```

#### Create a new book
```
POST /api/v1/books
Header: Authorization: Bearer {token}
{
    "title": "string",
    "author": "string",
    "isbn": "string",
    "description": "string",
    "publication_year": integer,
    "publisher": "string",
    "language": "string",
    "page_count": integer,
    "cover_image": "string"
}
```

#### Update a book
```
PUT /api/v1/books/{id}
Header: Authorization: Bearer {token}
{
    "title": "string",
    "author": "string",
    ...
}
```

#### Delete a book
```
DELETE /api/v1/books/{id}
Header: Authorization: Bearer {token}
```

### Shelf Endpoints

#### List user's shelves
```
GET /api/v1/shelves
Header: Authorization: Bearer {token}
```

#### Create a new shelf
```
POST /api/v1/shelves
Header: Authorization: Bearer {token}
{
    "name": "string",
    "description": "string",
    "is_public": boolean
}
```

#### Get a specific shelf
```
GET /api/v1/shelves/{id}
Header: Authorization: Bearer {token}
```

#### Update a shelf
```
PUT /api/v1/shelves/{id}
Header: Authorization: Bearer {token}
{
    "name": "string",
    "description": "string",
    "is_public": boolean
}
```

#### Delete a shelf
```
DELETE /api/v1/shelves/{id}
Header: Authorization: Bearer {token}
```

#### Add a book to a shelf
```
POST /api/v1/shelves/{shelf_id}/books
Header: Authorization: Bearer {token}
{
    "book_id": integer
}
```

#### Remove a book from a shelf
```
DELETE /api/v1/shelves/{shelf_id}/books/{book_id}
Header: Authorization: Bearer {token}
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Development Setup

For local development:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api/v1/`

## Test User

After seeding the database, you can use these credentials to test the API:
- Email: test@example.com
- Password: password

## License

This project is open-sourced software licensed under the MIT license.
