# Payroll Management System

A comprehensive Laravel-based payroll management system with attendance tracking, employee management, and department organization.

## 🚀 Features

- **Employee Management**: Complete CRUD operations for employee records
- **Department Management**: Organize employees by departments
- **Attendance System**: 
  - Time In/Out functionality
  - Daily attendance tracking
  - Timekeeping records
  - Overtime management
  - Leave management
- **Real-time Clock**: Live time display with Philippine timezone
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **Role-based Access**: Admin and HR role management

## 🛠️ Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Blade Templates + Tailwind CSS v3
- **Database**: MySQL/PostgreSQL
- **Build Tool**: Vite
- **Icons**: FontAwesome 6

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/PostgreSQL
- Git

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd PayrollLaravel
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Configuration
Update your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payroll_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run Database Migrations
```bash
php artisan migrate
```

### 7. Build Assets
```bash
npm run build
```

### 8. Start Development Server
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server (for hot reloading)
npm run dev
```

## 🎨 Frontend Development

### Available NPM Scripts
```bash
npm run dev          # Start Vite dev server with hot reloading
npm run build        # Build for production
npm run watch        # Build and watch for changes
npm run hot          # Start dev server with host access
```

### Tailwind CSS Configuration
The project uses Tailwind CSS v3 with the following plugins:
- `@tailwindcss/forms` - Better form styling
- `@tailwindcss/typography` - Typography utilities

### Custom CSS Classes
- `.scrollbar-thin` - Custom scrollbar styling
- `.animate-float` - Floating animation
- Mobile-optimized touch targets and spacing

## 🗄️ Database Structure

### Key Tables
- `employees` - Employee information
- `departments` - Department data
- `attendance_records` - Daily attendance tracking
- `work_schedules` - Employee work schedules
- `overtime_requests` - Overtime management
- `leave_requests` - Leave management
- `leave_balances` - Leave balance tracking

## 🔐 Authentication & Roles

The system supports role-based access control:
- **Admin**: Full system access
- **HR**: Human resources management
- **Employee**: Basic attendance functions

## 📱 Responsive Design

- **Desktop**: Full-featured interface with tables
- **Mobile**: Card-based layout for better touch interaction
- **Tablet**: Optimized for medium screens

## 🚀 Deployment

### Production Build
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
