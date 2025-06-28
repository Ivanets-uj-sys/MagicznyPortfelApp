# MagicznyPortfellApp

MagicznyPortfellApp to aplikacja służąca do zarządzania osobistymi finansami, wspierająca operacje, portfele, tagi i kategorie. Projekt zawiera autoryzację użytkowników oraz panel administratora.

---

## 🔧 Instalacja

### 1. Sklonuj repozytorium
```bash
git clone <URL_DO_REPOZYTORIUM>
cd MagicznyPortfellApp
```

### 2. Skonfiguruj plik `.env.local`
Zawartość:
```dotenv
DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony
```

### 3. Zbuduj i uruchom kontenery
```bash
docker-compose build
docker-compose up -d
```

### 4. Przejdź do kontenera PHP
```bash
docker exec -it magicznyportfellapp-php bash
```

### 5. Zainicjalizuj aplikację (migracje + testowe dane)
```bash
composer init-app
```

> W `composer.json` komenda `init-app` wykonuje:
> - `doctrine:migrations:migrate`
> - `doctrine:fixtures:load`

---

## 📦 Wykorzystywane Bundles
- `symfony/maker-bundle`
- `doctrine/doctrine-fixtures-bundle`

---

## 🌐 Dostęp do strony

Aplikacja jest dostępna pod adresem:

```
http://localhost:8000/en/login
```

---

## 👥 Dane logowania

### Zwykły użytkownik:
- `user0@example.com` do `user9@example.com`
- hasło: `user1234` 

### Administrator:
- `admin0@example.com` do `admin2@example.com`
- hasło: `admin1234` 

---

## ✅ Funkcjonalności

- ✅ Zmiana języka interfejsu
- ✅ Rejestracja / Logowanie / Wylogowanie
- ✅ Panel administratora:
  - Edycja kont
  - Podgląd i usuwanie kont
- ✅ Zwykły użytkownik:
  - Edycja własnego konta
- ✅ Operacje finansowe:
  - Podgląd
  - Tworzenie (także przez portfel)
  - Edycja
- ✅ Wyświetlanie salda
- ✅ Kategorie:
  - Podgląd (dla użytkownika)
  - Tworzenie, edycja, usuwanie (dla admina)
  - Usuwanie tylko po wcześniejszym przepisaniu operacji do innych kategorii
- ✅ Tagi:
  - Podgląd i tagowanie operacji (dla użytkownika)
  - Tworzenie, edycja, usuwanie (dla admina)
- ✅ Filtrowanie operacji według kategorii i tagów

---

## 📝 Uwaga 

> Początkowo planowałam zrealizować projekt na ocenę 3. Jednak obawy, że mogę być na dolnej granicy, zmotywowały mnie do dalszej pracy. W efekcie rozwinęłam projekt do poziomu oceny 4.
