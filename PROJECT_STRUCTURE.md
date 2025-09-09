# Project Structure - LombaBCF

Berikut adalah struktur lengkap dari project LombaBCF:

```
lombabcf/
├── 📁 bootstrap-5.0.2-dist/          # Bootstrap CSS & JS files
│   ├── 📁 css/
│   │   ├── bootstrap.css
│   │   ├── bootstrap.min.css
│   │   ├── bootstrap-grid.css
│   │   ├── bootstrap-reboot.css
│   │   └── bootstrap-utilities.css
│   └── 📁 js/
│       ├── bootstrap.bundle.js
│       ├── bootstrap.bundle.min.js
│       ├── bootstrap.js
│       └── bootstrap.min.js
│
├── 📁 config/                        # Configuration files
│   └── database.php                  # Database configuration
│
├── 📁 uploads/                       # File upload directory
│   └── index.html                    # Prevent directory browsing
│
├── 📄 index.php                      # Main homepage
├── 📄 daftar.php                     # Registration page
├── 📄 payment.php                    # Payment page
├── 📄 sukses.php                     # Success page

├── 📄 database.sql                   # Database structure
│
├── 📄 .htaccess                      # Apache configuration
├── 📄 .gitignore                     # Git ignore rules
├── 📄 .babelrc                       # Babel configuration
├── 📄 .eslintrc.js                   # ESLint configuration
├── 📄 jest.config.js                 # Jest configuration
├── 📄 webpack.config.js              # Webpack configuration
├── 📄 composer.json                  # PHP dependencies
├── 📄 package.json                   # Node.js dependencies
│
├── 📄 README.md                      # Main documentation
├── 📄 CHANGELOG.md                   # Version history
├── 📄 CONTRIBUTING.md                # Contribution guidelines
├── 📄 SECURITY.md                    # Security policy
├── 📄 LICENSE                        # MIT License
└── 📄 PROJECT_STRUCTURE.md           # This file
```

## 📋 File Descriptions

### 🌐 Core PHP Files

| File | Description | Size |
|------|-------------|------|
| `index.php` | Main homepage with hero section, lomba categories, timeline, and prizes | 17KB |
| `daftar.php` | Registration form with validation and progress indicator | 16KB |
| `payment.php` | Payment page with multiple payment methods | 9.4KB |
| `sukses.php` | Success page with confetti animation and confirmation details | 11KB |


### 🗄️ Database & Configuration

| File | Description | Size |
|------|-------------|------|
| `database.sql` | Complete database structure with tables and sample data | 4.2KB |
| `config/database.php` | Database connection and helper functions | - |

### 🎨 Frontend Assets

| Directory | Description |
|-----------|-------------|
| `bootstrap-5.0.2-dist/` | Bootstrap 5 CSS and JavaScript files |
| `uploads/` | File upload directory with security protection |

### ⚙️ Configuration Files

| File | Description |
|------|-------------|
| `.htaccess` | Apache configuration for security and optimization |
| `.gitignore` | Git ignore rules for sensitive files |
| `.babelrc` | Babel configuration for JavaScript transpilation |
| `.eslintrc.js` | ESLint configuration for code quality |
| `jest.config.js` | Jest configuration for testing |
| `webpack.config.js` | Webpack configuration for asset bundling |

### 📦 Dependency Management

| File | Description |
|------|-------------|
| `composer.json` | PHP dependencies and autoloading configuration |
| `package.json` | Node.js dependencies and build scripts |

### 📚 Documentation

| File | Description |
|------|-------------|
| `README.md` | Main project documentation |
| `CHANGELOG.md` | Version history and changes |
| `CONTRIBUTING.md` | Guidelines for contributors |
| `SECURITY.md` | Security policy and vulnerability reporting |
| `LICENSE` | MIT License |
| `PROJECT_STRUCTURE.md` | This file - project structure overview |

## 🗂️ Database Schema

### Tables Overview

| Table | Purpose | Records |
|-------|---------|---------|
| `kategori_lomba` | Lomba categories and pricing | 3 |
| `pendaftar` | Registration data | Dynamic |
| `pembayaran` | Payment information | Dynamic |
| `karya` | Submitted works | Dynamic |
| `admin` | Admin users and roles | 1 |
| `pengaturan` | System settings | 10 |

### Key Relationships

```sql
pendaftar.kategori_lomba_id → kategori_lomba.id
pembayaran.pendaftar_id → pendaftar.id
karya.pendaftar_id → pendaftar.id
```

## 🎯 Features by File

### Homepage (`index.php`)
- ✅ Hero section with animated background
- ✅ Lomba categories with pricing
- ✅ Timeline of events
- ✅ Prize information
- ✅ Responsive navigation
- ✅ Smooth scrolling
- ✅ Interactive animations

### Registration (`daftar.php`)
- ✅ Multi-step form with progress indicator
- ✅ Real-time validation
- ✅ Form animations
- ✅ Data validation and sanitization
- ✅ Session management
- ✅ Error handling

### Payment (`payment.php`)
- ✅ Multiple payment methods
- ✅ Payment instructions
- ✅ QR code support
- ✅ Copy-to-clipboard functionality
- ✅ Payment status tracking
- ✅ Responsive design

### Success (`sukses.php`)
- ✅ Confetti animation
- ✅ Success confirmation
- ✅ Registration details
- ✅ Payment information
- ✅ Next steps guidance
- ✅ Print functionality

## 🔧 Technical Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **PDO** - Database abstraction layer
- **Sessions** - User state management

### Frontend
- **Bootstrap 5** - CSS framework
- **Animate.css** - Animation library
- **Font Awesome** - Icon library
- **Vanilla JavaScript** - Client-side scripting

### Build Tools
- **Webpack** - Asset bundling
- **Babel** - JavaScript transpilation
- **ESLint** - Code linting
- **Jest** - Testing framework
- **Sass** - CSS preprocessing

### Development Tools
- **Composer** - PHP dependency management
- **npm** - Node.js package management
- **Git** - Version control

## 🚀 Performance Features

### Optimization
- ✅ Gzip compression
- ✅ Browser caching
- ✅ Minified assets
- ✅ Optimized images
- ✅ Database indexing
- ✅ CDN integration ready

### Security
- ✅ SQL injection protection
- ✅ XSS protection
- ✅ CSRF protection
- ✅ Input validation
- ✅ File upload security
- ✅ Security headers

## 📱 Responsive Design

### Breakpoints
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: < 768px

### Features
- ✅ Mobile-first approach
- ✅ Touch-friendly interface
- ✅ Optimized for all screen sizes
- ✅ Fast loading on mobile

## 🎨 Design System

### Colors
- **Primary**: #667eea (Bluvocation)
- **Secondary**: #1630df (Purple)
- **Success**: #28a745 (Green)
- **Warning**: #ffc107 (Yellow)
- **Danger**: #dc3545 (Red)

### Typography
- **Font Family**: Bootstrap default (system fonts)
- **Headings**: Bold weights
- **Body**: Regular weights
- **Icons**: Font Awesome

### Animations
- **Fade In**: Elements appear smoothly
- **Slide Up**: Content slides from bottom
- **Pulse**: Interactive elements
- **Hover Effects**: Button and card interactions
- **Confetti**: Success celebration

## 🔄 Development Workflow

### Setup
1. Clone repository
2. Install dependencies (`composer install`, `npm install`)
3. Setup database (`mysql -u root -p < database.sql`)
4. Configure database connection
5. Start development server

### Development
1. Make changes to source files
2. Run build process (`npm run build`)
3. Test functionality
4. Commit changes with descriptive messages

### Deployment
1. Build production assets (`npm run build`)
2. Upload files to server
3. Import database structure
4. Configure web server
5. Test all functionality

## 📊 File Statistics

### Total Files: 25
### Total Size: ~70KB
### Lines of Code: ~2,500
### Languages: PHP, JavaScript, CSS, HTML, SQL

## 🎯 Target Audience

- **Primary**: Students SMP (ages 12-15)
- **Secondary**: Teachers and parents
- **Tertiary**: Event organizers and administrators

## 🌟 Key Features

1. **Interactive Design** - Engaging for young users
2. **Simple Navigation** - Easy to use interface
3. **Mobile Friendly** - Works on all devices
4. **Secure** - Protected against common attacks
5. **Fast** - Optimized for performance
6. **Accessible** - Usable by everyone

---

**Project created with ❤️ for Indonesian SMP students**
