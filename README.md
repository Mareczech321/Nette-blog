## Project Overview

[Čestina](./README-czech.md)

This project is a modern PHP 8.3 web application built on the Nette Framework. The application acts as a social platform that allows users to publish posts, manage accounts, interact through comments and reactions, communicate via internal messaging, and purchase premium memberships.

The solution integrates both MariaDB/MySQL for relational data storage and Elasticsearch for advanced full-text search capabilities.

---

## Main Features

### User Management
- User registration
- Login and logout
- Profile management
- Password change
- Administrator role support
- Premium memberships

### Posts
- Create posts
- Edit posts
- Post detail pages
- Public post listing
- Pagination support

### Comments & Reactions
- Add comments
- Edit comments
- Post reactions
- Comment reactions
- Interaction tracking

### Search
- Elasticsearch full-text search
- Search by post title
- Fast content filtering

### Messaging
- Internal user messaging
- Communication between platform users

### Premium Memberships
- Premium plan selection
- Membership purchases
- Order management
- PDF document generation

### Administration
- Administrative dashboard
- Content management
- User management

---

## Technology Stack

### Backend
- PHP 8.3
- Nette Framework 3.x
- Latte Templates
- Nette Database
- Nette Security

### Database
- MariaDB 10.4
- MySQL-compatible schema

### Search Engine
- Elasticsearch 8.x

### Additional Libraries
- mPDF
- Tracy Debugger
- PHPStan

### DevOps
- Docker
- Docker Compose
- Apache

---

## Architecture

The project follows a layered architecture:

```text
Presenters
    ↓
Components
    ↓
Facades
    ↓
Repositories
    ↓
Database
```

Additional layers:

- DTOs
- Mappers
- Services
- Session management
- Elasticsearch integration

---

## Project Structure

```text
app/
├── Components/
├── Model/
│   ├── DTO/
│   ├── Repository/
│   ├── Mapper/
│   ├── Facade/
│   └── Auth/
├── Presenters/
├── Router/
└── Module/

config/
docker/
www/
log/
temp/
```

---

## Installation

### Requirements

- Docker
- Docker Compose
- PHP 8.3+
- Composer

### Run Application

```bash
make
make composer-install
```

or

```bash
docker compose -f docker/docker-compose.yml up -d
composer install
```

Application URL:

```text
http://localhost:9000
```

---

## Database

Included SQL scripts:

```text
db.sql
fix_emoji_charset.sql
```

Import the database after starting the MariaDB container.

---

## Elasticsearch

Default endpoint:

```text
http://localhost:9200
```

Used for full-text post searching.

---

## Static Analysis

Run PHPStan:

```bash
make ps
```

or

```bash
composer phpstan
```

---

## Security

The application uses:

- Nette Security
- Password hashing
- Access control
- Form protection
- Session management

---

## Future Improvements

- REST API
- OAuth authentication
- Notification system
- WebSocket realtime chat
- Media uploads
- Content moderation
- Automated testing
