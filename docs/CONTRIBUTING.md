# Contributing to LombaBCF

Terima kasih atas minat Anda untuk berkontribusi pada project LombaBCF! 

## 🚀 Cara Berkontribusi

### 1. Fork Repository
1. Buka repository LombaBCF di GitHub
2. Klik tombol "Fork" di pojok kanan atas
3. Clone repository yang sudah di-fork ke local machine Anda

```bash
git clone https://github.com/YOUR_USERNAME/lombabcf.git
cd lombabcf
```

### 2. Setup Development Environment

#### Prerequisites
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Node.js 16.0 atau lebih tinggi
- Composer
- Git

#### Installation
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Setup database
mysql -u root -p < database.sql

# Configure database connection
cp config/database.php config/database.local.php
# Edit config/database.local.php sesuai konfigurasi database Anda

# Build frontend assets
npm run build
```

### 3. Buat Branch untuk Feature
```bash
git checkout -b feature/nama-feature-anda
```

### 4. Development Guidelines

#### Code Style
- **PHP**: Ikuti PSR-12 coding standards
- **JavaScript**: Ikuti ESLint configuration
- **CSS/SCSS**: Ikuti BEM methodology
- **HTML**: Gunakan semantic HTML5

#### Database Changes
- Buat migration file untuk perubahan database
- Update database.sql dengan perubahan struktur
- Test query performance

#### Testing
```bash
# Run PHP tests
composer test

# Run JavaScript tests
npm test

# Run linting
npm run lint
composer cs-check
```

### 5. Commit Changes
```bash
# Add changes
git add .

# Commit dengan message yang jelas
git commit -m "feat: add new payment method"

# Push ke repository Anda
git push origin feature/nama-feature-anda
```

### 6. Create Pull Request
1. Buka repository yang sudah di-fork
2. Klik "Compare & pull request"
3. Isi template PR dengan lengkap
4. Submit PR

## 📋 Template Pull Request

```markdown
## Description
Jelaskan perubahan yang Anda buat

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
- [ ] Tested on local environment
- [ ] All tests passing
- [ ] No linting errors

## Screenshots (if applicable)
Tambahkan screenshot jika ada perubahan UI

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review of code
- [ ] Commented code, particularly in hard-to-understand areas
- [ ] Corresponding changes to documentation
- [ ] No new warnings
- [ ] Added tests that prove fix is effective or feature works
```

## 🐛 Reporting Bugs

### Template Bug Report
```markdown
## Bug Description
Jelaskan bug yang ditemukan

## Steps to Reproduce
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Expected Behavior
Apa yang seharusnya terjadi

## Actual Behavior
Apa yang sebenarnya terjadi

## Environment
- OS: [e.g. Windows 10, macOS, Ubuntu]
- Browser: [e.g. Chrome, Firefox, Safari]
- PHP Version: [e.g. 7.4, 8.0]
- MySQL Version: [e.g. 5.7, 8.0]

## Screenshots
Tambahkan screenshot jika diperlukan

## Additional Context
Informasi tambahan yang relevan
```

## 💡 Suggesting Features

### Template Feature Request
```markdown
## Feature Description
Jelaskan fitur yang ingin ditambahkan

## Problem Statement
Masalah apa yang akan diselesaikan dengan fitur ini

## Proposed Solution
Bagaimana fitur ini akan bekerja

## Alternative Solutions
Solusi alternatif yang sudah dipertimbangkan

## Additional Context
Informasi tambahan yang relevan
```

## 🔧 Development Setup

### Local Development
```bash
# Start development server
php -S localhost:8000

# Watch for changes
npm run dev

# Build for production
npm run build
```

### Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE lombabcf;"

# Import structure
mysql -u root -p lombabcf < database.sql

# Import sample data (if available)
mysql -u root -p lombabcf < sample_data.sql
```

### Environment Variables
Buat file `.env` untuk konfigurasi local:
```env
DB_HOST=localhost
DB_NAME=lombabcf
DB_USER=root
DB_PASS=

APP_ENV=development
APP_DEBUG=true
```

## 📚 Documentation

### Code Documentation
- Gunakan PHPDoc untuk dokumentasi PHP
- Gunakan JSDoc untuk dokumentasi JavaScript
- Update README.md jika ada perubahan setup

### API Documentation
- Dokumentasikan semua API endpoints
- Gunakan format OpenAPI/Swagger
- Update dokumentasi saat ada perubahan API

## 🧪 Testing

### PHP Testing
```bash
# Run all tests
composer test

# Run specific test
vendor/bin/phpunit tests/Unit/DatabaseTest.php

# Generate coverage report
composer test-coverage
```

### JavaScript Testing
```bash
# Run all tests
npm test

# Run tests in watch mode
npm run test:watch

# Generate coverage report
npm run test:coverage
```

### Manual Testing
- Test semua fitur utama
- Test responsive design
- Test cross-browser compatibility
- Test payment flow

## 🔒 Security

### Security Guidelines
- Jangan commit credentials atau sensitive data
- Gunakan prepared statements untuk query database
- Validate dan sanitize semua input
- Implement proper authentication dan authorization
- Follow OWASP security guidelines

### Reporting Security Issues
Jika Anda menemukan security vulnerability:
1. **DON'T** create public issue
2. Email ke security@lombabcf.com
3. Tunggu response dari tim security
4. Jangan disclose vulnerability sampai fixed

## 📞 Getting Help

### Resources
- [Documentation](README.md)
- [Issues](https://github.com/lombabcf/lombabcf/issues)
- [Discussions](https://github.com/lombabcf/lombabcf/discussions)

### Contact
- Email: info@lombabcf.com
- Discord: [LombaBCF Community](https://discord.gg/lombabcf)

## 🎉 Recognition

Kontributor akan diakui dengan:
- Mention di README.md
- Badge kontributor
- Certificate of appreciation
- Special access untuk fitur beta

---

**Terima kasih telah berkontribusi untuk LombaBCF! 🎉**
