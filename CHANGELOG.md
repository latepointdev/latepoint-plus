# Changelog

All notable changes to LatePoint+ will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-26

### Added
- **Bulk Services Module**: Add multiple services to orders at once
  - Bulk selection interface with category filtering
  - Seamless integration into order edit screens
  - Smart show/hide functionality
- **Quick Status Module**: Toggle service status from services list
  - One-click enable/disable buttons
  - AJAX-powered instant updates
  - Visual feedback with color-coded states
- **Quick Button Module**: Fast access to new item forms
  - Context-aware "New Item" buttons on page headers
  - Support for all major LatePoint pages
  - Full LatePoint Pro feature support
- Translation ready with text domain support
- Modular architecture for easy maintenance
- Full LatePoint integration using official hooks

### Technical
- Follows LatePoint addon starter template patterns
- Uses LatePoint's router system for proper addon registration
- Optimized asset loading (CSS/JS only when needed)
- Clean, production-ready code (no debug logging)
- Responsive design for mobile and desktop

### Requirements
- WordPress 5.0+
- PHP 7.4+
- LatePoint (latest version)

[1.0.0]: https://github.com/latepointdev/latepoint-plus/releases/tag/v1.0.0
