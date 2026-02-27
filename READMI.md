# Session Management System

Symfony + API Platform asosida qurilgan foydalanuvchi sessiyalarini boshqarish tizimi.

## Ishlatilgan texnologiyalar

- PHP 8.2
- Symfony 7.4
- API Platform
- Doctrine ORM
- MySQL
- LexikJWTAuthenticationBundle

## Loyiha haqida

Bu loyiha foydalanuvchilarning sessiyalarini boshqarish uchun yaratilgan. Foydalanuvchi tizimga kirganida sessiya yaratiladi. Sessiyalar orqali qaysi qurilmadan tizimga kirilganini kuzatib borish mumkin.

## Arxitektura

### Sessiya qanday yaratiladi?

Foydalanuvchi login qilganda `LoginSuccessEvent` ishga tushadi. `JwtAuthSubscriber` shu eventni tinglaydi va `SessionManager` servisini chaqiradi. `SessionManager` quyidagi ishlarni bajaradi:

1. Fingerprint asosida shu qurilmadan avval sessiya yaratilganmi tekshiradi
2. Agar sessiya mavjud bo'lsa — uni yangilaydi (yangi sessiya yaratmaydi)
3. Agar yo'q bo'lsa — aktiv sessiyalar sonini tekshiradi
4. 3 tadan ortiq bo'lsa — eng eski sessiyani o'chiradi
5. Yangi sessiya yaratadi

### Fingerprint nima?

Fingerprint — qurilmani aniqlaydigan noyob identifikator. U User-Agent va Accept-Language headerlaridan yaratiladi. Agar client X-Device-Fingerprint header yuborsa, u ishlatiladi.

### Xavfsizlik

- UUID ishlatiladi — ID ni taxmin qilib bo'lmaydi
- UNIQUE constraint — bir qurilmadan ikki aktiv sessiya bo'lmaydi
- Maximum 3 ta aktiv sessiya — 4-chi yaratilsa eng eskisi o'chiriladi
- Sessiya o'chirilganda darhol yaroqsiz bo'ladi

## API Endpointlar

### Ro'yxatdan o'tish
```
POST /api/register
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "123456"
}
```

### Login
```
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "123456"
}
```

### Sessiyalarni ko'rish
```
GET /api/me/sessions
Authorization: Bearer {token}
```

### Bitta sessiyani o'chirish
```
DELETE /api/me/sessions/{id}
Authorization: Bearer {token}
```

### Boshqa barcha sessiyalarni o'chirish
```
DELETE /api/me/sessions/others
Authorization: Bearer {token}
```

## Ishga tushirish

**1. Paketlarni o'rnatish:**
```bash
composer install
```

**2. .env faylini sozlang:**
```
DATABASE_URL="mysql://root:root@127.0.0.1:8889/session"
```

**3. JWT kalitlarini yaratish:**
```bash
php bin/console lexik:jwt:generate-keypair
```

**4. Migratsiyalarni bajarish:**
```bash
php bin/console doctrine:migrations:migrate
```

**5. Serverni ishga tushirish:**
```bash
symfony server:start
```

## Ma'lumotlar bazasi

### Jadvallar
- `user` — foydalanuvchilar
- `session` — sessiyalar

### Session jadvali indekslari
- `UNIQ_USER_FINGERPRINT` — bir qurilmadan faqat bitta aktiv sessiya bo'lishi uchun
