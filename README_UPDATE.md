# Applying the update

1. Copy every file into your project, keeping the same paths (overwrites existing files).
2. Delete `app/Http/Controllers/ProductController.php` old logic is replaced by the new file.
3. Run:

    composer dump-autoload
    php artisan migrate
    php artisan db:seed          # creates test@example.com / password (local only)
    npm install
    npm run build                # or: npm run dev
    php artisan test

4. Optional: in config/app.php set 'timezone' => 'Asia/Kuala_Lumpur'.
5. Open http://127.0.0.1:8000/login and sign in.

If you do not want the demo user, create your own:

    php artisan tinker
    >>> App\Models\User::create(['name'=>'Admin','email'=>'you@example.com','password'=>'a-strong-password']);
