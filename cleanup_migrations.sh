#!/bin/bash

# Create backup directory if it doesn't exist
mkdir -p database/backup/migrations

# First, make a backup of all migrations
cp database/migrations/*.php database/backup/migrations/

# Remove redundant admin_requests migrations
rm -f database/migrations/2025_04_15_224532_fix_admin_requests_table_add_missing_columns.php

# Remove redundant student_exam_attempts migrations
rm -f database/migrations/2025_04_15_225211_create_missing_student_exam_attempts_table.php

# Remove any other duplicate or unnecessary migrations
# Add more rm commands here as needed

echo "Migrations cleaned up!" 