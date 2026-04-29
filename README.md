# UGR Portfolio

Aplicación web de gestión y monitorización de Work Packages sobre la API de OpenProject del Vicerrectorado de Transformación Digital de la Universidad de Granada.

Desarrollada como Trabajo de Fin de Grado — Grado en Ingeniería Informática, ETSIIT, Universidad de Granada.

**Autor:** Fernando Castilla  
**Tutor:** Manuel Noguera  
**Curso:** 2025–2026

---

## ¿Qué hace?

UGR Portfolio consume la API REST de una instancia de OpenProject y presenta sus datos en una interfaz centralizada adaptada al perfil de seguimiento:

- **Dashboard** con KPIs globales, gráfica de distribución de estados, actividad reciente y panel lateral interactivo
- **Listado de proyectos** con filtros y jerarquía padre-hijo
- **Work Packages** con filtros multidimensionales, jerarquía expandible, tiempos estimado/dedicado, paginación por familias y semáforo de salud por proyecto
- **Calendario** interactivo con colores por tipo de estado y leyenda
- **Exportación a PDF y CSV** del listado de tareas de cada proyecto
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

> **Nota:** PHP y Composer solo son necesarios para la instalación inicial. Una vez levantado el entorno Docker, todos los comandos se ejecutan dentro del contenedor con `./vendor/bin/sail`.

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/FernandoCastilla/TFG-Portfolio.git
cd TFG-Portfolio

# 2. Instalar dependencias PHP
composer install

# 3. Configurar el entorno
cp .env.example .env

# 4. Levantar el entorno Docker
./vendor/bin/sail up -d

# 5. Generar la clave de la aplicación
./vendor/bin/sail artisan key:generate

# 6. Ejecutar las migraciones
./vendor/bin/sail artisan migrate
```

Abre el navegador en **http://localhost**

---

## Configuración

Edita el fichero `.env` con tus valores. Los campos obligatorios son:

```env
# URL base de la instancia de OpenProject
OPENPROJECT_URL=https://tu-instancia.openproject.com

# Token de API personal
# Genéralo en: tu-instancia → Mi cuenta → Token de API
OPENPROJECT_TOKEN=tu_token_aqui
```

El resto de variables tienen valores por defecto listos para desarrollo local.

### Sesiones con VPN activa

Si accedes a OpenProject a través de VPN (Cisco AnyConnect), añade esto al `.env` para evitar conflictos de red con Docker:

```env
SESSION_DRIVER=file
CACHE_STORE=file
```

---

## Sincronización con OpenProject

Con conectividad a la instancia, sincroniza proyectos y tareas:

```bash
./vendor/bin/sail artisan ugr:update
```

También puedes hacerlo desde el botón **"Sincronizar con OpenProject"** del dashboard una vez autenticado.

Los datos se guardan localmente en `storage/app/` y la aplicación funciona sin conexión permanente a la API.

---

## Crear un usuario

Regístrate desde el formulario en `/registro`, o por consola:

```bash
./vendor/bin/sail artisan tinker
>>> App\Models\User::create(['name'=>'Admin','email'=>'admin@ejemplo.es','password'=>bcrypt('tu_password')]);
```

---

## Ejecutar los tests

```bash
./vendor/bin/sail artisan test                            # todos
./vendor/bin/sail artisan test --testsuite=Unit           # solo unitarios
./vendor/bin/sail artisan test --filter=ParsearDuracion   # uno concreto
```

---

## Despliegue en producción

El proyecto incluye un `Dockerfile` y un `docker-compose.prod.yml` para despliegue en producción con Nginx + PHP-FPM.

```bash
# Construir la imagen
docker build -t ugr-portfolio:latest .

# Arrancar con las variables de entorno necesarias
OPENPROJECT_TOKEN=tu_token docker compose -f docker-compose.prod.yml up -d
```

La aplicación escucha en el puerto 8080. Configura un proxy inverso (Nginx o Apache) para exponerla en la URL pública deseada.

---

## Comandos útiles

```bash
./vendor/bin/sail up -d           # levantar entorno
./vendor/bin/sail down            # detener entorno
./vendor/bin/sail logs -f         # ver logs
./vendor/bin/sail shell           # entrar al contenedor
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
| Base de datos | MySQL 8.4 (desarrollo) / SQLite (producción) |
| Entorno | Docker + Laravel Sail |
| PDF | barryvdh/laravel-dompdf |

---

## Estructura del proyecto

```
app/
├── Console/Commands/
│   └── ActualizarDatosUGR.php   # Comando de sincronización con la API
├── Http/Controllers/
│   ├── AuthController.php        # Login y logout
│   └── ProjectController.php     # Proyectos, tareas, KPIs, PDF y CSV
└── Models/
    ├── SyncLog.php               # Historial de sincronizaciones (fichero JSON)
    └── User.php
docker/
├── nginx.conf                    # Configuración Nginx para producción
├── supervisord.conf              # Gestión de procesos
└── entrypoint.sh                 # Inicialización del contenedor
resources/views/
├── layouts/app.blade.php         # Layout principal
├── welcome.blade.php             # Dashboard
├── proyectos/                    # Vistas de proyectos y tareas
├── auth/                         # Login y registro
└── errors/                       # Páginas de error personalizadas
storage/app/
├── proyectos_ugr.json            # Proyectos sincronizados
├── tareas_ugr.json               # Work Packages sincronizados
└── sync_log.json                 # Historial de sincronizaciones
tests/
├── Feature/                      # Tests de integración
└── Unit/                         # Tests unitarios
```

---

## Licencia

Este proyecto está liberado bajo la licencia [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html).
