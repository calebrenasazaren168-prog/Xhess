# XHESS — Sistema de Torneos de Ajedrez

XHESS es una aplicación web para la gestión profesional de torneos de ajedrez por equipos. Permite organizar torneos, gestionar equipos y jugadores, generar rondas automáticamente, registrar resultados y calcular clasificaciones en tiempo real.

---

## Características Principales

- **Gestión de torneos** con soporte para tres sistemas de juego:
  - Round Robin Clásico
  - Round Robin Ponderado (emparejamiento por puntuación individual)
  - Sistema Suizo
- **Gestión de equipos y jugadores** con sistema de solicitudes de ingreso
- **Generación automática de rondas y emparejamientos**
- **Registro de resultados** por tablero y cálculo de clasificaciones
- **Sistema de rating ELO** actualizado automáticamente tras cada partida
- **Verificación de correo electrónico** al registrarse
- **Sistema de solicitudes** para inscribir equipos en torneos
- **Panel de administración** para gestión completa del sistema

---

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 7.2+ |
| Base de datos | MariaDB 11.4 (MySQL compatible) |
| Frontend | Bootstrap 5, Font Awesome 6, JavaScript |
| Correo | Brevo API (HTTP, sin SMTP) |
| Hosting | InfinityFree (compartido) |

---

## Estructura del Proyecto

```
xhess/
├── classes/
│   ├── Database.php          # Singleton PDO
│   ├── Auth.php              # Login, registro, sesiones
│   ├── JugadorManager.php    # Gestión de jugadores
│   ├── EquipoManager.php     # Gestión de equipos y solicitudes
│   ├── TorneoManager.php     # Torneos, clasificaciones, solicitudes
│   ├── RondaManager.php      # Generación de rondas y emparejamientos
│   ├── RatingCalculator.php  # Cálculo ELO
│   ├── AdminManager.php      # Funciones administrativas
│   └── EmailService.php      # Envío de correos vía Brevo API
│
├── handlers/
│   └── ActionHandler.php     # Procesa todas las acciones POST
│
├── includes/
│   ├── navbar.php
│   ├── inicio.php
│   ├── footer.php
│   └── modals/
│       ├── modal_Login.php
│       ├── modal_Registro.php
│       ├── modal_MiEquipo.php
│       ├── modal_MisTorneos.php
│       ├── modal_CrearTorneo.php
│       ├── modal_Admin.php
│       ├── modal_GestionEquipo.php
│       ├── modal_SolicitudesEquipo.php
│       ├── modal_SolicitudTorneo_modal.php
│       ├── modal_VerClasificacion.php
│       └── torneos/
│           ├── tab_equipos.php
│           ├── tab_clasificacion.php
│           ├── tab_rondas.php
│           ├── tab_emparejamientos.php
│           ├── tab_solicitudes_torneo.php
│           ├── selector_torneo.php
│           └── selector_ronda.php
│
├── index.php                 # Punto de entrada principal
├── sistema_xhess.php         # Clase fachada XhessSystem + bootstrap
├── ajax_clasificacion.php    # Endpoint AJAX para clasificaciones
├── verificar_email.php       # Verificación de cuenta por token
├── script.js                 # Lógica del cliente
├── style.css                 # Estilos personalizados
└── xhess.sql                 # Esquema e datos iniciales de la BD
```


---

## Modelo de Datos (Resumen)

| Tabla | Descripción |
|-------|-------------|
| `cuentas` | Usuarios del sistema con roles |
| `roles` | admin, coach, jugador |
| `jugadores` | Perfil de jugador vinculado a una cuenta |
| `equipos` | Equipos pertenecientes a clubes |
| `clubes` | Organizaciones que agrupan equipos |
| `torneos` | Torneos con sistema, fechas y estado |
| `torneo_equipos` | Relación muchos-a-muchos torneo ↔ equipo |
| `rondas` | Rondas generadas dentro de un torneo |
| `emparejamientos` | Enfrentamientos entre equipos por ronda |
| `partidas` | Partidas individuales por tablero |
| `solicitudes_equipo` | Solicitudes de jugadores para unirse a equipos |
| `solicitudes_torneo` | Solicitudes de equipos para inscribirse en torneos |
| `estadisticas_equipo` | Estadísticas acumuladas por torneo |
| `estadisticas_jugador` | Estadísticas acumuladas por torneo |
| `activaciones_cuenta` | Tokens de verificación / reset de contraseña |

---

## Instalación

### Requisitos

- PHP 7.2 o superior con extensiones PDO y cURL
- MySQL / MariaDB 10.x o superior
- Servidor web Apache o Nginx

### Pasos

1. **Subir los archivos** al servidor web.

2. **Importar la base de datos:**
```bash
   mysql -u usuario -p nombre_base < xhess.sql
```

3. **Configurar la conexión** en `classes/Database.php`:
```php
   $this->pdo = new PDO(
       "mysql:host=TU_HOST;dbname=TU_BD;charset=utf8mb4",
       "TU_USUARIO",
       "TU_CONTRASEÑA"
   );
```

4. **Configurar el correo** en `classes/EmailService.php`:
```php
   private const API_KEY  = 'xkeysib-TU_API_KEY';
   private const URL_BASE = 'https://tu-dominio.com';
```
   > Requiere cuenta en [Brevo](https://www.brevo.com/).

5. **Crear la cuenta administrador** en la base de datos:
```sql
   INSERT INTO cuentas (usuario, contrasena, id_rol, correo, email_verificado)
   VALUES ('admin', '$2y$10$HASH_BCRYPT', 1, 'admin@tudominio.com', 1);
```

---

## Roles de Usuario

| Rol | Permisos |
|-----|----------|
| **admin** | Acceso total al sistema |
| **coach** | Reservado para uso futuro |
| **jugador** | Crear equipos, unirse, inscribirse en torneos |

---

## Sistemas de Juego

**Round Robin Clásico** — todos contra todos, una vez.

**Round Robin Ponderado** — misma estructura, pero los jugadores se ordenan por puntuación acumulada para asignar tableros dentro de cada match.

**Sistema Suizo** — empareja equipos con puntuación similar en cada ronda, evitando repetir enfrentamientos y balanceando colores.

---

## Sistema de Puntuación

| Resultado | Puntos de match |
|-----------|----------------|
| Victoria  | 2 |
| Empate    | 1 |
| Derrota   | 0 |

Desempate por puntos de tablero. Rating ELO actualizado automáticamente con K = 32.
