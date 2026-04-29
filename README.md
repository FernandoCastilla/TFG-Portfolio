# UGR Portfolio

Aplicación web de gestión y monitorización de Work Packages sobre la API de OpenProject del Vicerrectorado de Transformación Digital de la Universidad de Granada.

Desarrollada como Trabajo de Fin de Grado — Grado en Ingeniería Informática, ETSIIT, Universidad de Granada.

**Autor:** Fernando Castilla
**Tutor:** Manuel Nohuera 
**Curso:** 2025–2026

---

## ¿Qué hace?

UGR Portfolio consume la API REST de la instancia institucional de OpenProject (`proyectostic.ugr.es`) y presenta sus datos en una interfaz centralizada adaptada al perfil de seguimiento del Vicerrectorado:

- **Dashboard** con KPIs globales, gráfica de distribución de estados, actividad reciente y panel lateral interactivo
- **Listado de proyectos** con filtros y jerarquía padre-hijo
- **Work Packages** con filtros multidimensionales, jerarquía expandible, tiempos estimado/dedicado, paginación por familias y semáforo de salud por proyecto
- **Calendario** interactivo con colores por tipo de estado UGR (H\_, S\_, T\_) y leyenda
- **Exportación a PDF** del listado de tareas de cada proyecto
- **Buscador global** de proyectos y tareas
- **Historial de sincronizaciones** con métricas de rendimiento (solo para usuarios autenticados)
- **Modo oscuro** completo con preferencia persistente
- **Autenticación** de usuarios con registro y login

---

## Requisitos previos

| Herramienta | Versión mínima | Notas |
|---|---|---|
| [Docker](https://www.docker.com/) | 24.x | Con Docker Compose integrado |
| [PHP](https://www.php.net/) | 8.2 | Solo para instalar Composer |
| [Composer](https://getcomposer.org/) | 2.x | Gestión de dependencias PHP |
| VPN UGR | — | Cisco AnyConnect, para sincronizar con la API |

> **Nota:** PHP y Composer solo son necesarios para la instalación inicial. Una vez levantado el entorno Docker, todos los comandos se ejecutan dentro del contenedor con `./vendor/bin/sail`.

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/FernandoCastilla/TFG-Portfolio.git
cd ugr-portfolio
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
```

Abre `.env` y rellena los valores obligatorios:

```env
# Token de API de OpenProject
# Genéralo en: proyectostic.ugr.es → Mi cuenta → Token de API
OPENPROJECT_TOKEN=tu_token_aqui
```

Los valores de base de datos (`DB_*`) y el resto de servicios ya tienen valores por defecto listos para desarrollo. No es necesario cambiarlos salvo conflicto de puertos.

### 4. Levantar el entorno Docker

```bash
./vendor/bin/sail up -d
```

La primera vez descargará las imágenes Docker (~2 GB). Las siguientes arranca en segundos.

### 5. Generar la clave de la aplicación

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Ejecutar las migraciones

```bash
./vendor/bin/sail artisan migrate
```

### 7. Acceder a la aplicación

Abre el navegador en **http://localhost**

---

## Configuración de red con VPN (importante)

La API de OpenProject está en `proyectostic.ugr.es`, accesible únicamente desde la red de la UGR o mediante **VPN Cisco AnyConnect**.

El proyecto usa `network_mode: host` en Docker para que el contenedor comparta la interfaz de red del sistema anfitrión, permitiendo que las peticiones a la API salgan a través de la VPN. Esta configuración ya está incluida en `compose.yaml`.

### Problema conocido: sesiones con VPN activa

Cisco AnyConnect modifica las reglas de `iptables` al conectarse, bloqueando el tráfico hacia `127.0.0.1`. Esto impide que Laravel acceda a MySQL para gestionar sesiones. La solución ya está aplicada en `.env.example`:

```env
SESSION_DRIVER=file
CACHE_STORE=file
```

Con esta configuración las sesiones se guardan en `storage/framework/sessions/` y no dependen de MySQL.

---

## Sincronización con la API

Con la VPN activa, sincroniza proyectos y tareas desde la API:

```bash
# Desde terminal (fuera del contenedor)
./vendor/bin/sail artisan ugr:update

# O desde el botón "Sincronizar con OpenProject" del dashboard
```

La sincronización descarga todos los proyectos y Work Packages, los fusiona con los datos locales y guarda el resultado en:

```
storage/app/proyectos_ugr.json
storage/app/tareas_ugr.json
storage/app/sync_log.json
```

---

## Crear un usuario

Registra un usuario desde el formulario en `/registro` o directamente por consola:

```bash
./vendor/bin/sail artisan tinker
>>> App\Models\User::create(['name'=>'Admin','email'=>'admin@ugr.es','password'=>bcrypt('tu_password')]);
```

---

## Ejecutar los tests

```bash
# Todos los tests
./vendor/bin/sail artisan test

# Solo tests unitarios
./vendor/bin/sail artisan test --testsuite=Unit

# Un test concreto
./vendor/bin/sail artisan test --filter=ParsearDuracion
```

---

## Comandos útiles

```bash
# Levantar/detener el entorno
./vendor/bin/sail up -d
./vendor/bin/sail down

# Ver logs del contenedor
./vendor/bin/sail logs -f

# Entrar al contenedor
./vendor/bin/sail shell

# Limpiar caché
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
```

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS |
| Interactividad | Alpine.js, Chart.js, FullCalendar |
| Base de datos | MySQL 8.4 |
| Entorno | Docker + Laravel Sail |
| PDF | barryvdh/laravel-dompdf |

---

## Estructura del proyecto

```
app/
├── Console/Commands/
│   └── ActualizarDatosUGR.php   # ugr:update — sincronización con la API
├── Http/Controllers/
│   ├── AuthController.php        # Login y logout
│   └── ProjectController.php     # Proyectos, tareas, KPIs, PDF y parsearDuracion()
└── Models/
    ├── Objetivo.php              # Entidad pendiente de implementación
    ├── SyncLog.php               # Historial de sincronizaciones (fichero JSON)
    └── User.php
resources/views/
├── layouts/app.blade.php         # Layout principal con aside institucional UGR
├── welcome.blade.php             # Dashboard
├── proyectos/                    # Vistas de proyectos y tareas
├── auth/                         # Login y registro
├── errors/                       # Páginas 404 y 500 personalizadas
└── calendario.blade.php
storage/app/
├── proyectos_ugr.json            # Datos de proyectos (sincronizados)
├── tareas_ugr.json               # Datos de Work Packages (sincronizados)
└── sync_log.json                 # Historial de sincronizaciones
tests/
├── Feature/                      # Tests de rutas HTTP, autenticación y paginación
└── Unit/                         # Tests unitarios de parsearDuracion()
```

---

## Licencia

Este proyecto está liberado bajo la licencia [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html).