# Pelek Properties Website

A premium real estate and property management platform for Nairobi-based Pelek Properties agency.

## Tech Stack 🛠

- **Framework:** Laravel 12.x
- **Frontend:** 
  - Livewire/Volt for dynamic components
  - Flux for UI components
  - TailwindCSS for styling
- **Authentication:** Laravel built-in authentication
- **Authorization:** Spatie Permission for role management
- **Notifications:** SweetAlert2
- **Database:** MySQL

## Prerequisites 📋

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL

## Installation 🚀

1. Clone the repository
```bash
git clone [repository-url]
cd pelek-properties
```

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM packages
```bash
npm install
```

4. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

5. Set up database
```bash
php artisan migrate
php artisan db:seed
```

6. Build assets
```bash
npm run build
```

7. Start development server
```bash
php artisan serve
```

## Features 🌟

- Property Listings (Sales, Rentals, Airbnb)
- Advanced Property Search & Filters
- Booking System for Airbnb Properties
- Property Management Services
- Blog/News Section
- WhatsApp Integration
- Role-Based Access Control

### Property Management
- Comprehensive property listings with support for sales, rentals, and Airbnb properties
- Advanced image management system
  - Featured image support for all property types
  - Ordered image galleries for Airbnb listings
  - Automatic placeholder images
  - Efficient image storage and delivery through Laravel's public disk
- Featured properties showcase on homepage
- Property categorization and filtering

### Commercial Property Management
- Office space listings and management
- Retail unit management
- Industrial space solutions
- Commercial tenant screening
- Facility management services

### Property Management Services
- Comprehensive tenant management
- Maintenance coordination
- Financial reporting and accounting
- Marketing services
- Vendor relationship management

### Property Valuation Services
- Professional property valuations
- Market analysis reports
- Investment potential assessment
- Online valuation requests
- Detailed valuation reports

## Project Structure 📁

```
app/
├── Services/        # Business logic layer
├── Models/          # Eloquent models
└── View/
    └── Components/  # Reusable view components
resources/
├── views/
│   └── livewire/
│       ├── pages/              # Full page components
│       │   ├── properties/     # Property-related pages
│       │   ├── admin/         # Admin pages
│       │   └── settings/      # User settings pages
│       ├── components/        # Reusable components
│       │   ├── property/      # Property-related components
│       │   ├── forms/        # Form components
│       │   ├── ui/          # UI components
│       │   └── layout/      # Layout components
│       └── admin/           # Admin-specific components
│           ├── components/  # Admin reusable components
│           └── widgets/    # Admin dashboard widgets
├── css/
└── js/
```

## User Roles 👥

- **Administrator:** Full system access
- **Property Manager:** Property listing management
- **Content Editor:** Blog and content management
- **Guest:** Public access to listings and information

## Development Guidelines 📝

- Follow PSR-12 coding standards
- Use service layer pattern for business logic
- Organize Livewire components by feature and responsibility
- Keep components small and focused
- Use proper Volt syntax for all components
- Group related components in appropriate directories
- Maintain consistent naming conventions
- Implement proper validation and authorization
- Write tests for critical features

## Image Handling
The application uses Laravel's Storage facade for efficient image management:
- Images are stored in the public disk for optimal delivery
- Featured images are handled through a dedicated relationship
- Fallback to placeholder images when no image is available
- Special image ordering for Airbnb listings
- Support for multiple image formats

## Testing 🧪

Run tests using:
```bash
php artisan test
```

## Contributing 🤝

1. Create a feature branch
2. Commit your changes
3. Push to the branch
4. Create a Pull Request

## Project Status 📊

See [TRACKING.md](TRACKING.md) for detailed implementation progress.

## License 📄

[License details here]
