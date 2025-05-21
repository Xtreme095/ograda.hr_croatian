# Ograda.hr - Croatian Website

This repository contains the Croatian version of Ograda.hr website, a platform for [brief description of what the website does].

## Project Structure

The repository is organized as follows:

- `ograda.hr/` - Main application for the Croatian version
- `si.ograda.hr/` - Slovenian version of the website (reference only)

## Setup Instructions

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for dependency management

### Local Development Setup

1. Clone this repository to your local environment
2. Configure your web server to point to the `ograda.hr` directory
3. Create a database for the application
4. Copy the configuration files:
   ```
   cp ograda.hr/application/config/config.sample.php ograda.hr/application/config/config.php
   cp ograda.hr/application/config/database.sample.php ograda.hr/application/config/database.php
   ```
5. Update the configuration files with your local settings
6. Install dependencies (if needed):
   ```
   cd ograda.hr
   composer install
   ```
7. Import the database schema (if available):
   ```
   mysql -u [username] -p [database_name] < schema.sql
   ```

## Contact

For questions or assistance, please contact [contact information].

## License

[Specify license information] 