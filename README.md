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
- **Buscador global** de proyectos y tareas con filtro por entidad solicitante y año, y resultados en tiempo real
- **Asistente IA** en lenguaje natural para consultar los datos del Vicerrectorado (Llama 3.3 70B vía Groq)
- **Panel de configuración** en `/configuracion` para introducir los tokens sin editar ficheros del servidor
- **Modo oscuro** completo con preferencia persistente
- **Autenticación** de usuarios con registro y login

---

## Requisitos previos

| Herramienta | Versión mínima | Notas |
|---|---|---|
| [Docker](https://www.docker.com/) | 24.x | Con Docker Compose integrado |
| [PHP](https://www.php.net/) | 8.2 | Solo para instalar Composer |
| [Composer](https://getcomposer.org/) | 2.x | Gestión de dependencias PHP |

> **Nota:** PHP y Composer solo son necesarios para la instalación inicial en desarrollo. En producción solo se necesita Docker.

---

## Instalación (desarrollo local)

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

### Sesiones con VPN activa

Si accedes a OpenProject a través de VPN (Cisco AnyConnect), añade esto al `.env` para evitar conflictos de red con Docker:

```env
SESSION_DRIVER=file
CACHE_STORE=file
DB_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
```

---

## Despliegue en producción (Docker)

La imagen de producción se construye con Nginx + PHP-FPM y **no requiere configurar ningún fichero** antes de arrancar — el contenedor se inicializa solo.

### 1. Eliminar datos de MySQL locales

```bash
sudo rm -rf docker/mysql/
```

### 2. Construir la imagen

```bash
DOCKER_BUILDKIT=0 docker build -t ugr-portfolio:latest .
```

### 3. Arrancar el contenedor

```bash
docker compose -f docker-compose.prod.yml up -d
```

El contenedor arranca automáticamente, crea el `.env`, genera la `APP_KEY`, crea la base de datos SQLite y ejecuta las migraciones. No es necesario pasar ninguna variable de entorno para el primer arranque.

### 4. Verificar que funciona

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080
# Debe devolver: 200
```

### 5. Configurar los tokens

Abre `/configuracion` en el navegador e introduce:
- La URL y token de tu instancia de OpenProject
- Tu clave de API de Groq (gratuita en [console.groq.com](https://console.groq.com))

Los valores se guardan en `storage/app/config.json` sin necesidad de editar ningún fichero del servidor.

### 6. Proxy inverso

La aplicación escucha en el puerto 8080. Para exponerla públicamente configura tu proxy inverso (Nginx, Apache o Traefik) apuntando a `http://127.0.0.1:8080`.

### Exportar la imagen para enviar a un servidor

```bash
docker save ugr-portfolio:latest | gzip > ugr-portfolio.tar.gz
```

En el servidor de destino:

```bash
docker load < ugr-portfolio.tar.gz
docker compose -f docker-compose.prod.yml up -d
```

---

## Configuración de tokens

Los tokens se configuran desde el panel `/configuracion` de la propia aplicación, sin necesidad de acceso al servidor. Los valores se leen en este orden de prioridad:

1. `storage/app/config.json` (configurado desde el panel)
2. Variables de entorno del contenedor
3. Fichero `.env`

Para pasar los tokens directamente como variables de entorno al contenedor:

```bash
OPENPROJECT_TOKEN=tu_token GROQ_API_KEY=tu_clave docker compose -f docker-compose.prod.yml up -d
```

---

## Asistente IA

UGR Portfolio incluye un asistente conversacional accesible desde el botón flotante del dashboard. Permite consultar los datos en lenguaje natural:

- *¿Qué proyectos llevan retraso respecto a su fecha límite?*
- *¿Cuántas tareas tiene asignadas Manuel Noguera?*
- *¿Cuál es el progreso medio del proyecto de comedores?*

Usa el modelo **Llama 3.3 70B** a través de la API gratuita de [Groq](https://groq.com) (14.400 peticiones/día). Los datos se procesan localmente antes de enviarse al modelo.

---

## Sincronización con OpenProject

Con conectividad a la instancia y el token configurado, sincroniza desde el dashboard o por consola:

```bash
./vendor/bin/sail artisan ugr:update
```

Los datos se guardan en `storage/app/` y la aplicación funciona sin conexión permanente a la API.

---

## Crear un usuario

Regístrate desde `/registro`, o por consola:

```bash
./vendor/bin/sail artisan tinker
>>> App\Models\User::create(['name'=>'Admin','email'=>'admin@ejemplo.es','password'=>bcrypt('password')]);
```

---

## Ejecutar los tests

```bash
./vendor/bin/sail artisan test                            # todos
./vendor/bin/sail artisan test --testsuite=Unit           # solo unitarios
./vendor/bin/sail artisan test --filter=ParsearDuracion   # uno concreto
```

---

## Comandos útiles

```bash
./vendor/bin/sail up -d           # levantar entorno de desarrollo
./vendor/bin/sail down            # detener entorno
./vendor/bin/sail logs -f         # ver logs
./vendor/bin/sail shell           # entrar al contenedor
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear

# Producción
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml down
docker logs ugr-portfolio
docker exec ugr-portfolio php artisan migrate:status
```

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS |
| Interactividad | Alpine.js, Chart.js, FullCalendar |
| Base de datos | MySQL 8.4 (desarrollo) / SQLite (producción) |
| Asistente IA | Llama 3.3 70B vía Groq API |
| Entorno desarrollo | Docker + Laravel Sail |
| Entorno producción | Docker (Nginx + PHP-FPM + Supervisor) |
| PDF | barryvdh/laravel-dompdf |

---

## Estructura del proyecto

```
app/
├── Console/Commands/
│   └── ActualizarDatosUGR.php    # Comando de sincronización con la API
├── Helpers/
│   └── AppConfig.php             # Helper para leer tokens de config.json o .env
├── Http/Controllers/
│   ├── AuthController.php        # Login y logout
│   └── ProjectController.php     # Proyectos, tareas, KPIs, PDF y CSV
├── Services/
│   ├── WorkPackageTreeBuilder.php # Árbol jerárquico padre-hijo
│   ├── FamilyPaginator.php        # Paginación por familias completas
│   └── KpiCalculator.php          # Cálculo de KPIs y semáforo de salud
└── Models/
    ├── SyncLog.php               # Historial de sincronizaciones (JSON)
    └── User.php
docker/
├── nginx.conf                    # Configuración Nginx para producción
├── supervisord.conf              # Gestión de procesos Nginx + PHP-FPM
└── entrypoint.sh                 # Inicialización automática del contenedor
resources/views/
├── layouts/app.blade.php         # Layout principal
├── welcome.blade.php             # Dashboard con asistente IA
├── configuracion.blade.php       # Panel de configuración de tokens
├── proyectos/                    # Vistas de proyectos y tareas
├── auth/                         # Login y registro
└── errors/                       # Páginas de error personalizadas
storage/app/
├── config.json                   # Tokens configurados desde el panel
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