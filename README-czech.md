# Social Network Platform (Nette Framework)

---

## Přehled projektu

Tento projekt představuje moderní webovou aplikaci vyvinutou v PHP 8.3 s využitím frameworku Nette. Aplikace funguje jako sociální platforma umožňující publikování příspěvků, správu uživatelských účtů, komentáře, reakce, interní komunikaci a správu prémiových členství.

Součástí řešení je integrace relační databáze MariaDB/MySQL a fulltextového vyhledávání pomocí Elasticsearch.

---

## Hlavní funkcionality

### Správa uživatelů
- Registrace nových uživatelů
- Přihlášení a odhlášení
- Správa uživatelského profilu
- Změna hesla
- Role administrátora
- Prémiové členství

### Příspěvky
- Vytváření příspěvků
- Editace příspěvků
- Detail příspěvku
- Veřejný přehled příspěvků
- Stránkování výsledků

### Komentáře a reakce
- Přidávání komentářů
- Editace komentářů
- Reakce na příspěvky
- Reakce na komentáře
- Sledování počtu interakcí

### Vyhledávání
- Fulltextové vyhledávání pomocí Elasticsearch
- Vyhledávání podle názvu příspěvku
- Rychlé filtrování obsahu

### Chat a komunikace
- Interní zasílání zpráv
- Uživatelská komunikace v rámci aplikace

### Prémiové členství
- Výběr prémiových plánů
- Nákup členství
- Evidence objednávek
- Generování PDF dokumentů

### Administrace
- Administrátorské rozhraní
- Správa obsahu
- Správa uživatelů

---

## Technologie

### Backend
- PHP 8.3
- Nette Framework 3.x
- Latte Templates
- Nette Database
- Nette Security

### Databáze
- MariaDB 10.4
- MySQL kompatibilní schéma

### Vyhledávání
- Elasticsearch 8.x

### Další knihovny
- mPDF
- Tracy Debugger
- PHPStan

### DevOps
- Docker
- Docker Compose
- Apache

---

## Architektura projektu

Projekt využívá vícevrstvou architekturu:

```text
Presenters
    ↓
Components
    ↓
Facades
    ↓
Repositories
    ↓
Database
```

Další vrstvy:

- DTO objekty
- Mappery
- Služby
- Session management
- Elasticsearch integrace

---

## Struktura projektu

```text
app/
├── Components/
├── Model/
│   ├── DTO/
│   ├── Repository/
│   ├── Mapper/
│   ├── Facade/
│   └── Auth/
├── Presenters/
├── Router/
└── Module/

config/
docker/
www/
log/
temp/
```

---

## Instalace

### Požadavky

- Docker
- Docker Compose
- PHP 8.3+
- Composer

### Spuštění

```bash
make
make composer-install
```

nebo:

```bash
docker compose -f docker/docker-compose.yml up -d
composer install
```

Aplikace bude dostupná na:

```text
http://localhost:9000
```

---

## Databáze

Projekt obsahuje SQL skripty:

```text
db.sql
fix_emoji_charset.sql
```

Po spuštění databázového kontejneru lze databázi importovat standardním SQL importem.

---

## Elasticsearch

Výchozí konfigurace:

```text
http://localhost:9200
```

Používá se pro fulltextové vyhledávání příspěvků.

---

## Statická analýza

Spuštění PHPStan:

```bash
make ps
```

nebo

```bash
composer phpstan
```

---

## Bezpečnost

Projekt využívá:

- Nette Security
- Hashování hesel
- Řízení přístupových práv
- Ochranu formulářů
- Session management

---

## Možná rozšíření

- REST API
- OAuth přihlášení
- Notifikační systém
- Realtime chat (WebSocket)
- Upload médií
- Moderace obsahu
- Automatické testování