# CMSC433 Group 5: Pokémon Battle Game

A lightweight browser-based Pokémon battle simulator built using HTML, CSS, JavaScript, PHP, and MariaDB. No installations or dependencies required beyond XAMPP!

## 🚀 Getting Started

Follow the steps below to set up and run the project locally.

### 1. Download and Install XAMPP

If you don't have XAMPP installed, download it from [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html) and install it.

Make sure the following modules are available:
- **Apache** (Web server)
- **MySQL/MariaDB** (Database)

> Note: MariaDB is included with XAMPP, so no separate installation is needed.

### 2. Clone the Repository

Clone this repository into your XAMPP `htdocs` directory:

\"\"\"
cd /path/to/xampp/htdocs
git clone https://github.com/your-username/CMSC433_Group5_PokemonBattle.git
\"\"\"

Or simply download the ZIP and extract it into the `htdocs` folder.

### 3. Start Servers

Open the **XAMPP Control Panel**, then:

- Start **Apache**
- Start **MySQL** (MariaDB)

### 4. Set Up the Database

Open your browser and go to:

\"\"\"
http://localhost/CMSC433_Group5_PokemonBattle/proj3_setup.php
\"\"\"

This script will automatically set up the necessary database and tables.

### 5. Play the Game!

Once the setup is complete, start battling Pokémon by visiting:

\"\"\"
http://localhost/CMSC433_Group5_PokemonBattle/proj3.html
\"\"\"

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MariaDB (via XAMPP)

## ✅ No Extra Dependencies Required

No npm, no Composer, no frameworks — just a local web server and a database, all handled through XAMPP.

## 📁 Folder Structure

\"\"\"
CMSC433_Group5_PokemonBattle/
├── proj3.html            # Main game page
├── proj3_setup.php       # Database setup script
├── assets/               # (Optional) images, stylesheets, etc.
├── backend/              # (Optional) PHP scripts for gameplay
├── README.md             # You're reading it!
└── ...
\"\"\"
