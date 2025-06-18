# API para Sistema de Reserva de Restaurantes

Este repositorio contiene el backend de un sistema de reserva de restaurantes, desarrollado con Laravel. La API proporciona los endpoints necesarios para gestionar usuarios (administradores, clientes, restaurantes), menús, reservas, calificaciones, reportes y un sistema de recomendación basado en K-Means.

## Autores

* **Aparicio Llanquipacha Gabriel** - [Deus-Ex-Umbra](https://github.com/Deus-Ex-Umbra)
* **Limachi Villaroel Alan Nicolás** - [AlanDevPro](https://github.com/AlanDevPro)

## Docente

* Ing. Oswaldo Gerardo Velásquez Aroni, M.Sc.

## Tabla de Contenidos

1.  [Características Principales](#características-principales)
2.  [Requisitos Previos](#requisitos-previos)
3.  [Instalación](#instalación)
4.  [Estructura de la API](#estructura-de-la-api)
5.  [Uso de la API (Endpoints)](#uso-de-la-api-endpoints)
    * [Autenticación](#autenticación)
    * [Rutas de Administrador](#rutas-de-administrador)
    * [Rutas de Cliente](#rutas-de-cliente)
    * [Rutas de Restaurante](#rutas-de-restaurante)
    * [Rutas Públicas](#rutas-públicas)
6.  [Códigos de Respuesta](#códigos-de-respuesta)
7.  [Ejemplos de Uso](#ejemplos-de-uso)

---

## Características Principales

- **Sistema de autenticación JWT** con roles diferenciados (Administrador, Cliente, Restaurante)
- **Gestión completa de usuarios** con perfiles específicos por rol
- **Sistema de reservas** con validación de disponibilidad y capacidad
- **Gestión de menús y platos** con soporte para imágenes
- **Sistema de calificaciones y reportes** para control de calidad
- **Recomendaciones inteligentes** basadas en algoritmo K-Means y One vs All
- **Filtros avanzados de búsqueda** para cada tipo de usuario
- **API RESTful** con respuestas en formato JSON

---

## Requisitos Previos

Asegúrate de tener instalado lo siguiente en tu entorno de desarrollo:

* **PHP** >= 8.2
* **Composer** (gestor de dependencias de PHP)
* **Base de datos** (MySQL, PostgreSQL, MariaDB o SQLite)
* **Git** (para clonar el repositorio)

---

## Instalación

Sigue estos pasos para configurar el proyecto en tu máquina local.

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/Deus-Ex-Umbra/ReservaRestaurantesAPI.git
    cd ReservaRestaurantesAPI
    ```

2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```

3.  **Configurar el entorno:**
    ```bash
    cp .env.example .env
    ```
    Edita el archivo `.env` con tu configuración de base de datos:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=reserva_restaurantes
    DB_USERNAME=tu_usuario
    DB_PASSWORD=tu_contraseña
    ```

4.  **Generar claves de seguridad:**
    ```bash
    php artisan key:generate
    php artisan jwt:secret
    ```

5.  **Configurar la base de datos:**
    ```bash
    php artisan migrate --seed
    ```
    Esto creará las tablas y datos de prueba incluyendo:
    - Administradores (gab.aparicio.ll@gmail.com / B4phy_B4lph0m3t)
    - 10,000 clientes con preferencias
    - 100 restaurantes con menús y platos
    - Reservas y calificaciones de ejemplo

6.  **Crear enlace simbólico para imágenes:**
    ```bash
    php artisan storage:link
    ```

7.  **Iniciar el servidor:**
    ```bash
    php artisan serve
    ```
    La API estará disponible en `http://127.0.0.1:8000`

---

## Estructura de la API

- **URL Base:** `http://127.0.0.1:8000/api/`
- **Autenticación:** JWT (JSON Web Tokens)
- **Formato de respuesta:** JSON
- **Método de autenticación:** `Authorization: Bearer {token}`

### Roles de Usuario

1. **Administrador:** Gestión completa del sistema, reportes y análisis
2. **Cliente:** Búsqueda de restaurantes, reservas y calificaciones
3. **Restaurante:** Gestión de menús, mesas, reservas y calificaciones recibidas

---

## Uso de la API (Endpoints)

### Autenticación

#### Registro de Usuario
```http
POST /api/autenticacion/registrarse
```

**Body (JSON):**
```json
{
    "correo": "usuario@example.com",
    "contraseña": "password123",
    "rol": "cliente"
}
```

**Roles disponibles:** `administrador`, `cliente`, `restaurante`

#### Iniciar Sesión
```http
POST /api/autenticacion/iniciar-sesion
```

**Body (JSON):**
```json
{
    "correo": "usuario@example.com",
    "contraseña": "password123"
}
```

**Respuesta exitosa:**
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
        "id": 1,
        "correo": "usuario@example.com",
        "rol": "cliente"
    },
    "usuario_detalle": {
        "id": 1,
        "nombres": "Juan",
        "apellidos": "Pérez",
        "telefono": "12345678",
        "imagen_base64": "data:image/jpeg;base64,..."
    },
    "perfil_completo": true
}
```

#### Obtener Perfil
```http
GET /api/autenticacion/perfil
Authorization: Bearer {token}
```

#### Cerrar Sesión
```http
GET /api/autenticacion/cerrar-sesion
Authorization: Bearer {token}
```

---

### Rutas de Administrador

**Prefijo:** `/api/administrador`  
**Autenticación:** Requerida (rol: administrador)

#### Gestión de Usuarios

```http
# Obtener todos los usuarios
GET /api/administrador/usuarios

# Obtener usuarios por rol
GET /api/administrador/usuarios/{rol}

# Buscar usuarios con filtros
GET /api/administrador/buscar/usuarios?correo=juan&rol=cliente&nombres=Juan

# Obtener usuario específico
GET /api/administrador/usuario/{id}

# Eliminar usuario
DELETE /api/administrador/eliminar-usuario/{id}
```

#### Análisis y Reportes

```http
# Ver todas las reservas del sistema
GET /api/administrador/todas-reservas?id_restaurante=1&estado_reserva=completada

# Ver todos los reportes
GET /api/administrador/todos-reportes?estado_reporte=pendiente

# Ver todas las calificaciones
GET /api/administrador/todas-calificaciones?puntuacion_minima=4

# Generar dataset para análisis
GET /api/administrador/generar-dataset-kmeans
```

#### Gestión de Reportes

```http
# Obtener reportes pendientes
GET /api/administrador/reportes-pendientes

# Procesar reporte
PUT /api/administrador/procesar-reporte/{id}
```

**Body para procesar reporte:**
```json
{
    "accion": "aceptar",
    "comentario_admin": "Reporte válido, acción tomada"
}
```

---

### Rutas de Cliente

**Prefijo:** `/api/cliente`  
**Autenticación:** Requerida (rol: cliente)

#### Gestión de Perfil

```http
# Crear perfil de cliente
POST /api/cliente/crear-cliente
```

**Body (form-data):**
```json
{
    "id_usuario": 1,
    "nombres": "Juan",
    "apellidos": "Pérez",
    "telefono": "12345678",
    "ruta_imagen_cliente": "archivo_imagen.jpg"
}
```

```http
# Editar perfil
PUT /api/cliente/editar/{id}

# Obtener perfil
GET /api/cliente/{id}
```

#### Gestión de Preferencias

```http
# Crear preferencias
POST /api/cliente/crear-preferencia
```

**Body (JSON):**
```json
{
    "id_usuario_cliente": 1,
    "tipo_restaurante_preferencia": "italiana",
    "calificacion_minima_preferencia": 4.0,
    "precio_maximo_preferencia": 200.00
}
```

```http
# Obtener preferencias
GET /api/cliente/preferencia/{id_usuario_cliente}

# Editar preferencias
PUT /api/cliente/editar-preferencia/{id}

# Eliminar preferencias
DELETE /api/cliente/eliminar-preferencia/{id}
```

#### Búsqueda de Restaurantes

```http
# Buscar restaurantes con filtros
GET /api/cliente/buscar/restaurantes?nombre_restaurante=pizza&tipo_restaurante=italiana&calificacion_minima=4&precio_maximo=150
```

**Parámetros disponibles:**
- `nombre_restaurante`: Buscar por nombre
- `tipo_restaurante`: comida-tradicional, parrilla, comida-rapida, italiana, china, internacional, postres, bebidas
- `calificacion_minima`: Calificación mínima (0-5)
- `direccion`: Buscar por dirección
- `categoria`: Buscar por categoría
- `precio_maximo`: Precio máximo promedio de platos

#### Gestión de Reservas

```http
# Crear reserva
POST /api/cliente/crear-reserva
```

**Body (JSON):**
```json
{
    "id_usuario_cliente": 1,
    "id_restaurante": 2,
    "id_mesas": [1, 2],
    "id_platos": [5, 6, 7],
    "fecha_reserva": "2025-07-20",
    "hora_reserva": "20:00",
    "personas_reserva": 4,
    "comentarios_reserva": "Mesa en terraza si es posible",
    "telefono_contacto_reserva": "12345678"
}
```

```http
# Ver mis reservas
GET /api/cliente/reservas/{id_usuario_cliente}

# Ver reservas agrupadas por restaurante
GET /api/cliente/reservas-por-restaurante/{id_usuario_cliente}

# Cancelar reserva
PUT /api/cliente/cancelar-reserva/{id}
```

#### Gestión de Calificaciones

```http
# Crear calificación
POST /api/cliente/crear-calificacion
```

**Body (JSON):**
```json
{
    "id_usuario_cliente": 1,
    "id_restaurante": 2,
    "id_reserva": 3,
    "puntuacion": 4.5,
    "comentario": "Excelente servicio y comida deliciosa"
}
```

```http
# Ver mis calificaciones
GET /api/cliente/calificaciones/{id_usuario_cliente}

# Editar calificación
PUT /api/cliente/editar-calificacion/{id}

# Eliminar calificación
DELETE /api/cliente/eliminar-calificacion/{id}
```

#### Recomendaciones

```http
# Obtener recomendaciones personalizadas
GET /api/cliente/recomendaciones/{id_usuario_cliente}
```

#### Reportes

```http
# Crear reporte
POST /api/cliente/crear-reporte
```

**Body (JSON):**
```json
{
    "id_usuario_reportante": 1,
    "tipo_usuario_reportante": "cliente",
    "id_calificacion": 5,
    "motivo_reporte": "contenido-inapropiado",
    "descripcion_reporte": "Comentario ofensivo en la calificación"
}
```

---

### Rutas de Restaurante

**Prefijo:** `/api/restaurante`  
**Autenticación:** Requerida (rol: restaurante)

#### Gestión de Perfil

```http
# Crear perfil de restaurante
POST /api/restaurante/crear-usuario
```

**Body (form-data):**
```json
{
    "id_usuario": 3,
    "nombre_restaurante": "La Bella Italia",
    "direccion": "Av. Principal 123",
    "telefono": "12345678",
    "categoria": "Restaurante Familiar",
    "horario_apertura": "09:00",
    "horario_cierre": "23:00",
    "tipo_restaurante": "italiana",
    "ruta_imagen_restaurante": "archivo_imagen.jpg"
}
```

```http
# Editar perfil
PUT /api/restaurante/editar-usuario/{id}

# Obtener perfil
GET /api/restaurante/{id}
```

#### Gestión de Mesas

```http
# Ver mis mesas
GET /api/restaurante/{id}/mesas

# Crear mesa
POST /api/restaurante/crear-mesa
```

**Body (JSON):**
```json
{
    "id_restaurante": 1,
    "numero_mesa": 5,
    "capacidad_mesa": 4,
    "precio_mesa": 25.00,
    "estado_mesa": "disponible"
}
```

```http
# Editar mesa
PUT /api/restaurante/editar-mesa/{id}

# Eliminar mesa
DELETE /api/restaurante/eliminar-mesa/{id}

# Cambiar estado de mesa
PUT /api/restaurante/cambiar-estado-mesa/{id}
```

#### Gestión de Menús y Platos

```http
# Ver mis menús
GET /api/restaurante/{id}/menus

# Crear menú
POST /api/restaurante/crear-menu
```

**Body (form-data):**
```json
{
    "id_restaurante": 1,
    "nombre_menu": "Especialidades Italianas",
    "descripcion_menu": "Nuestros platos más tradicionales",
    "tipo_menu": "italiana",
    "ruta_imagen_menu": "archivo_imagen.jpg"
}
```

```http
# Ver platos de un menú
GET /api/restaurante/menu/{id_menu}/platos

# Crear plato
POST /api/restaurante/crear-plato
```

**Body (form-data):**
```json
{
    "id_menu": 1,
    "nombre_plato": "Pizza Margherita",
    "descripcion_plato": "Pizza clásica con tomate, mozzarella y albahaca",
    "precio_plato": 85.00,
    "disponible": true,
    "ruta_imagen_plato": "archivo_imagen.jpg"
}
```

```http
# Editar plato
PUT /api/restaurante/editar-plato/{id}

# Eliminar plato
DELETE /api/restaurante/eliminar-plato/{id}

# Cambiar disponibilidad
PUT /api/restaurante/cambiar-disponibilidad-plato/{id}
```

#### Gestión de Reservas

```http
# Ver mis reservas
GET /api/restaurante/reservas/{id_restaurante}

# Buscar reservas con filtros
GET /api/restaurante/buscar/{id_restaurante}/reservas?estado_reserva=pendiente&fecha_inicio=2025-01-01&fecha_fin=2025-12-31

# Ver reservas por fecha específica
GET /api/restaurante/reservas-por-fecha/{id_restaurante}?fecha=2025-07-20

# Procesar reserva (aceptar/rechazar)
PUT /api/restaurante/procesar-reserva/{id}
```

**Body para procesar reserva:**
```json
{
    "accion": "aceptar",
    "comentario_restaurante": "Reserva confirmada, mesa preparada"
}
```

```http
# Completar reserva
PUT /api/restaurante/completar-reserva/{id}
```

#### Gestión de Calificaciones

```http
# Ver todas mis calificaciones
GET /api/restaurante/calificaciones/{id_restaurante}

# Ver calificaciones agrupadas por cliente
GET /api/restaurante/calificaciones-por-cliente/{id_restaurante}
```

---

### Rutas Públicas

**Prefijo:** `/api/publico`  
**Autenticación:** No requerida

```http
# Ver todos los restaurantes
GET /api/publico/restaurantes

# Ver restaurante específico
GET /api/publico/restaurante/{id}

# Buscar restaurantes
GET /api/publico/buscar/restaurantes?nombre=pizza&tipo_restaurante=italiana

# Ver restaurantes por tipo
GET /api/publico/restaurantes/tipo/italiana

# Ver mesas disponibles
GET /api/publico/restaurante/{id_restaurante}/mesas-disponibles?fecha=2025-07-20&hora=20:00&personas=4

# Ver tipos de restaurante disponibles
GET /api/publico/tipos-restaurante

# Ver tipos de menú disponibles
GET /api/publico/tipos-menu

# Ver menús por tipo
GET /api/publico/menus/tipo/italiana
```

---

## Códigos de Respuesta

| Código | Descripción |
|--------|------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Error en los datos enviados |
| 401 | Unauthorized - Token inválido o faltante |
| 403 | Forbidden - Sin permisos para esta acción |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Errores de validación |
| 500 | Internal Server Error - Error del servidor |

### Estructura de Respuestas de Error

```json
{
    "error": {
        "campo": ["Mensaje de error específico"]
    }
}
```

### Estructura de Respuestas Exitosas

```json
{
    "message": "Operación exitosa",
    "data": {
        // Datos del recurso
    }
}
```

---

## Ejemplos de Uso

### 1. Flujo Completo de Cliente

```bash
# 1. Registrarse
curl -X POST http://127.0.0.1:8000/api/autenticacion/registrarse \
  -H "Content-Type: application/json" \
  -d '{"correo":"cliente@test.com","contraseña":"password123","rol":"cliente"}'

# 2. Iniciar sesión
curl -X POST http://127.0.0.1:8000/api/autenticacion/iniciar-sesion \
  -H "Content-Type: application/json" \
  -d '{"correo":"cliente@test.com","contraseña":"password123"}'

# 3. Crear perfil (usar token obtenido)
curl -X POST http://127.0.0.1:8000/api/cliente/crear-cliente \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"id_usuario":1,"nombres":"Juan","apellidos":"Pérez","telefono":"12345678"}'

# 4. Buscar restaurantes
curl -X GET "http://127.0.0.1:8000/api/cliente/buscar/restaurantes?tipo_restaurante=italiana&calificacion_minima=4" \
  -H "Authorization: Bearer {token}"
```

### 2. Flujo de Reserva

```bash
# 1. Ver mesas disponibles
curl -X GET "http://127.0.0.1:8000/api/publico/restaurante/1/mesas-disponibles?fecha=2025-07-20&hora=20:00&personas=4"

# 2. Crear reserva
curl -X POST http://127.0.0.1:8000/api/cliente/crear-reserva \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id_usuario_cliente": 1,
    "id_restaurante": 1,
    "id_mesas": [1],
    "id_platos": [1, 2],
    "fecha_reserva": "2025-07-20",
    "hora_reserva": "20:00",
    "personas_reserva": 4
  }'
```

### 3. Sistema de Calificaciones

```bash
# 1. Crear calificación (después de completar reserva)
curl -X POST http://127.0.0.1:8000/api/cliente/crear-calificacion \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id_usuario_cliente": 1,
    "id_restaurante": 1,
    "id_reserva": 1,
    "puntuacion": 4.5,
    "comentario": "Excelente servicio"
  }'

# 2. Ver calificaciones del restaurante
curl -X GET http://127.0.0.1:8000/api/restaurante/calificaciones/1 \
  -H "Authorization: Bearer {token_restaurante}"
```

---

## Notas Importantes

1. **Autenticación:** Todas las rutas excepto las públicas y de autenticación requieren token JWT
2. **Roles:** Cada usuario tiene un rol específico que determina a qué endpoints puede acceder
3. **Imágenes:** Se almacenan en base64 y se devuelven en las respuestas cuando están disponibles
4. **Validaciones:** Todos los endpoints validan los datos de entrada y devuelven errores específicos
5. **Recomendaciones:** El sistema usa algoritmo K-Means entrenado con datos históricos de reservas
6. **Estados de reserva:** `pendiente` → `aceptada`/`rechazada` → `completada`/`cancelada`

Para más información o soporte, contacta a los desarrolladores a través de sus perfiles de GitHub.