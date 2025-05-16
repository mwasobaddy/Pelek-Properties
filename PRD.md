# Pelek Properties Website - Product Requirements Document

## 1. Introduction

### 1.1 Purpose
The Pelek Properties website will serve as the digital platform for this premium Nairobi-based real estate and property management agency, designed to:

- Reflect the company's premium brand identity
- Streamline client interactions for:
  - Rentals
  - Sales
  - Airbnb stays
- Drive bookings for furnished Airbnb properties
- Generate leads for property sales and management services
- Provide valuable real estate information and resources

### 1.2 Audience
**Primary stakeholders:**
- Pelek Properties management and staff
- Website development team (designers, developers, testers)

### 1.3 Scope
This document covers requirements for the initial website launch. Future enhancements are explicitly marked as out of scope.

## 2. Goals
**Primary objectives:**
- Increase brand visibility as a premium real estate agency
- Generate more inquiries for rental/sale properties
- Improve occupancy rates for Airbnb stays
- Attract new property management clients
- Deliver excellent user experience

## 3. Target Audience

| User Group | Needs |
|------------|-------|
| Prospective Tenants | Rental property search |
| Prospective Buyers | Property purchase options |
| Short-Stay Guests | Furnished Airbnb accommodations |
| Landlords & Investors | Property management services |
| General Visitors | Market insights, company info |
| Administrators | Website content management |
| Staff Users | Listing/blog content management |

## 4. Functional Requirements

### 4.1 Core Website Sections

#### Home Page
- Visually appealing property category highlights (Rentals, Sales, Airbnb)
- Featured listings carousel
- Clear CTAs: "Explore Rentals", "View Properties for Sale", "Book Your Stay"
- Company introduction
- Navigation to key sections

#### Properties for Sale
**Features:**
- Comprehensive property listings
- Advanced search/filter by:
  - Price range
  - Location (neighborhood/region)
  - Property type
  - Bedrooms/bathrooms
  - Amenities

**Listing components:**
- High-quality photos
- Detailed description
- Key details (price, size, rooms)
- Contact/inquiry options

#### Properties for Rent
(Same structure as Sales section with rental-specific filters)

#### Furnished Airbnb Stays
- Curated property selection
- Per-listing features:
  - High-res interior photos
  - Amenities list
  - Availability calendar
  - Instant booking inquiry
  - Clear pricing (nightly/weekly/monthly)

#### Commercial Spaces
- Listing features:
  - Office spaces
  - Retail units
  - Warehouses
  - Industrial spaces
- Search/filter by:
  - Type of space
  - Size (square footage)
  - Location
  - Price range
  - Available facilities

#### Property Management Services
Comprehensive service offerings:
- Tenant Management:
  - Screening and selection
  - Lease administration
  - Rent collection
  - Tenant communication
- Property Maintenance:
  - Regular inspections
  - Maintenance coordination
  - Emergency repairs
  - Vendor management
- Financial Services:
  - Rent collection
  - Financial reporting
  - Expense tracking
  - Budget management
- Marketing Services:
  - Property listing
  - Professional photography
  - Virtual tours
  - Marketing strategy

#### Property Valuation Services
- Service Types:
  - Market value assessment
  - Rental value assessment
  - Investment potential analysis
  - Development land valuation
- Features:
  - Professional valuation report
  - Market analysis
  - Comparable property analysis
  - Investment recommendations
  - Online booking system
  - Valuation request form

#### About Us
- Brand story
- Team introductions
- Mission/values statement

#### Blog/News
Content types:
- Renting guides
- Market insights
- Nairobi travel tips
- Company updates

#### Contact Page
- Inquiry form
- WhatsApp chat integration
- Social media links
- Physical location map

### 4.2 Dynamic Features

1. **Component Organization**
   - Clear separation of pages and components
   - Logical grouping by feature and responsibility
   - Reusable components in dedicated directories
   - Admin components separated from public components
   - Property-specific components grouped together
   - Form components isolated for reusability
   - UI components separated from business logic

2. **Smart Search & Filters**
   - Cross-category property search
   - Intuitive filtering interface
   - Pagination with:
     - Current page highlighting
     - Brand color integration
     - Dark mode support
     - Responsive design
     - Accessible navigation controls

3. **Booking System**
   - Airbnb availability calendars
   - Date selection for inquiries

4. **Promotional Tools**
   - Admin-controlled featured listings
   - Special offer highlighting

5. **Future: Payment Integration**
   - Booking invoices
   - M-Pesa/PayPal/card payments

6. **Live Communication**
   - WhatsApp chat integration
   - Real-time support options

7. **Content Management**
   - WordPress (or similar) CMS
   - Staff-friendly interface for:
     - Listing management
     - Blog content
     - General website updates

### 4.3 System Workflows

#### Guest Users
1. Visit homepage
2. Browse properties
3. Click "Book" → WhatsApp inquiry
   - No login required

#### Admin Users
1. Secure login
2. Access to:
   - All property management
   - Blog content
   - Staff permissions

#### Staff Users
- Role-based access:
  - Property management
  - Blog content
  - (As assigned by admin)

## 5. Non-Functional Requirements

| Category | Requirements |
|----------|--------------|
| Usability | Intuitive UI, responsive design |
| Performance | Fast loading, optimized media |
| Security | Data protection, secure logins |
| Scalability | Growth-ready architecture |
| Maintainability | Well-documented, easy CMS |
| Accessibility | WCAG compliance where possible |
| Code Organization | - Clear component hierarchy
                    - Logical feature grouping
                    - Consistent naming conventions
                    - Proper use of Volt syntax |

## 6. Release Criteria
The website is ready for launch when:

- All core sections are functional
- Search/filters work correctly
- Booking calendars operational
- CMS fully integrated
- Admin/staff access working
- Basic security implemented
- Responsive across devices
- Content populated
- Performance tested

## 7. Future Enhancements
*(Out of scope for initial release)*

- Direct online payments (M-Pesa integration)
- User accounts/favorites
- CRM integration
- Advanced analytics
- Multi-language support
- Virtual tours
- Automated email notifications

## Image Management Requirements

### Property Images
- Each property can have multiple images
- One image can be designated as the featured image
- Images are stored in the public disk for optimal delivery
- Image paths are stored relative to the storage disk

### Featured Images
- All property types support featured images
- Featured images are displayed prominently on the home page
- Default placeholder images are provided when no image is available

### Image Display Requirements
- Homepage featured properties must display their featured image
- Property cards must handle missing images gracefully
- Airbnb listings must display images in the specified order
- Image URLs must be properly generated using Storage::disk('public')->url()

### Image Storage Requirements
- Images must be stored in the public disk
- Image paths must be relative to maintain portability
- Placeholder images must be available for properties without images
- Support for multiple image formats (jpg, png, webp)