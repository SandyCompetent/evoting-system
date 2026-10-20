#!/bin/bash

# Define colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== E-Voting System Setup & Runner ===${NC}"
echo ""

# 1. Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}Error: PHP is not installed.${NC}"
    echo -e "Please install PHP (e.g., 'brew install php' on macOS) and try again."
    exit 1
fi
echo -e "✅ PHP is installed."

# 2. Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}Error: MySQL command-line tool is not installed.${NC}"
    echo -e "Please install MySQL (e.g., 'brew install mysql' on macOS) and try again."
    exit 1
fi
echo -e "✅ MySQL is installed."

# 3. Setup Database
echo -e "\n${YELLOW}Configuring the database...${NC}"

# Check if MySQL server is running and accessible
if mysql -u root -e "SELECT 1;" &> /dev/null; then
    echo -e "Creating database 'evoting_system' if it doesn't exist..."
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS evoting_system;"

    echo -e "Importing database schema and default data from 'evoting_system.sql'..."
    mysql -u root evoting_system -e "SET FOREIGN_KEY_CHECKS = 0; SOURCE evoting_system.sql; SET FOREIGN_KEY_CHECKS = 1;" 2>/dev/null

    # Alternatively, you can drop and recreate the DB to ensure a clean slate every time:
    # mysql -u root -e "DROP DATABASE IF EXISTS evoting_system; CREATE DATABASE evoting_system;"
    # mysql -u root evoting_system < evoting_system.sql

    echo -e "✅ Database setup complete!"
else
    echo -e "${RED}Warning: Could not connect to MySQL as 'root' without a password.${NC}"
    echo -e "Ensure your MySQL server is running (e.g., 'brew services start mysql')."
    echo -e "If your MySQL 'root' user requires a password, please set up the database manually:"
    echo -e "  1. mysql -u root -p -e 'CREATE DATABASE evoting_system;'"
    echo -e "  2. mysql -u root -p evoting_system < evoting_system.sql"
    echo ""
fi

# 4. Find an available port and start the Application
PORT=8000
while lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null ; do
    echo -e "${YELLOW}Port $PORT is already in use. Trying next port...${NC}"
    PORT=$((PORT+1))
done

echo -e "\n${GREEN}🚀 Starting the PHP development server...${NC}"
echo -e "🌐 You can access the application at: ${YELLOW}http://localhost:$PORT${NC}"
echo -e "Press [Ctrl+C] to stop the server.\n"

# Open the browser automatically
sleep 1 && open "http://localhost:$PORT" &

# Run PHP built-in server
php -S localhost:$PORT
