<?php
// Archivo de configuración del entorno.
// Aquí cada miembro del equipo pone su configuración local de base de datos en su PC.
// En el servidor de desarrollo (dev.jobspot.es) habrá otro env.php con su propia configuración,
// y en el servidor de producción (jobspot.es) habrá otro distinto con la suya.
// El archivo (env.php) que crees, no se sube a GitHub porque contiene tus credenciales.
// En .gitignore ya está configurado para ignorar config/env.php y no se suban las credenciales.

// Host de la base de datos.
// En local normalmente será 127.0.0.1 o localhost.
define('DB_HOST', '127.0.0.1');

// Nombre de la base de datos que usa el proyecto.
define('DB_NAME', 'jobspot');

// Usuario de la base de datos.
// En XAMPP local normalmente suele ser root.
define('DB_USER', 'root');

// Contraseña de la base de datos.
// En muchos XAMPP locales está vacía, pero depende de cada entorno.
define('DB_PASS', '');