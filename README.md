# Sogda Tour CRM

Avtomatlashtirilgan tur boshqaruv tizimi. Laravel 13 (API) + Vue 3 + Vuetify 3 (frontend).

## Arxitektura

```
Internet (80/443)
    │
    ▼
 Nginx
    ├── /api/*   → PHP-FPM (Laravel)
    ├── /logo    → PHP-FPM (Laravel)
    └── /*       → Vue SPA (static)
        │
        ▼
     MySQL 8
```

---

## VPS ga deploy qilish

### Talablar

- Ubuntu 22.04 / 24.04 VPS
- Docker 24+ va Docker Compose v2
- `my.sogdatour.uz` domain DNS → VPS IP ga yo'naltirilgan

### 1. Docker o'rnatish (yangi VPS)

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker
```

### 2. Kodni yuklash

```bash
git clone <repo-url> /opt/sogdatour
cd /opt/sogdatour
```

Yoki serverga ZIP yuklash:

```bash
# Lokal kompyuterda:
scp -r /home/eversoft/Projects/my.sogdatour.uz user@VPS_IP:/opt/sogdatour
```

### 3. `.env` fayl yaratish

```bash
cd /opt/sogdatour
cp .env.example .env
nano .env
```

`.env` ichida to'ldiring:

```env
DB_DATABASE=sogda_tour
DB_USERNAME=sogda
DB_PASSWORD=KUCHLI_PAROL
DB_ROOT_PASSWORD=ROOT_PAROL

# Quyidagi buyruq bilan hosil qiling:
# docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
APP_KEY=base64:XXXX...
```

### 4. Build va ishga tushirish

```bash
docker compose up -d --build
```

Birinchi marta ~3-5 daqiqa ketadi (npm install + composer + migrations).

### 5. Super admin yaratish

```bash
docker exec -it sogda_app php artisan tinker
```

```php
\App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@sogdatour.uz',
    'password' => bcrypt('parolingiz'),
]);
// keyin rolni biriktiring (ilovangiz rollarига qarab)
```

### 6. HTTPS (Let's Encrypt)

```bash
# Certbot o'rnatish
sudo apt install certbot -y

# Sertifikat olish (nginx to'xtatilmaydi, standalone rejim)
docker compose stop nginx
sudo certbot certonly --standalone -d my.sogdatour.uz
docker compose start nginx
```

Keyin `docker/nginx/default.conf` ga HTTPS qo'shing:

```nginx
server {
    listen 80;
    server_name my.sogdatour.uz;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name my.sogdatour.uz;

    ssl_certificate     /etc/letsencrypt/live/my.sogdatour.uz/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/my.sogdatour.uz/privkey.pem;

    # ... qolgan config o'sha
}
```

`docker-compose.yml` da nginx servisiga volume qo'shing:

```yaml
  nginx:
    volumes:
      - /etc/letsencrypt:/etc/letsencrypt:ro
      - backend_storage:/var/www/backend/storage:ro
    ports:
      - "80:80"
      - "443:443"
```

Keyin rebuild:

```bash
docker compose up -d --build nginx
```

**Sertifikatni yangilash** (cron orqali):

```bash
sudo crontab -e
# Qo'shing:
0 3 * * * certbot renew --quiet && docker exec sogda_nginx nginx -s reload
```

---

## Kundalik operatsiyalar

### Loglarni ko'rish

```bash
docker compose logs -f app      # Laravel logs
docker compose logs -f nginx    # Nginx logs
docker compose logs -f mysql    # MySQL logs
```

### Migratsiya (yangi migration qo'shilganda)

```bash
docker exec sogda_app php artisan migrate --force
```

### Kodni yangilash (deploy)

```bash
git pull
docker compose up -d --build
```

### Barcha konteynerlarni to'xtatish

```bash
docker compose down
```

### Ma'lumotlar bazasiga kirish

```bash
docker exec -it sogda_mysql mysql -u sogda -p sogda_tour
```

### Laravel Artisan

```bash
docker exec -it sogda_app php artisan <buyruq>
```

---

## Loyiha tuzilishi

```
my.sogdatour.uz/
├── backend/          # Laravel 13 (PHP 8.3)
│   ├── app/
│   ├── routes/
│   │   └── api.php
│   └── public/
├── frontend/         # Vue 3 + Vuetify 3 + Pinia
│   └── src/
├── docker/
│   ├── php/
│   │   ├── Dockerfile    # PHP-FPM image
│   │   └── entrypoint.sh
│   └── nginx/
│       ├── Dockerfile    # Nginx + Vue build
│       └── default.conf
├── docker-compose.yml
├── .env.example
└── README.md
```

## Texnologiyalar

| Qatlam    | Texnologiya                      |
|-----------|----------------------------------|
| Backend   | Laravel 13, PHP 8.3, Sanctum     |
| Frontend  | Vue 3, Vuetify 3, Pinia, Vite    |
| Database  | MySQL 8.0                        |
| Server    | Nginx 1.27 + PHP-FPM             |
| Container | Docker + Docker Compose v2       |
