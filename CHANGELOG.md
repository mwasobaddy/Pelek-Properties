# Changelog

## Git Commit Messages

```
fix(image-upload): resolve image validation issues with Livewire temporary uploads

- Modified PropertyImageService to handle Livewire's TemporaryUploadedFile objects
- Skip isReadable check for Livewire temporary uploads which may fail but are still valid
- Fixed property creation/editing when uploading images
- Improved error handling for image uploads

Breaking: No
Related: #PropertyImageService #Livewire #FileUploads
Fixes: #IMAGE_FILE_NOT_READABLE
Date: 2025-05-21
```

```
fix(properties): resolve enum constraint violations in property creation

- Add proper handling for commercial_type enum field
- Add conditional validation rules based on listing type
- Add data cleaning for empty fields
- Add better error handling and logging
- Fix validation rules to match database constraints
- Add proper handling of JSON/array fields

Breaking: No
Related: #PropertyService #Database
Fixes: #CHECK_CONSTRAINT_COMMERCIAL_TYPE
```

```
fix(properties): enhance property creation error handling and debugging

- Add detailed error logging for property creation failures
- Add database transaction handling in Livewire component
- Add fallback for user_id when not authenticated
- Add success logging for creation and updates
- Add event dispatch for list refresh after save
- Fix image upload handling with proper featured image setting

Breaking: No
Related: #PropertyService #Database
```

```
fix(properties): resolve user_id constraint violation in property creation

- Add user_id to property creation data
- Improve error handling in save method
- Add proper transaction handling for property creation
- Ensure authenticated user is properly associated with new properties

Breaking: No
Related: #PropertyService #Authentication
Fixes: #NOT_NULL_CONSTRAINT_USER_ID
```

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

```
fix(properties): resolve image encoding issues and improve error handling

- Fix Intervention Image V3 encoding method usage
- Add better error handling for image uploads
- Add individual image upload error catching
- Add detailed logging for upload failures
- Improve data cleaning for form fields
- Add proper null handling for empty fields

Breaking: No
Related: #PropertyImageService #PropertyService
Fixes: #INTERVENTION_IMAGE_ENCODING_ERROR
```

```
fix(properties): fix DB facade namespace and optimize image processing in Livewire components

- Fix DB facade namespace with proper import
- Update image processing with proper error handling
- Fix temporary image data structure
- Add image file type and size validation
- Add retry mechanism for failed image uploads
- Add improved error messages for users

Breaking: No
Related: #PropertyImageService #Livewire
Fixes: #DB_NAMESPACE_ERROR #IMAGE_UPLOAD_ISSUES
```

```
fix(properties): resolve image processing and save issues with Intervention Image V3

- Fix Intervention Image integration for V3 compatibility
- Update image encoding method for thumbnails
- Add proper null handling for empty form fields
- Add improved error handling for image uploads
- Add transaction context with proper DB:: namespace
- Fix image file path and format handling

Breaking: No
Related: #PropertyImageService #PropertyService
Fixes: #INTERVENTION_IMAGE_ENCODING_ERROR
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

## [1.0.2] - 2025-05-21

### Fixed
- Fixed image encoding issues with Intervention Image V3
- Improved error handling for image uploads
- Added proper null handling for empty form fields
- Added detailed logging for upload failures
- Fixed commercial type handling for non-commercial properties

### Changed
- Enhanced error logging with more context
- Updated image upload error handling to continue on individual failures
- Improved form data cleaning process

### Technical Details
- Fixed encode() method usage for Intervention Image V3
- Added try-catch blocks for individual image uploads
- Added DB transaction handling improvements
- Enhanced logging for debugging purposes

## [1.0.3] - 2025-05-21

### Fixed
- Fixed Intervention Image V3 compatibility issues
- Fixed image encoding errors in PropertyImageService
- Fixed image file path handling for thumbnails
- Fixed database transaction handling with proper namespace
- Fixed form data cleaning for array fields
- Fixed DB facade usage with proper imports

### Added
- Added enhanced error handling with stack traces
- Added better logging for image processing errors
- Added individual try-catch blocks for image uploads
- Added proper handling for array form fields
- Added file existence checks before image processing
- Added image metadata for improved tracking
- Added image size and type validation

### Changed
- Updated PropertyImageService to use Intervention Image V3 API correctly
- Updated image resizing to maintain aspect ratio
- Updated error logging to include more context
- Updated form data processing to handle array fields properly

### Technical Details
- Updated thumbnail generation to use proper Intervention Image V3 methods
- Added ImageManager with GD driver for image processing
- Fixed toJpeg() usage instead of deprecated encode() method
- Added getRealPath() for proper file handling
- Enhanced transaction handling with DB namespace