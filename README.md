<div align="center">

# 🎮 Pokemonrol-System

### Sistema de gestión de rol Pokemon con PHP y MySQL
*Sistema completo de gestión para partidas de rol Pokemon*

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/es/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

</div>

---

## 📚 Tabla de Contenidos

- [📖 Descripción](#-descripción)
- [✨ Características](#-características)
- [🗂️ Estructura del Proyecto](#️-estructura-del-proyecto)
- [🚀 Instalación](#-instalación)
- [🎮 Uso](#-uso)
- [🛠️ Tecnologías](#️-tecnologías)
- [📸 API Endpoints](#-api-endpoints)
- [👨‍💻 Autor](#-autor)

---

## 📖 Descripción

**Pokemonrol-System** es un sistema completo de gestión para partidas de rol de Pokemon. Permite a los jugadores crear y gestionar sus Pokemon, participar en combates, y a los Game Masters administrar las partidas.

### 🎯 Funcionalidades principales

- Sistema de autenticación de jugadores
- Gestión completa de Pokemon
- Sistema de combate con tiradas de dados
- Panel de administración para Game Masters
- API REST para integraciones
- Sistema de migraciones de base de datos

---

## ✨ Características

- 🔐 **Autenticación segura**: Sistema de login para jugadores y administradores
- 🐉 **Gestión de Pokemon**: Crear, editar y eliminar Pokemon
- 🎲 **Sistema de combate**: Tiradas de dados y mecánicas de combate
- 👥 **Multi-jugador**: Soporte para múltiples jugadores simultáneos
- 📊 **Panel de administración**: Control total para Game Masters
- 🔄 **API REST**: 24 endpoints disponibles
- 💾 **Respaldos automáticos**: Sistema de backup de base de datos
- 📈 **Estadísticas**: Seguimiento de partidas y Pokemon

---

## 🗂️ Estructura del Proyecto

```
Pokemonrol-System/
├── api/                    # 24 Endpoints de la API
│   ├── players.php
│   ├── create-pokemon.php
│   ├── dice-roll.php
│   └── ...
├── backups/                # Respaldos de base de datos
│   └── backup.sql
├── img/                    # Assets e imágenes
│   ├── items/
│   ├── pokemon/
│   └── README-images.md
├── migrations/             # Migraciones SQL
│   ├── 004-initial.sql
│   ├── 005-update.sql
│   └── ...
├── scripts/                # Scripts de utilidad
│   ├── cleanup-triggers.php
│   └── pokemon-info.php
├── index.php               # Página principal
├── dashboard.php           # Panel de jugador
├── admin.php               # Panel administrativo
└── README.md
```

---

## 🚀 Instalación

### Requisitos previos

```bash
✅ PHP 7.4 o superior
✅ MySQL 5.7 o superior
✅ Apache 2.4+
✅ XAMPP/WAMP/LAMP (recomendado)
```

### Pasos de instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/albertogarciaquintana5-jpg/Pokemonrol-System.git
cd Pokemonrol-System

# 2. Importar base de datos
mysql -u root -p nombre_db < migrations/004-initial.sql

# 3. Configurar conexión
# Editar archivo de configuración con tus credenciales de MySQL

# 4. Copiar a servidor web
# XAMPP: Copiar a C:\xampp\htdocs\
# WAMP: Copiar a C:\wamp64\www\

# 5. Acceder en navegador
# http://localhost/Pokemonrol-System
```

---

## 🎮 Uso

### Para Jugadores

1. **Registro**: Crear cuenta en el sistema
2. **Login**: Iniciar sesión con credenciales
3. **Dashboard**: Ver y gestionar tus Pokemon
4. **Combate**: Participar en batallas usando dados

### Para Game Masters

1. **Panel Admin**: Acceso a todas las funcionalidades
2. **Gestión de jugadores**: Aprobar/eliminar jugadores
3. **Control de Pokemon**: Ver y modificar Pokemon
4. **Respaldos**: Crear backups de la base de datos

---

## 🛠️ Tecnologías

| Tecnología | Uso |
|-----------|-----|
| ![PHP](https://img.shields.io/badge/-PHP-777BB4?style=flat-square&logo=php&logoColor=white) | Backend y lógica del servidor |
| ![MySQL](https://img.shields.io/badge/-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) | Base de datos |
| ![JavaScript](https://img.shields.io/badge/-JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) | Interactividad frontend |
| ![HTML5](https://img.shields.io/badge/-HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) | Estructura |
| ![CSS3](https://img.shields.io/badge/-CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) | Estilos |

---

## 📸 API Endpoints

```
GET  /api/players.php              - Listar jugadores
POST /api/create-pokemon.php       - Crear Pokemon
GET  /api/pokemon-info.php         - Información de Pokemon
POST /api/dice-roll.php            - Tirar dados
PUT  /api/update-pokemon.php       - Actualizar Pokemon
DELETE /api/delete-pokemon.php     - Eliminar Pokemon
```

---

## 👨‍💻 Autor

**Alberto García Quintana**

- 📧 Email: albertogarciaquintana5@gmail.com
- 🔗 GitHub: [@albertogarciaquintana5-jpg](https://github.com/albertogarciaquintana5-jpg)
- 💼 LinkedIn: [Alberto García Quintana](https://linkedin.com/in/albertogarciaquintana)

---

<div align="center">

### ⭐ Si te gusta este proyecto, ¡dale una estrella!

**Desarrollado con 💙 para la comunidad de rol Pokemon 🎮**

</div>
