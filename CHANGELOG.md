# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.1] - 2025-05-21

### Fixed
- Blog admin page error with pagination by converting getPosts() method to a computed property following Laravel 12.14.1 and Livewire 3 best practices.

### Commit Message
```
fix: resolve blog admin pagination error

Fixed the blog admin pagination issue by converting the getPosts() method 
to a computed property posts() in the BlogIndex component. This resolves 
the error "Too few arguments to function getPosts()" by following Laravel 12.14.1 
and Livewire 3 best practices for computed properties.
```

## [0.1.0] - 2025-05-20

### Added
- Initial release of Pelek Properties platform with property management features
- Basic blog management functionality for administrators
- User authentication with role-based permissions using Spatie