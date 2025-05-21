# Changelog

## Git Commit Message
```
feat(properties): add image management to property form

- Implement drag-and-drop image upload with previews
- Add featured image selection and management
- Add image deletion with confirmation modal
- Add support for multiple image uploads
- Add thumbnail generation and storage optimization
- Improve UX with loading states and feedback messages

Breaking: No
Related: #PropertyImage #PropertyService
```

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-05-21

### Added
- Image management functionality in property form modal
  - Added ability to upload multiple images with preview
  - Added ability to delete existing property images
  - Added ability to set featured image
  - Added drag and drop image upload support
  - Added image preview grid with hover actions
  - Added confirmation modal for image deletion
  - Added validation for image uploads (5MB max size)

### Changed
- Updated property form modal to include image management section
- Enhanced PropertyService to handle image uploads during property creation/update
- Improved UI/UX for image management with loading states and feedback

### Technical Details
- Added WithFileUploads trait to handle file uploads in Livewire component
- Implemented temporary image preview functionality
- Added proper image deletion with storage cleanup
- Added featured image management system
- Enhanced modals with proper state management
- Added support for both light and dark mode in image management UI