1. Clone the repository:
   ```bash
   git clone https://github.com/BrantySis/AITAS-capstone.git

cp .env.example .env

composer install
npm install

php artisan key:generate

php artisan migrate

php artisan serve
