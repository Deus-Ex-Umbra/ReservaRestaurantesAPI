# API para Sistema de Reserva de Restaurantes

Este repositorio contiene el backend de un sistema de reserva de restaurantes, desarrollado con Laravel. La API proporciona los endpoints necesarios para gestionar usuarios (administradores, clientes, restaurantes), menús, reservas, calificaciones y más.

## Autores

* **Aparicio Llanquipacha Gabriel** - [Deus-Ex-Umbra](https://github.com/Deus-Ex-Umbra)
* **Limachi Villaroel Alan Nicolás** - [AlanDevPro](https://github.com/AlanDevPro)

## Docente

* Ing. Oswaldo Gerardo Velásquez Aroni, M.Sc.

## Tabla de Contenidos

1.  [Requisitos Previos](#requisitos-previos)
2.  [Instalación](#instalación)
3.  [Uso de la API (Endpoints)](#uso-de-la-api-endpoints)
    * [Autenticación](#autenticación)
    * [Rutas de Administrador](#rutas-de-administrador)
    * [Rutas de Cliente](#rutas-de-cliente)
    * [Rutas de Restaurante](#rutas-de-restaurante)
    * [Rutas de Imágenes (Públicas)](#rutas-de-imágenes-públicas)

---

## Requisitos Previos

Asegúrate de tener instalado lo siguiente en tu entorno de desarrollo:

* PHP >= 8.2
* Composer
* Un gestor de base de datos (Ej. MySQL, PostgreSQL, MariaDB)
* Git

## Instalación

Sigue estos pasos para configurar el proyecto en tu máquina local.

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/Deus-Ex-Umbra/ReservaRestaurantesAPI.git](https://github.com/Deus-Ex-Umbra/ReservaRestaurantesAPI.git)
    cd ReservaRestaurantesAPI
    ```

2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```

3.  **Configurar el entorno:**
    Crea una copia del archivo de ejemplo `.env`.
    ```bash
    cp .env.example .env
    ```
    Abre el archivo `.env` y configura tus credenciales de base de datos (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4.  **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```

5.  **Generar el secreto para JWT (JSON Web Tokens):**
    Este comando creará la clave secreta para la autenticación.
    ```bash
    php artisan jwt:secret
    ```

6.  **Ejecutar las migraciones y los seeders:**
    Las migraciones crearán la estructura de la base de datos y los seeders la llenarán con datos de prueba.
    ```bash
    php artisan migrate --seed
    ```

7.  **Crear el enlace simbólico para el almacenamiento:**
    Para que las imágenes y otros archivos públicos sean accesibles.
    ```bash
    php artisan storage:link
    ```

8.  **Iniciar el servidor de desarrollo:**
    ```bash
    php artisan serve
    ```
    La API estará disponible en `http://127.0.0.1:8000` o en el puerto que se indique.

---

## Uso de la API (Endpoints)

La URL base para todos los endpoints es `http://127.0.0.1:8000/api/`. Todas las rutas que requieren autenticación deben incluir el token JWT en la cabecera `Authorization: Bearer {token}`.

### Autenticación

Rutas para registrar, iniciar y cerrar sesión.

#### `POST /autenticacion/registrarse`

Registra un nuevo usuario. El tipo de usuario se define con el campo `rol`.

* **Acceso:** Público
* **Body para Cliente (raw, JSON):**
    ```json
    {
        "nombre": "Juan Perez",
        "email": "juan.perez@example.com",
        "password": "password123",
        "password_confirmation": "password123",
        "rol": "cliente"
    }
    ```
* **Body para Restaurante (raw, JSON):**
    ```json
    {
        "nombre": "La Buena Mesa",
        "email": "contacto@labuenamesa.com",
        "password": "password123",
        "password_confirmation": "password123",
        "rol": "restaurante",
        "direccion": "Calle Falsa 123, Springfield",
        "telefono": "555123456",
        "descripcion": "La mejor comida tradicional.",
        "horario": "09:00-23:00"
    }
    ```

#### `POST /autenticacion/iniciar-sesion`

Inicia sesión para obtener un token de acceso.

* **Acceso:** Público
* **Body (raw, JSON):**
    ```json
    {
        "email": "cliente1@example.com",
        "password": "password"
    }
    ```
* **Respuesta Exitosa (200 OK):**
    ```json
    {
        "access_token": "ey...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "nombre": "Juan Perez",
            "email": "cliente1@example.com",
            "rol": "cliente"
        }
    }
    ```

#### `GET /autenticacion/cerrar-sesion`

Invalida el token del usuario para cerrar su sesión.

* **Acceso:** Autenticado (cualquier rol)
* **Headers:** `Authorization: Bearer {token}`

---

### Rutas de Administrador

* **Prefijo:** `/administrador`
* **Middleware:** `auth:api`, `rol:administrador`

#### Gestión de Usuarios
* **`GET /usuarios`**: Obtiene todos los usuarios del sistema.
* **`GET /usuarios/{rol}`**: Obtiene usuarios filtrados por rol (`cliente`, `restaurante`, `administrador`).
* **`GET /usuario/{id}`**: Obtiene un usuario específico por su ID.
* **`POST /crear-usuario`**: Crea un nuevo usuario administrador.
* **`PUT /actualizar-usuario/{id}`**: Actualiza un usuario administrador.
* **`DELETE /eliminar-usuario/{id}`**: Elimina cualquier usuario por su ID.

#### Gestión de Reportes
* **`GET /reportes`**: Obtiene todos los reportes.
* **`GET /reportes-pendientes`**: Obtiene reportes con estado "pendiente".
* **`GET /reporte/{id}`**: Obtiene un reporte por su ID.
* **`PUT /procesar-reporte/{id}`**: Cambia el estado de un reporte a "procesado".

#### Sistema de Recomendación
* **`GET /generar-dataset-kmeans`**: Genera y devuelve el dataset utilizado por el recomendador.

---

### Rutas de Cliente

* **Prefijo:** `/cliente`
* **Middleware:** `auth:api`, `rol:cliente`

#### Gestión de Perfil
* **`GET /{id}`**: Obtiene la información del perfil del cliente.
* **`PUT /editar/{id}`**: Edita la información del perfil del cliente.
    * **Body (raw, JSON):**
        ```json
        {
            "nombre": "Juan Carlos",
            "telefono": "111222333"
        }
        ```

#### Gestión de Preferencias
* **`GET /preferencia/{id_usuario_cliente}`**: Obtiene las preferencias de un cliente.
* **`POST /crear-preferencia`**: Añade una nueva preferencia al cliente.
* **`PUT /editar-preferencia/{id}`**: Edita una preferencia existente.
* **`DELETE /eliminar-preferencia/{id}`**: Elimina una preferencia.

#### Reservas y Calificaciones
* **`POST /crear-reserva`**: Crea una nueva reserva.
    * **Body (raw, JSON):**
        ```json
        {
            "id_usuario_cliente": 1,
            "id_usuario_restaurante": 2,
            "fecha": "2025-07-20",
            "hora": "20:00:00",
            "cantidad_personas": 4,
            "platos": [1, 3],
            "mesas": [5]
        }
        ```
* **`GET /reservas/{id_usuario_cliente}`**: Obtiene el historial de reservas del cliente.
* **`PUT /cancelar-reserva/{id}`**: Cancela una reserva.
* **`POST /crear-calificacion`**: Permite al cliente calificar un restaurante.
    * **Body (raw, JSON):**
        ```json
        {
            "id_usuario_cliente": 1,
            "id_usuario_restaurante": 2,
            "puntuacion": 5,
            "comentario": "¡Excelente servicio!"
        }
        ```
* **`GET /calificaciones/{id_usuario_cliente}`**: Obtiene las calificaciones hechas por el cliente.
* **`PUT /editar-calificacion/{id}`**: Edita una calificación.
* **`DELETE /eliminar-calificacion/{id}`**: Elimina una calificación.

#### Otros
* **`GET /recomendaciones/{id_usuario_cliente}`**: Obtiene restaurantes recomendados.
* **`POST /crear-reporte`**: Permite a un cliente crear un reporte sobre un restaurante o experiencia.

---

### Rutas de Restaurante

* **Prefijo:** `/restaurante`
* **Middleware:** `auth:api`, `rol:restaurante`

#### Gestión de Perfil del Restaurante
* **`GET /{id}`**: Obtiene el perfil del restaurante.
* **`PUT /editar-usuario/{id}`**: Edita la información del restaurante.
* **`DELETE /eliminar-usuario/{id}`**: Elimina la cuenta del restaurante.

#### Gestión de Mesas
* **`GET /{id}/mesas`**: Obtiene todas las mesas del restaurante.
* **`POST /crear-mesa`**: Crea una nueva mesa.
    * **Body (raw, JSON):**
        ```json
        {
            "id_usuario_restaurante": 2,
            "capacidad": 4,
            "ubicacion": "Terraza"
        }
        ```
* **`PUT /editar-mesa/{id}`**: Edita una mesa.
* **`DELETE /eliminar-mesa/{id}`**: Elimina una mesa.
* **`PUT /cambiar-estado-mesa/{id}`**: Cambia el estado de una mesa.

#### Gestión de Menús y Platos
* **`GET /{id}/menus`**: Obtiene todos los menús del restaurante.
* **`POST /crear-menu`**: Crea un nuevo menú.
* **`GET /menu/{id_menu}/platos`**: Obtiene todos los platos de un menú específico.
* **`POST /crear-plato`**: Crea un nuevo plato. Requiere `multipart/form-data`.
    * **Body (form-data):**
        * `nombre`: Lomo Saltado
        * `descripcion`: Carne de res salteada con verduras.
        * `precio`: 55.50
        * `id_menu`: 1
        * `imagen`: (archivo de imagen)
* **`PUT /editar-plato/{id}`**: Edita un plato (usar `POST` con `_method: "PUT"` si se envía imagen).
* **`DELETE /eliminar-plato/{id}`**: Elimina un plato.
* **`PUT /cambiar-disponibilidad-plato/{id}`**: Cambia la disponibilidad de un plato.

#### Gestión de Reservas y Calificaciones
* **`GET /reservas/{id_restaurante}`**: Obtiene todas las reservas del restaurante.
* **`GET /reservas-por-fecha/{id_restaurante}`**: Filtra las reservas por fecha (`?fecha=YYYY-MM-DD`).
* **`PUT /procesar-reserva/{id}`**: Procesa una reserva (ej. confirmar).
* **`PUT /completar-reserva/{id}`**: Marca una reserva como completada.
* **`GET /calificaciones/{id_restaurante}`**: Obtiene todas las calificaciones recibidas.

#### Reportes
* **`POST /crear-reporte`**: Permite al restaurante generar reportes internos.

---

### Rutas de Imágenes (Públicas)

Endpoints públicos para obtener imágenes del sistema.

* **`GET /imagen/plato/{id}`**: Obtiene la imagen de un plato por su ID.
* **`GET /imagen/restaurante/perfil/{id}`**: Obtiene la imagen de perfil de un restaurante.
* **`GET /imagen/restaurante/banner/{id}`**: Obtiene la imagen de banner de un restaurante.

