# SolVolt Energía — Trabajo Final PHP

Sitio web de una empresa ficticia de energías renovables, desarrollado con **HTML5, CSS3, JavaScript, PHP y MySQL**.

---

## 1. Instalación

1. Copia la carpeta completa del proyecto dentro del directorio del servidor web:
   - XAMPP: `C:\xampp\htdocs\energia-web`
   - MAMP: `/Applications/MAMP/htdocs/energia-web`
   - Linux: `/var/www/html/energia-web`

2. Arranca **Apache** y **MySQL** desde el panel de control.

3. Importa la base de datos:
   - Abre `http://localhost/phpmyadmin`
   - Pestaña **Importar** → selecciona `sql/database.sql` → **Continuar**
   - Esto crea la base de datos `solvolt_energia` con sus 4 tablas y datos de ejemplo.

4. Revisa las credenciales de conexión en `config/db.php` y ajústalas si tu MySQL
   usa otro usuario o contraseña:

   ```php
   $db_user = 'root';
   $db_pass = '';
   ```

5. Abre el sitio en el navegador: `http://localhost/energia-web/index.php`

6. **Descarga las fotografías reales** (paso recomendado). Abre una sola vez:

   ```
   http://localhost/energia-web/descargar-imagenes.php
   ```

   Descarga 6 fotos de Pexels (licencia libre) y las coloca en `images/` con los
   nombres correctos. El proyecto incluye ilustraciones de respaldo, así que si
   no ejecutas este paso o falla la descarga, el sitio se sigue viendo bien.
   Los créditos y los enlaces de descarga manual están en `images/LEEME-imagenes.md`.

7. Asegúrate de que la carpeta `images/` tiene permisos de escritura, ya que ahí
   se guardan las imágenes que suben los administradores al crear noticias.
   En Linux/macOS: `chmod 755 images`

---

## 2. Cuentas de prueba

| Rol           | Usuario      | Contraseña  |
|---------------|--------------|-------------|
| Administrador | `admin`      | `Admin1234` |
| Usuario       | `carlosruiz` | `User1234`  |

También puedes crear una cuenta nueva desde la página de **Registro** (siempre se
crea con rol `user`).

---

## 3. Estructura de archivos

```
energia-web/
│
├── index.php                        Portada del sitio
├── noticias.php                     Listado público de noticias
├── registro.php                     Registro de nuevos visitantes
├── login.php                        Inicio de sesión
├── logout.php                       Cierre de sesión
├── perfil.php                       Perfil (usuarios y admins)
├── citaciones.php                   Gestión de citas del usuario
├── usuarios-administracion.php      CRUD de usuarios (admin)
├── citas-administracion.php         CRUD de citas por usuario (admin)
├── noticias-administracion.php      CRUD de noticias (admin)
├── descargar-imagenes.php          Descarga las fotografías reales (Pexels)
│
├── config/
│   └── db.php                       Conexión PDO a MySQL
│
├── includes/
│   ├── auth.php                     Sesiones, roles y utilidades
│   ├── navbar.php                   Barra de navegación dinámica
│   ├── header.php                   Cabecera común
│   └── footer.php                   Pie común
│
├── css/style.css                    Estilos del sitio
├── js/script.js                     JavaScript (validación y confirmaciones)
├── images/                          Imágenes del sitio y de las noticias
└── sql/database.sql                 Script de la base de datos
```

---

## 4. Base de datos

Cuatro tablas relacionadas:

- **users_data** — datos personales (PK `idUser`, email único)
- **users_login** — credenciales (FK `idUser` única, `rol`: admin/user)
- **citas** — citas de cada usuario (FK `idUser`)
- **noticias** — noticias publicadas por administradores (FK `idUser`, título único)

Todas las claves foráneas usan `ON DELETE CASCADE`, de modo que al borrar un
usuario se eliminan también sus citas y noticias.

---

## 5. Funcionalidades por rol

### Visitante (sin iniciar sesión)
Navegación: Inicio · Noticias · Iniciar sesión · Registro

- Ver la portada y las noticias.
- Registrarse (validación en PHP, contraseña encriptada con `password_hash`).
- Iniciar sesión.

### Usuario (`rol = user`)
Navegación: Inicio · Noticias · Citaciones · Perfil · Cerrar sesión

- Ver y modificar sus datos personales (el nombre de usuario no es editable).
- Cambiar la contraseña (nunca se muestra la actual).
- Solicitar citas nuevas.
- Modificar y borrar únicamente las citas cuya fecha no haya pasado.

### Administrador (`rol = admin`)
Navegación: Inicio · Noticias · Usuarios · Citas · Noticias (admin) · Perfil · Cerrar sesión

- **Usuarios**: crear usuarios asignando rol `user` o `admin`, modificarlos y borrarlos.
- **Citas**: seleccionar un usuario y crear, ver, modificar o borrar sus citas.
- **Noticias**: crear noticias con imagen, verlas todas, modificarlas y borrarlas.

---

## 6. Seguridad aplicada

- Contraseñas encriptadas con `password_hash()` y comprobadas con `password_verify()`.
- Consultas preparadas con PDO en todas las operaciones (previene inyección SQL).
- `htmlspecialchars()` en todas las salidas (previene XSS).
- Validación de los campos obligatorios en el servidor con PHP.
- Control de acceso por rol en cada página (`requiereLogin()` y `requiereAdmin()`),
  de modo que las páginas privadas no son accesibles escribiendo la URL directamente.
- Validación del tipo y tamaño de las imágenes subidas.
- Transacciones al insertar en `users_data` y `users_login` a la vez.
