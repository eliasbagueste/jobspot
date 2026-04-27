-- db/seed.sql
-- =========================================================
-- DATOS DE PRUEBA COMPLETOS PARA JOBSPOT
-- =========================================================
-- Contraseña de TODOS los usuarios: Test1234
--
-- USUARIOS:
--   admin@jobspot.local           → Administrador
--   tech@jobspot.local            → Empresa (Tech Solutions - verificada)
--   restaurante@jobspot.local     → Empresa (El Rincón - verificada)
--   construccion@jobspot.local    → Empresa (Obras del Norte - verificada)
--   academia@jobspot.local        → Empresa (Academia Progresa - verificada)
--   sinverificar@jobspot.local    → Empresa (StartupXYZ - SIN verificar, sin ofertas)
--   ana.garcia@jobspot.local      → Candidata
--   carlos.lopez@jobspot.local    → Candidato
--   maria.martinez@jobspot.local  → Candidata
--   pedro.sanchez@jobspot.local   → Candidato
--   lucia.fernandez@jobspot.local → Candidata
--   david.romero@jobspot.local    → Candidato
--
-- OFERTAS: 10 (published y closed únicamente)
-- CANDIDATURAS: 17 (sent, reviewed, accepted, rejected)
--
-- ADVERTENCIA: Borra TODOS los datos existentes.
-- Nunca ejecutes esto en producción.
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE favorite_jobs;
TRUNCATE TABLE applications;
TRUNCATE TABLE jobs;
TRUNCATE TABLE companies;
TRUNCATE TABLE candidate_profiles;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- 1. USUARIOS  (IDs 1-12)
-- =========================================================
-- password_hash('Test1234', PASSWORD_BCRYPT)

INSERT INTO users (id, full_name, email, password_hash, role, is_active) VALUES
(1,  'Administrador',        'admin@jobspot.local',           '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'admin',     1),
(2,  'Tech Solutions',       'tech@jobspot.local',            '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(3,  'Grupo El Rincón',      'restaurante@jobspot.local',     '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(4,  'Obras del Norte',      'construccion@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(5,  'Academia Progresa',    'academia@jobspot.local',        '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(6,  'StartupXYZ',           'sinverificar@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(7,  'Ana García',           'ana.garcia@jobspot.local',      '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(8,  'Carlos López',         'carlos.lopez@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(9,  'María Martínez',       'maria.martinez@jobspot.local',  '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(10, 'Pedro Sánchez',        'pedro.sanchez@jobspot.local',   '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(11, 'Lucía Fernández',      'lucia.fernandez@jobspot.local', '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(12, 'David Romero',         'david.romero@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1);


-- =========================================================
-- 2. PERFILES DE CANDIDATO
-- =========================================================

INSERT INTO candidate_profiles (user_id, phone, city, profile_summary) VALUES
(7,  '612 345 678', 'Barcelona', 'Desarrolladora full-stack con 3 años de experiencia en PHP y Vue.js. Apasionada por el diseño de interfaces limpias y el código bien estructurado.'),
(8,  '623 456 789', 'Sevilla',   'Profesional de hostelería con 4 años de experiencia en restaurantes de nivel medio-alto. También con formación previa como delineante técnico.'),
(9,  '634 567 890', 'Madrid',    'Camarera con amplia experiencia en sala y barra. Nivel de inglés B2, carné de manipulador de alimentos en vigor.'),
(10, '645 678 901', 'Valencia',  'Diseñador UX/UI con portfolio en Behance. Manejo Figma y Adobe XD. También he trabajado en obra como peón durante dos veranos.'),
(11, '656 789 012', 'Bilbao',    'Profesora de matemáticas con 5 años de experiencia en enseñanza secundaria y academia. Grado en Matemáticas por la UPV.'),
(12, '667 890 123', 'Zaragoza',  'Aparejador colegiado con 6 años en obra residencial y rehabilitación. Experiencia en dirección de ejecución y coordinación de equipos.');


-- =========================================================
-- 3. CATEGORÍAS  (IDs 1-8)
-- =========================================================

INSERT INTO categories (id, name, slug, is_active) VALUES
(1, 'Tecnología',     'tecnologia',     1),
(2, 'Hostelería',     'hosteleria',     1),
(3, 'Administración', 'administracion', 1),
(4, 'Comercio',       'comercio',       1),
(5, 'Construcción',   'construccion',   1),
(6, 'Educación',      'educacion',      1),
(7, 'Sanidad',        'sanidad',        1),
(8, 'Transporte',     'transporte',     1);


-- =========================================================
-- 4. EMPRESAS  (IDs 1-5)
-- =========================================================

INSERT INTO companies (id, owner_user_id, legal_name, brand_name, tax_id, location, description, is_verified) VALUES
(1, 2, 'Tech Solutions S.L.',               'Tech Solutions',  'B12345678', 'Barcelona', 'Empresa tecnológica especializada en desarrollo web y soluciones digitales para pymes. Equipo de 15 personas distribuidas por España y México.', 1),
(2, 3, 'Grupo Gastronómico El Rincón S.L.', 'El Rincón',       'B23456789', 'Sevilla',   'Grupo hostelero con más de 15 años de trayectoria en Sevilla. Restaurante de cocina mediterránea y cafetería de especialidad en el centro de la ciudad.', 1),
(3, 4, 'Obras del Norte S.A.',              'Obras del Norte', 'A34567890', 'Bilbao',    'Constructora con 20 años de experiencia en obra civil y edificación residencial en el País Vasco y Navarra.', 1),
(4, 5, 'Academia Progresa S.L.',            'Academia Progresa','B45678901', 'Madrid',   'Centro de formación reglada y extraescolar con más de 800 alumnos. Especialistas en refuerzo escolar y preparación de oposiciones.', 1),
(5, 6, 'StartupXYZ S.L.',                   'StartupXYZ',      NULL,        'Madrid',   'Startup tecnológica en fase seed dedicada al desarrollo de una app de movilidad urbana sostenible.', 0);


-- =========================================================
-- 5. OFERTAS DE TRABAJO  (IDs 1-10)
-- =========================================================
-- Solo estados published y closed, coherentes con el flujo actual.
--
-- Tech Solutions:     3 publicadas + 1 cerrada
-- El Rincón:          2 publicadas + 1 cerrada
-- Obras del Norte:    2 publicadas
-- Academia Progresa:  1 publicada
-- StartupXYZ:         sin ofertas (empresa sin verificar)

INSERT INTO jobs (id, company_id, category_id, title, description, location, contract_type, workday, modality, salary_min, salary_max, status, published_at) VALUES

-- Tech Solutions
(1, 1, 1, 'Desarrollador/a PHP',
'Buscamos un/a Desarrollador/a PHP con experiencia real en proyectos en producción para incorporarse a nuestro equipo de backend.

FUNCIONES:
- Desarrollo de nuevas funcionalidades en PHP (Laravel 10/11) siguiendo principios SOLID
- Diseño e implementación de APIs RESTful consumidas por frontends en React
- Optimización de consultas SQL sobre bases de datos MySQL y PostgreSQL
- Revisiones de código y pair programming
- Integración con Stripe, Redsys y APIs de terceros
- Tests unitarios y de integración con PHPUnit y Pest

REQUISITOS:
- Experiencia mínima de 2 años con PHP en entornos profesionales
- Dominio de Laravel o Symfony
- Conocimientos sólidos de MySQL: índices, relaciones, transacciones
- Familiaridad con Git y metodologías ágiles

OFRECEMOS:
- Contrato indefinido
- 100% remoto con reuniones semanales
- Flexibilidad horaria
- 1.000 €/año para formación
- Hardware a elegir: MacBook Pro M3 o ThinkPad con Linux',
'Barcelona', 'permanent', 'full_time', 'remote', 26000.00, 34000.00, 'published', '2026-04-01 09:00:00'),

(2, 1, 1, 'Administrador/a de Sistemas',
'Empresa de desarrollo de software busca Administrador/a de Sistemas para reforzar su departamento de infraestructura.

FUNCIONES:
- Administración de servidores Linux (Debian/Ubuntu) y Windows Server
- Gestión de entornos de virtualización con VMware y Proxmox
- Configuración de redes: VLANs, firewalls, VPN OpenVPN y WireGuard
- Administración de Apache, Nginx, MySQL, PostgreSQL, Redis
- Implementación de pipelines CI/CD con GitLab CI y GitHub Actions
- Gestión de contenedores con Docker

REQUISITOS:
- CFGS ASIR o Ingeniería Informática
- Experiencia mínima de 2 años administrando sistemas Linux en producción
- Conocimientos de redes: TCP/IP, DNS, DHCP, HTTP/S
- Scripting en Bash y/o Python

OFRECEMOS:
- Contrato indefinido
- Modalidad híbrida: 3 días remoto, 2 días en oficina
- 23 días de vacaciones
- Presupuesto anual para formación y certificaciones',
'Barcelona', 'permanent', 'full_time', 'hybrid', 28000.00, 36000.00, 'published', '2026-04-05 10:00:00'),

(3, 1, 1, 'Diseñador/a UX/UI',
'Buscamos un/a Diseñador/a UX/UI para sumarse al equipo de producto y mejorar la experiencia de nuestras aplicaciones.

FUNCIONES:
- Diseño de interfaces para aplicaciones web y móvil con Figma
- Creación de prototipos interactivos y flujos de usuario
- Tests de usabilidad con usuarios reales
- Colaboración estrecha con el equipo de desarrollo frontend
- Mantenimiento y evolución del Design System

REQUISITOS:
- Portfolio con proyectos UX/UI (imprescindible)
- Dominio de Figma
- Conocimientos de principios de accesibilidad (WCAG)
- Se valorará conocimiento básico de HTML y CSS

OFRECEMOS:
- Contrato indefinido
- Trabajo presencial en Barcelona
- Horario flexible
- Presupuesto para eventos y conferencias de diseño',
'Barcelona', 'permanent', 'full_time', 'onsite', 24000.00, 30000.00, 'published', '2026-04-10 11:00:00'),

(4, 1, 1, 'DevOps Engineer',
'Oferta cerrada. Posición ya cubierta.

Buscábamos un/a DevOps Engineer para modernizar nuestra infraestructura en AWS y automatizar los procesos de despliegue.

REQUISITOS:
- Experiencia con AWS (EC2, RDS, S3, ECS)
- Terraform e Infraestructura como Código
- Kubernetes y Helm
- Pipelines CI/CD

Gracias a todos los candidatos que aplicaron.',
'Barcelona', 'permanent', 'full_time', 'remote', 32000.00, 42000.00, 'closed', '2026-03-01 09:00:00'),

-- El Rincón
(5, 2, 2, 'Camarero/a de sala',
'Buscamos incorporar un/a Camarero/a de sala para nuestro restaurante en el corazón de Sevilla.

FUNCIONES:
- Atención y asesoramiento a los clientes durante toda su estancia
- Toma de comandas y gestión de pedidos a través de TPV
- Servicio de bebidas y alimentos en mesa
- Preparación y mantenimiento de la sala: mise en place
- Coordinación con el equipo de cocina

REQUISITOS:
- Experiencia mínima de 1 año en puesto similar
- Nivel básico de inglés (se valorará francés)
- Carné de manipulador de alimentos en vigor
- Capacidad de trabajar en equipo y bajo presión

OFRECEMOS:
- Contrato temporal con posibilidad de conversión a indefinido
- Propinas distribuidas equitativamente
- Comida de personal incluida
- Dos días libres consecutivos a la semana',
'Sevilla', 'temporary', 'full_time', 'onsite', 17000.00, 20000.00, 'published', '2026-04-08 09:00:00'),

(6, 2, 2, 'Barista — Cafetería de especialidad',
'Cafetería de especialidad busca un/a Barista apasionado/a por el café de calidad.

FUNCIONES:
- Preparación de espressos, cappuccinos, flat whites y carta de bebidas
- Latte art a nivel medio (roseta, tulipán, corazón)
- Ajuste de parámetros de molienda y extracción por variedad
- Atención personalizada y asesoramiento sobre origen y perfil de sabor
- Mantenimiento de maquinaria: espresso, molinillo, V60, Chemex

REQUISITOS:
- Experiencia mínima de 6 meses como barista o en hostelería
- Conocimientos de métodos de extracción: espresso, filtro, cold brew
- Actitud proactiva y orientada al detalle
- Se valorará formación SCA

OFRECEMOS:
- Contrato a jornada parcial con posibilidad de ampliación
- Formación continua a cargo de la empresa
- Descuento en consumiciones
- Horario de mañanas: 7:00 – 14:00',
'Sevilla', 'permanent', 'part_time', 'onsite', 15000.00, 18000.00, 'published', '2026-04-12 10:00:00'),

(7, 2, 2, 'Jefe/a de cocina',
'Oferta cerrada. Posición cubierta internamente.

Buscábamos un/a Jefe/a de cocina con experiencia demostrable en restaurante de cocina mediterránea para liderar nuestro equipo de 6 personas.',
'Sevilla', 'permanent', 'full_time', 'onsite', 28000.00, 35000.00, 'closed', '2026-03-15 09:00:00'),

-- Obras del Norte
(8, 3, 5, 'Aparejador/a de obra',
'Constructora con 20 años de trayectoria busca Aparejador/a colegiado/a para incorporación inmediata.

FUNCIONES:
- Dirección de ejecución de obras de edificación residencial
- Control de calidad de materiales y procesos constructivos
- Coordinación de subcontratas y proveedores en obra
- Elaboración de mediciones y certificaciones mensuales

REQUISITOS:
- Titulación en Arquitectura Técnica o Ingeniería de Edificación
- Colegiación vigente (imprescindible)
- Experiencia mínima de 3 años en dirección de obra residencial
- Carné de conducir B
- Conocimientos de AutoCAD y Presto

OFRECEMOS:
- Contrato indefinido
- Vehículo de empresa para desplazamientos a obra
- 24 días de vacaciones
- Proyectos estables con financiación asegurada',
'Bilbao', 'permanent', 'full_time', 'onsite', 30000.00, 38000.00, 'published', '2026-04-03 09:00:00'),

(9, 3, 5, 'Peón de construcción',
'Se necesita peón de construcción para obra en Bilbao con incorporación inmediata.

FUNCIONES:
- Apoyo general en tareas de obra: carga y descarga de materiales, limpieza
- Manejo de herramientas manuales y eléctricas básicas
- Ayuda a oficiales en trabajos de albañilería y encofrado
- Cumplimiento estricto de las normas de seguridad en obra

REQUISITOS:
- No se requiere experiencia previa (se valorará)
- Carné de conducir B (deseable)
- Disponibilidad inmediata

OFRECEMOS:
- Contrato temporal de 3 meses con posibilidad de prórroga
- Salario según convenio de la construcción
- Equipo de protección individual a cargo de la empresa',
'Bilbao', 'temporary', 'full_time', 'onsite', 16000.00, 19000.00, 'published', '2026-04-15 09:00:00'),

-- Academia Progresa
(10, 4, 6, 'Profesor/a de Matemáticas',
'Academia de refuerzo escolar busca profesor/a de matemáticas para clases presenciales en Madrid.

FUNCIONES:
- Impartir clases de matemáticas a alumnos de ESO, Bachillerato y Universidad
- Preparación personalizada de exámenes y pruebas de acceso
- Seguimiento y reporte del progreso de cada alumno

REQUISITOS:
- Grado en Matemáticas, Física o equivalente (imprescindible)
- Experiencia docente mínima de 1 año
- Habilidades comunicativas y paciencia
- Se valorará formación pedagógica (Máster de Profesorado)

OFRECEMOS:
- Contrato indefinido a jornada parcial (tardes: 16:00 – 20:00)
- Posibilidad de ampliar horas según demanda
- Buen ambiente de trabajo en equipo consolidado',
'Madrid', 'permanent', 'part_time', 'onsite', 14000.00, 18000.00, 'published', '2026-04-18 10:00:00');


-- =========================================================
-- 6. CANDIDATURAS  (17 en total, todos los estados)
-- =========================================================
--
--  job | oferta        | candidaturas
--  ----+---------------+----------------------------------------------
--   1  | PHP           | Ana(reviewed), Carlos(rejected), Lucía(sent)
--   2  | Sysadmin      | Ana(sent), María(reviewed), David(accepted)
--   3  | UX/UI         | Ana(accepted), Pedro(rejected)
--   5  | Camarero      | Carlos(sent), María(accepted), David(rejected)
--   6  | Barista       | María(sent), Pedro(reviewed)
--   8  | Aparejador    | Carlos(sent), David(reviewed)
--   9  | Peón          | Pedro(sent)
--  10  | Profesor mat. | Lucía(sent)
--  ----+---------------+----------------------------------------------
--  Sin candidaturas: 4 (DevOps-cerrada), 7 (Jefe cocina-cerrada)

INSERT INTO applications (job_id, candidate_user_id, status, message, applied_at) VALUES

-- Ana García → PHP (reviewed)
(1, 7, 'reviewed',
'Hola, me llamo Ana García y llevo 3 años desarrollando con PHP y Laravel en una agencia de Barcelona. He trabajado con MySQL, APIs REST y Vue.js en el frontend. Estoy buscando un proyecto más técnico donde seguir creciendo. Tengo portfolio en GitHub con varios proyectos propios.',
'2026-04-10 10:30:00'),

-- Ana García → Sysadmin (sent)
(2, 7, 'sent',
'Buenos días, aunque mi perfil es más de desarrollo, tengo formación en administración de sistemas Linux y he gestionado los servidores de los proyectos en los que he participado. Me interesa mucho esta posición.',
'2026-04-15 09:15:00'),

-- Ana García → UX/UI (accepted)
(3, 7, 'accepted',
'Soy desarrolladora con fuerte interés en el diseño de interfaces. Manejo Figma a diario para crear prototipos antes de implementar. Adjunto enlace a mi portfolio: behance.net/anagarcia.',
'2026-04-12 11:00:00'),

-- Carlos López → PHP (rejected)
(1, 8, 'rejected',
'Hola, soy Carlos López. Tengo un año de experiencia con PHP vanilla y algo de CodeIgniter. Me estoy formando en Laravel por mi cuenta y me gustaría dar el salto a un equipo más grande.',
'2026-04-11 16:45:00'),

-- Carlos López → Camarero (sent)
(5, 8, 'sent',
'Buenas tardes, tengo 4 años de experiencia en restaurantes de nivel medio-alto en Sevilla. Estoy acostumbrado a trabajar en sala con alto volumen de clientes y tengo el carné de manipulador de alimentos en vigor.',
'2026-04-17 12:00:00'),

-- Carlos López → Aparejador (sent)
(8, 8, 'sent',
'Aunque mi trayectoria principal es en hostelería, tengo formación como delineante técnico y he trabajado dos veranos como ayudante en obra. Me gustaría retomar esa vía profesional.',
'2026-04-20 10:30:00'),

-- María Martínez → Sysadmin (reviewed)
(2, 9, 'reviewed',
'Buenos días, soy María Martínez. Tengo 2 años de experiencia administrando servidores Linux en una empresa de logística de Madrid. Gestiono entornos Ubuntu Server, Apache y MySQL. Me estoy formando en Docker y tengo el LPIC-1.',
'2026-04-08 09:00:00'),

-- María Martínez → Camarero (accepted)
(5, 9, 'accepted',
'Hola, llevo 3 años trabajando en sala en diferentes restaurantes de Madrid y Sevilla. Tengo inglés nivel B2, que me ha permitido atender a clientela internacional. Busco estabilidad en Sevilla.',
'2026-04-18 14:00:00'),

-- María Martínez → Barista (sent)
(6, 9, 'sent',
'Me encanta el mundo del café de especialidad y llevo tiempo formándome por mi cuenta. Tengo experiencia en máquinas espresso semiprofesionales y conozco los métodos de filtro más habituales.',
'2026-04-21 11:30:00'),

-- Pedro Sánchez → UX/UI (rejected)
(3, 10, 'rejected',
'Soy diseñador gráfico con interés en el UX. Tengo experiencia en Photoshop e Illustrator pero llevo solo 6 meses aprendiendo Figma. Mi portfolio incluye rediseños de apps conocidas como ejercicio.',
'2026-04-13 17:00:00'),

-- Pedro Sánchez → Barista (reviewed)
(6, 10, 'reviewed',
'Hola, llevo 2 años trabajando como barista en una cafetería de Valencia. Manejo máquina espresso La Cimbali y he hecho un curso básico de latte art. Me interesa trabajar en un entorno más profesional.',
'2026-04-16 10:00:00'),

-- Pedro Sánchez → Peón (sent)
(9, 10, 'sent',
'Buenos días, estoy disponible para incorporación inmediata. He trabajado como peón en dos obras durante el verano de 2024 y 2025. Tengo carné de conducir B y estoy acostumbrado a trabajar en exterior.',
'2026-04-22 08:30:00'),

-- Lucía Fernández → PHP (sent)
(1, 11, 'sent',
'Me llamo Lucía Fernández, soy profesora de matemáticas pero en paralelo he estado aprendiendo desarrollo web. Tengo conocimientos de PHP, JavaScript y algo de Laravel mediante cursos y proyectos personales.',
'2026-04-19 09:45:00'),

-- Lucía Fernández → Profesor matemáticas (sent)
(10, 11, 'sent',
'Hola, soy Lucía Fernández, profesora de matemáticas con 5 años de experiencia en academia y clases particulares. Tengo el Grado en Matemáticas por la UPV y el Máster de Profesorado. Busco estabilidad en Madrid.',
'2026-04-19 16:00:00'),

-- David Romero → Sysadmin (accepted)
(2, 12, 'accepted',
'Buenos días, soy David Romero. Llevo 4 años gestionando la infraestructura IT de mi empresa actual: servidores Linux, VPN, backups y monitorización con Zabbix. Busco una posición dedicada a tiempo completo en el área de sistemas.',
'2026-04-07 10:00:00'),

-- David Romero → Aparejador (reviewed)
(8, 12, 'reviewed',
'Soy David Romero, aparejador colegiado con 6 años de experiencia en obra residencial y rehabilitación. He llevado proyectos de hasta 30 viviendas como director de ejecución. Manejo AutoCAD y Presto con soltura.',
'2026-04-14 11:30:00'),

-- David Romero → Camarero (rejected)
(5, 12, 'rejected',
'Buenas, sé que mi perfil no encaja exactamente con lo que pedís, pero tuve una temporada trabajando en sala hace unos años y me gustaría volver mientras busco trabajo en mi sector.',
'2026-04-20 18:00:00');
