# OTU Project Documentation

## Project Architecture

```mermaid
graph TD
    A[Client/Browser] -->|HTTP Request| B[Laravel Application]
    B --> C[Routes]
    C --> D[Controllers]
    D --> E[Models]
    E -->|Query| F[(Database)]
    D --> G[Views]
    G --> H[Blade Templates]
    H -->|Response| A
```

## Project Structure

```
project-root/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Application controllers
│   │   ├── Middleware/     # HTTP middleware
│   │   └── Requests/       # Form requests
│   ├── Models/            # Eloquent models
│   └── Services/          # Business logic services
├── config/               # Configuration files
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/         # Database seeders
├── public/              # Publicly accessible files
├── resources/
│   ├── css/            # CSS files
│   ├── js/             # JavaScript files
│   └── views/          # Blade templates
├── routes/
│   ├── api.php         # API routes
│   └── web.php         # Web routes
└── tests/              # Application tests
```

## Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd <project-directory>
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Database**
   - Edit `.env` file with your database settings:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the Application**
   ```bash
   # Terminal 1: Start Laravel server
   php artisan serve

   # Terminal 2: Start Vite development server
   npm run dev
   ```

7. **Access the Application**
   - Open your browser and visit: `http://localhost:8000`
