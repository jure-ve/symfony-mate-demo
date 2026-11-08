# Symfony Mate Demo

Proyecto de demostración y experimentación con **Symfony AI Mate** (`symfony/ai-mate`).

Este repositorio muestra cómo dotar a un agente de código (Claude Code, Cursor, Antigravity, etc.) de **herramientas de diagnóstico directas en tiempo de ejecución**: acceso al Web Profiler, consulta de logs estructurados de Monolog, inspección del contenedor de inyección de dependencias y creación de herramientas personalizadas con atributos PHP (`#[MateTool]`).

---

## ¿Qué es Symfony Mate?

Tradicionalmente, para entender el comportamiento en ejecución de una aplicación, un agente de código debe inferirlo leyendo el código fuente o ejecutando scripts temporales ad-hoc. 

**Symfony Mate** cambia ese enfoque:
- Proporciona una interfaz de línea de comandos (`vendor/bin/mate`) y un conjunto de **skills** estandarizadas para agentes.
- Devuelve métricas y datos medidos directamente del framework (consultas SQL ejecutadas, tiempos por evento, servicios registrados, variables de entorno validadas).
- Aplica ofuscación y protección de datos sensibles en la salida.
- Formatea la información para minimizar el consumo de tokens en el contexto del agente.

---

## Contenido de la demo

- **Caso práctico de diagnóstico (N+1 queries y errores):**
  - `GET /catalog/slow`: Carga categorías y recorre productos disparando múltiples consultas adicionales (problema N+1 clásico con 21 consultas SQL para 20 categorías).
  - `GET /catalog/fast`: Consulta optimizada con `leftJoin` e hidratación conjunta en una única consulta SQL.
  - `GET /catalog/boom`: Registra un error estructurado en Monolog (`sku: X-001`) y lanza una excepción (`500 Internal Server Error`) para pruebas de diagnóstico forense.
- **Comando de seed (`app:seed`):** Genera 20 categorías con 10 productos cada una (200 productos) para simular volumen realista.
- **Herramienta personalizada (`MateTool`):** En `mate/src/AppRoutes.php` se implementa la tool `app-routes` usando `#[MateTool]`, permitiendo al agente listar y filtrar rutas registradas en la aplicación de forma segura.
- **Instrucciones para agentes:** Configuraciones listas en `AGENTS.md`, `CLAUDE.md`, `.agents/skills/` y `.claude/skills/`.

---

## Herramientas disponibles en Mate (0.14)

Con las extensiones oficiales instaladas en este proyecto (`ai-mate`, `ai-symfony-mate-extension`, `ai-monolog-mate-extension`), el agente dispone de:

| Categoría | Herramientas |
| --- | --- |
| **Entorno y Sistema** | `server-info` |
| **Symfony Framework** | `symfony-services`, `symfony-service-detail`, `symfony-profiler-list`, `symfony-profiler-get`, `symfony-dotenv-check` |
| **Monolog** | `monolog-search`, `monolog-context-search`, `monolog-tail`, `monolog-list-files`, `monolog-list-channels` |
| **Personalizadas (App)** | `app-routes` (definida en `mate/src/AppRoutes.php`) |

---

## Requisitos

- **PHP** >= 8.4 (extensiones `ctype`, `iconv`, `pdo_sqlite`)
- **Composer** 2.x
- **Symfony CLI** (opcional, también puedes usar el servidor embebido de PHP)

---

## Instalación y puesta en marcha

### 1. Clonar el repositorio e instalar dependencias

```bash
git clone https://github.com/jure-ve/symfony-mate-demo.git
cd symfony-mate-demo
composer install
```

### 2. Ejecutar migraciones de base de datos (SQLite)

El proyecto utiliza por defecto una base de datos SQLite local (`var/data.db`) configurada en `.env`:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 3. Sembrar datos de prueba

```bash
php bin/console app:seed
```

### 4. Iniciar el servidor local

```bash
symfony serve -d
# O con el servidor embebido de PHP:
# php -S 127.0.0.1:8000 -t public
```

---

## Verificación de endpoints

Puedes comprobar el impacto del problema N+1 y la optimización:

- **Endpoint no optimizado:**
  ```bash
  curl http://127.0.0.1:8000/catalog/slow
  ```
  *(Provoca 21 consultas SQL registradas en el Profiler)*

- **Endpoint optimizado:**
  ```bash
  curl http://127.0.0.1:8000/catalog/fast
  ```
  *(Resuelve todo en 1 única consulta SQL)*

- **Endpoint con fallo simulado (500 + log en Monolog):**
  ```bash
  curl http://127.0.0.1:8000/catalog/boom
  ```
  *(Registra el error en `var/log/dev.log` y captura la excepción en el Profiler)*

---

## Uso de Symfony Mate CLI

Las herramientas se invocan siempre a través del binario del proyecto:

```bash
# Listar todas las herramientas disponibles
vendor/bin/mate tools:list

# Inspeccionar parámetros de una herramienta
vendor/bin/mate tools:inspect symfony-profiler-list

# Consultar los últimos perfiles generados por peticiones HTTP
vendor/bin/mate tools:call symfony-profiler-list --limit=5

# Inspeccionar los colectores de un perfil por su token (db, exception, time, etc.)
vendor/bin/mate resources:read symfony-profiler://profile/<token>
vendor/bin/mate resources:read symfony-profiler://profile/<token>/db

# Buscar errores en los logs de Monolog
vendor/bin/mate tools:call monolog-search --term="Fallo simulado" --level=ERROR

# Probar la herramienta personalizada de rutas
vendor/bin/mate tools:call app-routes --path=/catalog

# Ver información del entorno PHP y extensiones
vendor/bin/mate tools:call server-info
```

---

## Integración con agentes de código

Cuando un agente compatible (como Claude Code o Antigravity) trabaja en este repositorio:

1. Lee las instrucciones en [AGENTS.md](AGENTS.md) o [CLAUDE.md](CLAUDE.md).
2. Descubre las herramientas disponibles ejecutando `vendor/bin/mate tools:list`.
3. Emplea las skills disponibles en `.agents/skills/` (por ejemplo `mate-symfony-profiler-debugging`) para inspeccionar el Profiler y diagnosticar consultas lentas o errores 500 basándose en datos reales, sin adivinar.

---

## Licencia

Este proyecto está distribuido bajo la licencia [MIT](LICENSE).
