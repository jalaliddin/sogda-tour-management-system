# Sogda Tour CRM

Avtomatlashtirilgan tur boshqaruv tizimi. Laravel 13 (API) + Vue 3 + Vuetify 3 (frontend).

## Arxitektura

```
Internet (80/443)
    │
    ▼
VPS Nginx (reverse proxy)          ← host nginx, certbot bilan HTTPS
    │  proxy_pass 127.0.0.1:8093
    ▼
Docker: sogda_nginx (:8093)        ← konteyner ichki Nginx
    ├── /api/*   → PHP-FPM (Laravel)
    ├── /logo    → PHP-FPM (Laravel)
    └── /*       → Vue SPA (static)
                      │
                      ▼
               sogda_mysql (MySQL 8)
```

VPSda bir nechta Docker ilovalar bo'lganda har biri o'z portida ishlaydi (`8091`, `8092`, `8093` ...).
Host Nginx esa domenlarga qarab to'g'ri portga yo'naltiradi.

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

> Docker ilovasi **8093** portida ishlaydi. Tashqaridan to'g'ridan-to'g'ri emas, VPS Nginx orqali kirish kerak (quyidagi 6-qadamga qarang).

### 5. Super admin yaratish

```bash
# 1. Foydalanuvchi yarating
docker exec sogda_app php artisan tinker --execute="\App\Models\User::create(['name'=>'Admin','email'=>'admin@sogdatour.uz','password'=>bcrypt('PAROLINGIZ')]);"

# 2. Rollar va ruxsatlarni seeder orqali yarating
docker exec sogda_app php artisan db:seed --class=RolesPermissionsSeeder --force

# 3. Super admin rolini biriktiring
docker exec sogda_app php artisan tinker --execute="\$u=\App\Models\User::where('email','admin@sogdatour.uz')->first();\$u->assignRole('super_admin');echo 'OK';"
```

### 6. VPS Nginx — Reverse Proxy + HTTPS

VPSda `nginx` va `certbot` o'rnatilgan bo'lishi kerak:

```bash
sudo apt install nginx certbot python3-certbot-nginx -y
```

**`/etc/nginx/sites-available/sogdatour`** faylini yarating:

```nginx
server {
    listen 80;
    server_name my.sogdatour.uz;

    location / {
        proxy_pass         http://127.0.0.1:8093;
        proxy_http_version 1.1;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 120s;
        client_max_body_size 50M;
    }
}
```

Faollashtiring va HTTPS sertifikat oling:

```bash
sudo ln -s /etc/nginx/sites-available/sogdatour /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Certbot avtomatik HTTPS qo'shadi
sudo certbot --nginx -d my.sogdatour.uz
```

Certbot `listen 443 ssl` bloklarini o'zi qo'shadi. Sertifikat 90 kunda yangilanadi (certbot timer avtomatik ishlaydi).

**Eslatma:** `docker-compose.yml` da port `8093:80` — tashqaridan faqat VPS Nginx orqali kirish mumkin, 8093 portiga to'g'ridan-to'g'ri kirish kerak emas.

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
